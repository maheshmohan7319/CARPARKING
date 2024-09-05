<?php
session_start();  // Start session at the beginning
include '../db_connect.php';
include 'header.php'; 
include 'nav.php';

$message = '';
$slot_number = '';
$slot_type = 'car'; // default value
$status = 'available'; // default value
$slot_id = null;
$is_edit = false;

// Check if an ID is provided for editing an existing slot
if (isset($_GET['id'])) {
    $slot_id = intval($_GET['id']);
    $is_edit = true;

    // Fetch existing slot data for editing
    $stmt = $conn->prepare("SELECT slot_number, slot_type, status FROM parkingslots WHERE slot_id = ?");
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $stmt->bind_result($slot_number, $slot_type, $status);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slot_number = $_POST['slot_number'];
    $slot_type = $_POST['slot_type'];
    $status = $_POST['status'];
    $slot_id = $_POST['slot_id'] ?? null;
    $is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] === '1';

    // Check for existing slot with the same number
    $stmt = $conn->prepare("SELECT COUNT(*) FROM parkingslots WHERE slot_number = ? AND (slot_id != ? OR ? IS NULL)");
    $stmt->bind_param("ssi", $slot_number, $slot_id, $slot_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $message = "Slot number already exists. Please choose a different number.";
    } else {
        if ($is_edit) {
            // Update existing slot
            $stmt = $conn->prepare("UPDATE parkingslots SET slot_number = ?, slot_type = ?, status = ? WHERE slot_id = ?");
            $stmt->bind_param("sssi", $slot_number, $slot_type, $status, $slot_id);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Slot updated successfully.";
                echo "<script>window.location.href='parking_slot.php';</script>";
                exit();
            } else {
                $message = "Error updating slot: " . $conn->error;
            }

            $stmt->close();
        } else {
            // Insert new slot
            $stmt = $conn->prepare("INSERT INTO parkingslots (slot_number, slot_type, status) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $slot_number, $slot_type, $status);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Slot added successfully.";
                echo "<script>window.location.href='parking_slot.php';</script>";
                exit();
            } else {
                $message = "Error adding slot: " . $conn->error;
            }

            $stmt->close();
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>LMS - Parking Slot Management</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="../assets/css/ready.css">
    <link rel="stylesheet" href="../assets/css/demo.css">
</head>
<body>
    <div class="wrapper">
        <div class="main-panel">
            <div class="content">
                <div class="container-fluid">
                    <h4 class="page-title"><?php echo $is_edit ? 'Edit' : 'Create'; ?> Parking Slot</h4>
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($message)) : ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($message); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <form action="parking_slot_creation.php" method="POST">
                                <div class="form-group">
                                    <label for="slot_number">Slot Number</label>
                                    <input type="text" class="form-control" id="slot_number" name="slot_number" value="<?php echo htmlspecialchars($slot_number); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="slot_type">Slot Type</label>
                                    <select class="form-control" id="slot_type" name="slot_type">
                                        <option value="car" <?php echo $slot_type == 'car' ? 'selected' : ''; ?>>Car</option>
                                        <option value="bike" <?php echo $slot_type == 'bike' ? 'selected' : ''; ?>>Bike</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="available" <?php echo $status == 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="occupied" <?php echo $status == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                        <option value="reserved" <?php echo $status == 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                                    </select>
                                </div>
                                <input type="hidden" name="slot_id" value="<?php echo htmlspecialchars($slot_id); ?>">
                                <input type="hidden" name="is_edit" value="<?php echo $is_edit ? '1' : '0'; ?>">
                                <div class="pt-1 mb-4 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-dark btn-lg"><?php echo $is_edit ? 'Update' : 'Create'; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/core/jquery.3.2.1.min.js"></script>
    <script src="../assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugin/chartist/chartist.min.js"></script>
    <script src="../assets/js/plugin/chartist/plugin/chartist-plugin-tooltip.min.js"></script>
    <script src="../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="../assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
    <script src="../assets/js/plugin/jquery-mapael/jquery.mapael.min.js"></script>
    <script src="../assets/js/plugin/jquery-mapael/maps/world_countries.min.js"></script>
    <script src="../assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="../assets/js/ready.min.js"></script>
</body>
</html>