<?php
include '../db_connect.php';
include 'header.php'; 
include 'nav.php';


$sql = "SELECT booking_id, user_id, vehicle_id, slot_id, booking_date, start_time, end_time, status, created_at FROM bookings";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result(); 

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>CRP - Slot List</title>
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
                    <h4 class="page-title">Booking List</h4>
                   
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
                                        <th>Booking ID</th>
                                        <th>User ID</th>
                                        <th>Vehicle ID</th>
                                        <th>Slot ID</th>
                                        <th>Booking Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Action</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['booking_id'] . "</td>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . $row['vehicle_id'] . "</td>";
                        echo "<td>" . $row['slot_id'] . "</td>";
                        echo "<td>" . $row['booking_date'] . "</td>";
                        echo "<td>" . $row['start_time'] . "</td>";
                        echo "<td>" . $row['end_time'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>
                            <form method='post' action='update_booking_status.php'>
                                <input type='hidden' name='booking_id' value='" . $row['booking_id'] . "'>
                                <select name='status' class='form-control'>
                                    <option value='booked'" . ($row['status'] == 'booked' ? ' selected' : '') . ">Booked</option>
                                    <option value='completed'" . ($row['status'] == 'completed' ? ' selected' : '') . ">Completed</option>
                                    <option value='cancelled'" . ($row['status'] == 'cancelled' ? ' selected' : '') . ">Cancelled</option>
                                </select>
                                <button type='submit' class='btn btn-primary mt-2'>Update</button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No bookings found</td></tr>";
                }
                ?>
            </tbody>

                                </table>
                            </div>
                        <?php else : ?>
                            <p>No Slot found.</p>
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
</body>
</html>




