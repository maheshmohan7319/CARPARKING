<?php
include '../db_connect.php'; 
include 'nav.php'; 
include 'header.php'; 
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];


$upcoming_bookings_query = "SELECT COUNT(*) AS upcoming_count FROM bookings WHERE status = 'booked'";
$upcoming_bookings_result = $conn->query($upcoming_bookings_query);
$upcoming_count = $upcoming_bookings_result->fetch_assoc()['upcoming_count'];

$ongoing_bookings_query = "SELECT COUNT(*) AS ongoing_count FROM bookings WHERE status = 'occupied'";
$ongoing_bookings_result = $conn->query($ongoing_bookings_query);
$ongoing_count = $ongoing_bookings_result->fetch_assoc()['ongoing_count'];

$completed_bookings_query = "SELECT COUNT(*) AS completed_count FROM bookings WHERE status = 'completed'";
$completed_bookings_result = $conn->query($completed_bookings_query);
$completed_count = $completed_bookings_result->fetch_assoc()['completed_count'];

$cancelled_bookings_query = "SELECT COUNT(*) AS cancelled_count FROM bookings WHERE status = 'completed'";
$cancelled_bookings_result = $conn->query($cancelled_bookings_query);
$cancelled_count = $cancelled_bookings_result->fetch_assoc()['cancelled_count'];

?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Admin Dashboard</title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
	<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
	<link rel="stylesheet" href="../assets/css/ready.css">
	<link rel="stylesheet" href="../assets/css/demo.css">
</head>
<body>
<div class="main-panel">
    <div class="content">
        <div class="container-fluid">
            <div class="row">            
                <div class="col-md-3">
                    <a href="booking.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-book"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Upcoming Bookings</p>
                                            <h4 class="card-title"><?php echo $upcoming_count; ?></h4> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="booking.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-book"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Completed Bookings</p>
                                             <h4 class="card-title"><?php echo $completed_count; ?></h4> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="booking.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-book"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Cancelled Bookings</p>
                                            <h4 class="card-title"><?php echo $cancelled_count; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="booking.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-book"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Ongoing Bookings</p>
                                             <h4 class="card-title"><?php echo $ongoing_count; ?></h4> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
			<div class="row">            
                <div class="col-md-3">
                    <a href="user.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-users"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Users</p>
                                             <h4 class="card-title"><?php echo $user_count; ?></h4> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="vehicle.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-car"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Vehicle</p>
                                            <h4 class="card-title"><?php echo $vehicle_count; ?></h4> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="parking_slot.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-comment"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Slot</p>
                                            <h4 class="card-title"><?php echo $slot_count; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="booking.php" style="text-decoration: none;">
                        <div class="card card-stats bg-white shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-5 py-5">
                                        <div class="icon-big text-center">
                                            <i class="la la-book"></i>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex align-items-center">
                                        <div class="numbers">
                                            <p class="card-category">Total Bookings</p>
                                             <h4 class="card-title"><?php echo $book_count; ?></h4> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
<script src="../assets/js/core/jquery.3.2.1.min.js"></script>
<script src="../assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugin/chart-circle/circles.min.js"></script>
<script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="../assets/js/ready.min.js"></script>
<script src="../assets/js/demo.js"></script>
</html>
