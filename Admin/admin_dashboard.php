<?php
include '../db_connect.php'; // Include your database connection file
include 'nav.php'; 
include 'header.php'; 
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch user count
$user_count_query = "SELECT COUNT(*) AS user_count FROM users";
$user_count_result = $conn->query($user_count_query);
$user_count = $user_count_result->fetch_assoc()['user_count'];

// Fetch book (vehicle) count
$vehicle_count_query = "SELECT COUNT(*) AS vehicle_count FROM vehicles";
$vehicle_count_result = $conn->query($vehicle_count_query);
$vehicle_count = $vehicle_count_result->fetch_assoc()['vehicle_count'];

// Fetch upcoming and completed bookings
$upcoming_bookings_query = "SELECT COUNT(*) AS upcoming_count FROM bookings WHERE status = 'upcoming'";
$upcoming_bookings_result = $conn->query($upcoming_bookings_query);
$upcoming_count = $upcoming_bookings_result->fetch_assoc()['upcoming_count'];

$completed_bookings_query = "SELECT COUNT(*) AS completed_count FROM bookings WHERE status = 'completed'";
$completed_bookings_result = $conn->query($completed_bookings_query);
$completed_count = $completed_bookings_result->fetch_assoc()['completed_count'];

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
	<div class="wrapper">
		<div class="main-panel">
			<div class="content">
				<div class="container-fluid">
					<h4 class="page-title">Dashboard</h4>
					<div class="row">
						<div class="col-md-3">
							<div class="card card-stats card-warning">
								<div class="card-body">
									<div class="row">
										<div class="col-5">
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
						</div>
						<div class="col-md-3">
							<div class="card card-stats card-success">
								<div class="card-body">
									<div class="row">
										<div class="col-5">
											<div class="icon-big text-center">
												<i class="la la-car"></i>
											</div>
										</div>
										<div class="col-7 d-flex align-items-center">
											<div class="numbers">
												<p class="card-category">Vehicles</p>
												<h4 class="card-title"><?php echo $vehicle_count; ?></h4>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card card-stats card-danger">
								<div class="card-body">
									<div class="row">
										<div class="col-5">
											<div class="icon-big text-center">
												<i class="la la-calendar"></i>
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
						</div>
						<div class="col-md-3">
							<div class="card card-stats card-primary">
								<div class="card-body">
									<div class="row">
										<div class="col-5">
											<div class="icon-big text-center">
												<i class="la la-check-circle"></i>
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
						</div>
					</div>
					<!-- Additional dashboard content can go here -->
				</div>
			</div>
		</div>
	</div>
	<footer class="footer bg-light">
		<div class="container-fluid">
			<nav class="pull-left">
				<ul class="nav">
					<li class="nav-item">
						<a class="nav-link" href="#">
							LIBRARY MANAGEMENT
						</a>
					</li>
				</ul>
			</nav>
			<div class="ml-auto">
				<span>2024, made with <i class="la la-heart heart text-danger"></i> by <a href="#">BSC Computer Science</a></span>
			</div>
		</div>
	</footer>
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
