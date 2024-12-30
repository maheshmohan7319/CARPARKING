<?php
include '../db_connect.php';
include 'header.php';
session_start();

$message = "";
$toast_class = "toast-success";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$vehicle_id = $_SESSION['vehicle_id'] ?? null;

$search_date = date('Y-m-d'); // Automatically set to today's date
$start_time = date('H:i:s'); // Default to the current time
$duration = 1;
$slots = [];

// Function to check if a slot is available
function isSlotAvailable($bookings, $search_start_time, $search_end_time) {
    if (empty($bookings)) return true;
    foreach ($bookings as $booking) {
        $booking_start = strtotime($booking['booked_start']);
        $booking_end = strtotime($booking['booked_end']);
        $search_start = strtotime($search_start_time);
        $search_end = strtotime($search_end_time);
        if (($search_start < $booking_end) && ($search_end > $booking_start)) {
            return false;
        }
    }
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_slots'])) {
    $start_time = $_POST['start_time'];
    $duration = intval($_POST['duration']);
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));

    // Fetch all slots and check availability
    $sql = "SELECT s.slot_id, s.slot_number, s.slot_type, s.status FROM ParkingSlots s";
    $result = $conn->query($sql);
    while ($slot = $result->fetch_assoc()) {
        $sql_bookings = "
            SELECT booking_id, start_time AS booked_start, end_time AS booked_end 
            FROM Bookings 
            WHERE slot_id = ? 
            AND booking_date = ? 
            AND (status = 'booked' OR status = 'occupied')
            AND ((start_time < ? AND end_time > ?) 
                OR (start_time < ? AND end_time > ?)
                OR (start_time >= ? AND end_time <= ?))
        ";
        $stmt_bookings = $conn->prepare($sql_bookings);
        $stmt_bookings->bind_param(
            "isssssss",
            $slot['slot_id'],
            $search_date,
            $end_time,
            $start_time,
            $end_time,
            $start_time,
            $start_time,
            $end_time
        );
        $stmt_bookings->execute();
        $bookings_result = $stmt_bookings->get_result();
        $bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
        $slot['is_available'] = isSlotAvailable($bookings, $start_time, $end_time);
        $slot['bookings'] = $bookings;
        $slots[] = $slot;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_slot'])) {
    $slot_id = $_POST['slot_id'];
    $start_time = $_POST['start_time'];
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 1;
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));
    $status = 'booked';

    $sql_insert = "
        INSERT INTO Bookings (user_id, vehicle_id, slot_id, booking_date, start_time, end_time, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiissss", $user_id, $vehicle_id, $slot_id, $search_date, $start_time, $end_time, $status);

    if ($stmt_insert->execute()) {
        $message = "Booking successfully created!";
        $toast_class = "toast-success";
    } else {
        $message = "Failed to create booking. Please try again.";
        $toast_class = "toast-danger";
    }
    $stmt_insert->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Slot Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="alert <?php echo $toast_class; ?>"><?php echo $message; ?></div>
    <div class="bg-success text-white p-4 rounded">
        <h2>Search and Book Parking Slot</h2>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" 
                           value="<?php echo date('H:i', strtotime($start_time)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="duration" class="form-label">Duration (hours)</label>
                    <input type="number" name="duration" class="form-control" 
                           value="<?php echo $duration; ?>" min="1" required>
                </div>
            </div>
            <button type="submit" name="search_slots" class="btn btn-primary mt-3">Search Slots</button>
        </form>
    </div>

    <div class="row mt-4">
        <?php if ($slots): ?>
            <?php foreach ($slots as $slot): ?>
                <div class="col-md-4 mb-3">
                    <div class="card slot-card <?php echo $slot['is_available'] ? 'available' : 'unavailable'; ?>">
                        <div class="card-body">
                            <h5 class="card-title">Slot <?php echo $slot['slot_number']; ?></h5>
                            <p class="card-text">Type: <?php echo ucfirst($slot['slot_type']); ?></p>
                            <p class="card-text">Status: <?php echo $slot['is_available'] ? 'Available' : 'Booked'; ?></p>
                            <?php if ($slot['is_available']): ?>
                                <form method="POST">
                                    <input type="hidden" name="slot_id" value="<?php echo $slot['slot_id']; ?>">
                                    <input type="hidden" name="start_time" value="<?php echo $start_time; ?>">
                                    <button type="submit" name="book_slot" class="btn btn-primary">Book Slot</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">Please search for available slots using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>