<?php
include 'db_connect.php';  // Ensure this path is correct and db_connect.php sets up the $conn variable properly
include 'user_header.php'; 
// Initialize messages
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if $conn is properly initialized
    if (!isset($conn) || $conn == null) {
        die("Database connection failed. Please check your database configuration.");
    }

    // Handle booking form submission
    if (isset($_POST['book_slot'])) {
        $slot_id = intval($_POST['slot_id']);
        $start_time = $_POST['start_time'];
        $duration = intval($_POST['duration']);
        $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));
        $user_id = 1; // Replace this with the actual logged-in user ID

        // Calculate total price (20 Rupees per hour)
        $total_price = $duration * 20;

        // Update slot status to 'occupied'
        $sql_update_slot = "UPDATE ParkingSlots SET status = 'occupied' WHERE slot_id = ?";
        $stmt = $conn->prepare($sql_update_slot);
        $stmt->bind_param("i", $slot_id);

        if ($stmt->execute()) {
            // Insert booking record
            $sql_insert_booking = "INSERT INTO Bookings (user_id, slot_id, booking_date, start_time, end_time, status, created_at) VALUES (?, ?, CURDATE(), ?, ?, 'confirmed', NOW())";
            $stmt_booking = $conn->prepare($sql_insert_booking);
            $stmt_booking->bind_param("iiss", $user_id, $slot_id, $start_time, $end_time);

            if ($stmt_booking->execute()) {
                $message = "Booking confirmed! Total price: ₹" . $total_price;
            } else {
                $message = "Error booking slot: " . $stmt_booking->error;
            }

            $stmt_booking->close();
        } else {
            $message = "Error updating slot status: " . $stmt->error;
        }

        $stmt->close();
    }
}


// Fetch parking slots data from the database
$sql = "SELECT slot_id, slot_number, slot_type, status FROM ParkingSlots";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>UserDashboard</title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
	<link rel="stylesheet" href="assets/css/ready.css">
	<link rel="stylesheet" href="assets/css/demo.css">
    <style>
        .available { background-color: #28a745; color: white; } /* Green for available slots */
        .occupied { background-color: #dc3545; color: white; }  /* Red for occupied slots */
        .booking-form { display: none; margin-top: 20px; }
    </style>
</head>
<body>
	<div class="wrapper" style="padding-top: 30px;">
			<div class="content ">
				<div class="container-fluid">
					<h4 class="page-title">Dashboard</h4>
                    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <div class="card text-center <?php echo $row['status'] == 'available' ? 'available' : 'occupied'; ?>">
                        <div class="card-body">
                            <h5 class="card-title">Slot <?php echo htmlspecialchars($row['slot_number']); ?></h5>
                            <p class="card-text">Type: <?php echo htmlspecialchars($row['slot_type']); ?></p>
                            <?php if ($row['status'] == 'available'): ?>
                                <button class="btn btn-light btn-sm" onclick="selectSlot(<?php echo $row['slot_id']; ?>, '<?php echo $row['slot_number']; ?>')">Book Now</button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Occupied</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No slots available.</p>
        <?php endif; ?>
    </div>
					<!-- Additional dashboard content can go here -->
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
<script src="assets/js/core/jquery.3.2.1.min.js"></script>
<script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>
<script src="assets/js/plugin/chart-circle/circles.min.js"></script>
<script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="assets/js/ready.min.js"></script>
<script src="assets/js/demo.js"></script>
</html>
