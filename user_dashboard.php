<?php
include 'db_connect.php';  // Ensure this path is correct and db_connect.php sets up the $conn variable properly

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
// Handle Logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // Destroy session to log out
    session_unset();
    session_destroy();
    
    // Redirect to the login page or homepage after logout
    header("Location: login.php"); // Replace 'login.php' with your login page
    exit();
}
// Fetch parking slots data from the database
$sql = "SELECT slot_id, slot_number, slot_type, status FROM ParkingSlots";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Slot Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <style>
        .available { background-color: #28a745; color: white; } /* Green for available slots */
        .occupied { background-color: #dc3545; color: white; }  /* Red for occupied slots */
        .booking-form { display: none; margin-top: 20px; }
    </style>
</head>
<body>
<div>
   <nav class="navbar navbar-dark bg-dark border-bottom border-secondary">
    <div class="container-fluid">
        <a class="navbar-brand text-white font-weight-bold" href="#">
            <img src="https://img.lovepik.com/free-png/20210923/lovepik-car-parking-sign-icon-free-vector-illustration-png-image_401277404_wh1200.png" 
                 alt="Logo" width="40" height="40" class="d-inline-block align-text-top rounded-circle">
            PARKING
        </a>
        <!-- Logout Button Form -->
        <form method="POST" class="ml-auto">
            <button type="submit" name="logout" class="btn btn-outline-light">Logout</button>
        </form>
    </div>
</nav>
</div>
<div class="container mt-5">
    <h2 class="mb-4">Select Parking Slot</h2>

    <!-- Display message if booking is confirmed -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Display slots -->
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

    <!-- Booking Form -->
    <form id="bookingForm" method="POST" class="booking-form">
        <input type="hidden" id="slot_id" name="slot_id">
        <div class="mb-3">
            <label for="slot_number_display" class="form-label">Slot Number</label>
            <input type="text" id="slot_number_display" class="form-control" disabled>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="duration" class="form-label">Duration (in hours)</label>
            <input type="number" id="duration" name="duration" class="form-control" min="1" max="24" required>
        </div>
        <button type="submit" name="book_slot" class="btn btn-primary">Confirm Booking</button>
        <button type="button" class="btn btn-secondary" onclick="cancelBooking()">Cancel</button>
    </form>
</div>

<script>
    function selectSlot(slotId, slotNumber) {
        document.getElementById('slot_id').value = slotId;
        document.getElementById('slot_number_display').value = slotNumber;
        document.getElementById('bookingForm').style.display = 'block';
        window.scrollTo(0, document.body.scrollHeight);
    }

    function cancelBooking() {
        document.getElementById('bookingForm').style.display = 'none';
    }
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
