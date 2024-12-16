<?php
session_start(); 
include '../db_connect.php';
include 'header.php';
include 'nav.php';

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

if (isAdmin() && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];

    // Fetch current status of the booking
    $status_query = "SELECT status FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    // Check if the current status is 'completed'
    if ($current_status === 'completed') {
        $_SESSION['message'] = "Cannot update a completed booking.";
        header("Location: booking.php");
        exit();
    }

    // Check if the new status is different from the current status
    if ($current_status === $new_status) {
        $_SESSION['message'] = "No changes made. The booking status is already set to '$new_status'.";
        header("Location: booking.php");
        exit();
    }

    // Update the booking status
    $update_query = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $booking_id); // Correct variable name here

    if ($stmt->execute()) {
        $_SESSION['message'] = "Booking status updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating booking status: " . $conn->error;
    }

    $stmt->close();
    header("Location: booking.php");
    exit();
}

// Handle search and filtering
$search_query = "";
$status_filter = "booked"; 

if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

if (isset($_GET['status_filter'])) {
    $status_filter = $_GET['status_filter'];
}

// Prepare SQL query to fetch bookings
$sql = "
    SELECT b.*, u.full_name, s.slot_number, v.vehicle_number
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parkingslots s ON b.slot_id = s.slot_id
    JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    WHERE b.status = '" . $conn->real_escape_string($status_filter) . "'
";

if (!empty($search_query)) {
    $sql .= " AND u.username LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}

$result = $conn->query($sql);

$status_options = ['booked', 'occupied', 'cancelled', 'completed'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>LMS - Reservations</title>
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
                    <h4 class="page-title">Reservations</h4>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert" id="statusMessage">
                            <?php 
                            echo $_SESSION['message']; 
                            unset($_SESSION['message']);
                            ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">

                            <form method="GET" action="booking.php" class="form-inline mb-3">
                                <input type="text" name="search" class="form-control mr-2" placeholder="Search Vehicle Number" value="<?php echo htmlspecialchars($search_query); ?>">
                                
                                <select name="status_filter" class="form-control mr-2">
                                    <?php foreach ($status_options as $option): ?>
                                        <option value="<?php echo $option; ?>" <?php echo ($status_filter == $option) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" class="btn btn-dark">Search</button>
                            </form>

                            <?php if ($result->num_rows > 0) : ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Sl.No</th>
                                                <th>User Name</th>
                                                <th>Vehicle Number</th>
                                                <th>Slot Name</th>
                                                <th>Booking Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <?php if (isAdmin()): ?>
                                                    <th>Action</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                             $counter = 1; 
                                             while ($row = $result->fetch_assoc()) : ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['slot_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                                    <?php if (isAdmin()): ?>
                                                        <td>
                                                            <?php if ($row['status'] !== 'completed'): ?>
                                                                <form method="POST" action="booking.php" class="d-flex align-items-center">
                                                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                                                    
                                                                    <select name="new_status" class="form-control form-control-sm d-inline-block w-auto mr-2">
                                                                        <?php foreach ($status_options as $option): ?>
                                                                            <?php if ($option !== 'cancelled'): ?>
                                                                                <option value="<?php echo $option; ?>" <?php echo ($row['status'] == $option) ? 'selected' : ''; ?>>
                                                                                    <?php echo ucfirst($option); ?>
                                                                                </option>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </select>

                                                                    <button type="submit" name="update_status" class="btn btn-dark btn-sm ml-auto">Update</button>
                                                                </form>
                                                            <?php else: ?>
                                                                <span>Completed</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <p>No bookings found.</p>
                            <?php endif; ?>
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

    <!-- JavaScript to hide the message after 2 seconds -->
    <script>
        setTimeout(function() {
            var statusMessage = document.getElementById('statusMessage');
            if (statusMessage) {
                statusMessage.style.display = 'none';
            }
        }, 2000); // 2 seconds
    </script>
</body