<?php
include '../db_connect.php'; // Include the database connection

// Fetch the count of users
$user_count_query = "SELECT COUNT(*) AS user_count FROM users";
$user_count_result = $conn->query($user_count_query);
$user_count = $user_count_result->fetch_assoc()['user_count'];

// Fetch the count of vehicles
$vehicle_count_query = "SELECT COUNT(*) AS vehicle_count FROM vehicles";
$vehicle_count_result = $conn->query($vehicle_count_query);
$vehicle_count = $vehicle_count_result->fetch_assoc()['vehicle_count'];

// Fetch the count of bookings
$booking_count_query = "SELECT COUNT(*) AS booking_count FROM bookings";
$booking_count_result = $conn->query($booking_count_query);
$booking_count = $booking_count_result->fetch_assoc()['booking_count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="assets/css/ready.css">
    <link rel="stylesheet" href="assets/css/demo.css">
</head>
<body>
<div class="sidebar">
    <div class="scrollbar-inner sidebar-wrapper">
        <ul class="nav">
            <li class="nav-item active">
                <a href="admin_dashboard.php">
                    <i class="la la-home"></i>
                    <p>Home</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php">
                    <i class="la la-user"></i>
                    <p>Users</p>
                    <span class="badge badge-count"><?php echo $user_count; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="vehicles.php">
                    <i class="la la-car"></i>
                    <p>Vehicles</p>
                    <span class="badge badge-count"><?php echo $vehicle_count; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="parking_slots.php">
                    <i class="la la-calendar-check-o"></i>
                    <p>Bookings</p>
                    <span class="badge badge-count"><?php echo $booking_count; ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>
</body>
</html>
