<?php
include '../db_connect.php';
include 'header.php'; 
include 'nav.php';

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); 
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Vehicles deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting class: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: vehicles.php");
    exit();
}

$sql = "SELECT * FROM vehicles";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>LMS - Class List</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="assets/css/ready.css">
    <link rel="stylesheet" href="assets/css/demo.css">
</head>
<body>
    <div class="wrapper">
        <div class="main-panel">
            <div class="content">
                <div class="container-fluid">
                    <h4 class="page-title">Vehicle List</h4>
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($message)) : ?>
                                <div id="messageAlert" class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($message); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                       
                            <?php if ($result->num_rows > 0) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sl.No</th>
                                            <th>Vehicle Name</th>
                                            <th>User Name</th>
                                            <th>Vehicle Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $counter = 1; 
                                        while($row = $result->fetch_assoc()) : ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                                                <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                                                <td>
                                                    <a href="vehicle.php?delete=<?php echo htmlspecialchars($row['vehicle_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this vehicle?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <p>No vehicle found.</p>
                        <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/core/jquery.3.2.1.min.js"></script>
    <script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/chartist/chartist.min.js"></script>
    <script src="assets/js/plugin/chartist/plugin/chartist-plugin-tooltip.min.js"></script>
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
    <script src="assets/js/plugin/jquery-mapael/jquery.mapael.min.js"></script>
    <script src="assets/js/plugin/jquery-mapael/maps/world_countries.min.js"></script>
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/ready.min.js"></script>

    <script>
        // Hide message after 2 seconds
        $(document).ready(function() {
            var messageAlert = $('#messageAlert');
            if (messageAlert.length) {
                setTimeout(function() {
                    messageAlert.alert('close');
                }, 2000); // 2000 milliseconds = 2 seconds
            }
        });
    </script>
</body>
</html>
