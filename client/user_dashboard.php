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

$search_date = date('Y-m-d'); // Default to today
$start_time = date('H:i:s'); // Default to current time
$duration = 1;
$slots = [];

// Function to convert 24hr time to 12hr format
function formatTime12Hr($time) {
    return date("g:i A", strtotime($time));
}

// Function to check if a slot is available
function isSlotAvailable($bookings, $search_start_time, $search_end_time) {
    if (empty($bookings)) return true;
    
    foreach ($bookings as $booking) {
        // Convert times to timestamps for comparison
        $booking_start = strtotime($booking['booked_start']);
        $booking_end = strtotime($booking['booked_end']);
        $search_start = strtotime($search_start_time);
        $search_end = strtotime($search_end_time);
        
        // Check for time overlap
        if (($search_start < $booking_end) && ($search_end > $booking_start)) {
            return false;
        }
    }
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_slots'])) {
    $search_date = $_POST['search_date'];
    $start_time = $_POST['start_time'];
    $duration = intval($_POST['duration']);
    
    // Calculate end time
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));
    
    // First, get all slots
    $sql = "SELECT s.slot_id, s.slot_number, s.slot_type, s.status FROM ParkingSlots s";
    $result = $conn->query($sql);
    
    while ($slot = $result->fetch_assoc()) {
        // For each slot, get any bookings that might conflict
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
        
        // Add availability information to slot
        $slot['is_available'] = isSlotAvailable($bookings, $start_time, $end_time);
        $slot['bookings'] = $bookings;
        $slots[] = $slot;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_slot'])) {
    // Collect booking details from the form
    $user_id = $_SESSION['user_id'];
    $vehicle_id = $_SESSION['vehicle_id'] ?? null;
    $slot_id = $_POST['slot_id'];
    $booking_date = $search_date;
    $start_time = $_POST['start_time'];
    $duration = intval($_POST['duration']);
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));
    $status = 'booked'; // Default status for a new booking

    // Insert booking into the Bookings table
    $sql_insert = "
        INSERT INTO Bookings (user_id, vehicle_id, slot_id, booking_date, start_time, end_time, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiissss", $user_id, $vehicle_id, $slot_id, $booking_date, $start_time, $end_time, $status);

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
    <style>
        .toast-message {
            display: <?php echo $message ? 'block' : 'none'; ?>;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .slot-card {
            transition: all 0.3s;
            border-width: 2px;
        }
        .slot-card.available {
            border-color: #28a745;
            background-color: #f8fff8;
        }
        .slot-card.unavailable {
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        .booking-time {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="toast-message alert <?php echo $toast_class; ?>"><?php echo $message; ?></div>

    <div class="bg-success text-white p-4 rounded">
        <h2>Search and Book Parking Slot</h2>

        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="search_date" class="form-label">Date</label>
                    <input type="date" name="search_date" class="form-control" 
                           value="<?php echo $search_date; ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" 
                           value="<?php echo date('H:i', strtotime($start_time)); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="duration" class="form-label">Duration (hours)</label>
                    <input type="number" name="duration" class="form-control" 
                           value="<?php echo $duration; ?>" min="1" required>
                </div>
            </div>
            <button type="submit" name="search_slots" class="btn btn-primary mt-3">Search Slots</button>
        </form>

        <?php if (isset($start_time) && isset($duration)): ?>
            <div class="mt-3 text-white">
                Selected Time: <?php echo formatTime12Hr($start_time); ?> - 
                <?php echo formatTime12Hr(date('H:i:s', strtotime("+$duration hours", strtotime($start_time)))); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="row mt-4">
        <?php if ($slots): ?>
            <?php foreach ($slots as $slot): ?>
                <div class="col-md-4 mb-3">
                    <div class="card slot-card <?php echo $slot['is_available'] ? 'available' : 'unavailable'; ?>">
                        <div class="card-body">
                            <h5 class="card-title">Slot <?php echo $slot['slot_number']; ?></h5>
                            <p class="card-text">Type: <?php echo ucfirst($slot['slot_type']); ?></p>
                            <p class="card-text">Status: 
                                <?php echo $slot['is_available'] ? 'Available' : 'Booked'; ?>
                            </p>
                            
                            <?php if (!$slot['is_available'] && !empty($slot['bookings'])): ?>
                                <div class="booking-time">
                                    Booked Times:
                                    <?php foreach ($slot['bookings'] as $booking): ?>
                                        <div>
                                            <?php 
                                            echo formatTime12Hr($booking['booked_start']) . ' - ' . 
                                                 formatTime12Hr($booking['booked_end']); 
                                            ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
    $(document).ready(function(){
        setTimeout(function(){
            $('.toast-message').fadeOut('slow');
        }, 3000);
    });
</script>

</body>
</html>