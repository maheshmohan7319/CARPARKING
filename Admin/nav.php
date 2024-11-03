<?php
include '../db_connect.php';
ob_start();
session_start();
$user_id = $_SESSION['user_id'];

$user_count_query = "SELECT COUNT(*) AS user_count FROM users WHERE user_id != ?";
$stmt = $conn->prepare($user_count_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_count_result = $stmt->get_result();
$user_count = $user_count_result->fetch_assoc()['user_count'];
$stmt->close();

$vehicle_count_query = "SELECT COUNT(*) AS vehicle_count FROM vehicles";
$vehicle_count_result = $conn->query($vehicle_count_query);
$vehicle_count = $vehicle_count_result->fetch_assoc()['vehicle_count'];

$slot_count_query = "SELECT COUNT(*) AS slot_count FROM parkingslots";
$slot_count_result = $conn->query($slot_count_query);
$slot_count = $slot_count_result->fetch_assoc()['slot_count'];

$book_count_query = "SELECT COUNT(*) AS book_count FROM bookings";
$book_count_result = $conn->query($book_count_query);
$book_count = $book_count_result->fetch_assoc()['book_count'];

$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="scrollbar-inner sidebar-wrapper">
        <ul class="nav">
        <li class="nav-item <?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">
    <a href="admin_dashboard.php" style="display: flex; align-items: center;">
        <i class="la la-home"></i>
        <p style="font-size: 18px; margin: 0 10px;">Home</p>
    </a>
</li>
            <li class="nav-item <?php echo $current_page == 'user.php' ? 'active' : ''; ?>">
                <a href="user.php" style="display: flex; justify-content: space-between; align-items: center;">
                    <i class="la la-user"></i>
                    <p style="font-size: 18px; margin: 0;">Users</p>
                    <span class="badge badge-count" style="font-size: 18px;"><?php echo $user_count; ?></span>
                </a>
            </li>
            <li class="nav-item <?php echo $current_page == 'vehicle.php' ? 'active' : ''; ?>">
                <a href="vehicle.php" style="display: flex; justify-content: space-between; align-items: center;">
                    <i class="la la-car"></i>
                    <p style="font-size: 18px; margin: 0;">Vehicle</p>
                    <span class="badge badge-count" style="font-size: 18px;"><?php echo $vehicle_count; ?></span>
                </a>
            </li>
            <li class="nav-item <?php echo $current_page == 'parking_slot.php' ? 'active' : ''; ?>">
                <a href="parking_slot.php" style="display: flex; justify-content: space-between; align-items: center;">
                    <i class="la la-comment"></i>
                    <p style="font-size: 18px; margin: 0;">Slot</p>
                    <span class="badge badge-count" style="font-size: 18px;"><?php echo $slot_count; ?></span>
                </a>
            </li>
            <li class="nav-item <?php echo $current_page == 'booking.php' ? 'active' : ''; ?>">
                <a href="booking.php" style="display: flex; justify-content: space-between; align-items: center;">
                    <i class="la la-book"></i>
                    <p style="font-size: 18px; margin: 0;">Bookings</p>
                    <span class="badge badge-count" style="font-size: 18px;"><?php echo $book_count; ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>