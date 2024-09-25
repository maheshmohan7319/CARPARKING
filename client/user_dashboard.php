<?php
include '../db_connect.php'; 
include 'header.php';
session_start();

$message = "";
$toast_class = "toast-success"; // Default to green success toast

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$vehicle_id = $_SESSION['vehicle_id'];

// Initialize search parameters
$search_date = '';
$start_time = '';
$duration = 1; // default duration
$slots = [];

// Handle search form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_slots'])) {
    $search_date = $_POST['search_date'];
    $start_time = $_POST['start_time'];
    $duration = intval($_POST['duration']);

    // Calculate end time based on duration
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));

    // Fetch available and booked slots based on search criteria
    $sql = "
        SELECT s.slot_id, s.slot_number, s.slot_type, s.status,
               b.booking_id, b.start_time AS booked_start, b.end_time AS booked_end
        FROM ParkingSlots s
        LEFT JOIN Bookings b ON s.slot_id = b.slot_id AND 
            (b.status = 'booked' AND 
            ((b.start_time <= ? AND b.end_time > ?) OR 
            (b.start_time < ? AND b.end_time >= ?)))
        WHERE s.status = 'available' 
        OR (b.booking_id IS NOT NULL)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $end_time, $start_time, $start_time, $start_time);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $slots[] = $row; // Store available and booked slots
    }
}

// Handle booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_slot'])) {
    $slot_id = $_POST['slot_id'];
    $start_time = $_POST['start_time']; // Retrieve start time from POST data
    $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time))); // Calculate end time

    $sql = "INSERT INTO Bookings (user_id, vehicle_id, slot_id, start_time, end_time, status, created_at) VALUES (?, ?, ?, ?, ?, 'booked', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $user_id, $vehicle_id, $slot_id, $start_time, $end_time);
    if ($stmt->execute()) {
        $message = "Booking confirmed!";
    } else {
        $message = "Error: " . $stmt->error;
        $toast_class = "toast-danger"; // Change to red error toast
    }
}

// Existing code for fetching parking slots without filter
// $sql = "SELECT slot_id, slot_number, slot_type, status FROM ParkingSlots";
// $result = $conn->query($sql);
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
        .body {
            background-color: #d4edda;
        }
        .available {
            background-color: #28a745;
            color: white;
            border: 1px solid #28a745;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .available:hover {
            transform: scale(1.05);
        }
        .occupied {
            background-color: #dc3545;
            color: white;
            border: 1px solid #dc3545;
            border-radius: 8px;
        }
        .booking-form { margin-top: 20px; }
        .modal-header {
            background-color: #cb5050;
            color: white;
        }
        .btn-primary {
            background-color: #023C6E;
            border-color: #023C6E;
        }
        .btn-primary:hover {
            background-color: #022f5e;
            border-color: #022f5e;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050; 
        }
        .toast-success {
            min-width: 300px; 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .toast-danger {
            min-width: 300px; 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        .toast-header {
            color: white; 
        }
        .toast-header.success-header {
            background-color: #28a745;
        }
        .toast-header.danger-header {
            background-color: #dc3545;
        }
        .booking-background {
    background-image: url('https://images.unsplash.com/photo-1470880587080-599f3e4f0913?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); 
    background-size: cover; 
    background-position: center; 
    padding: 20px;
    border-radius: 8px; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
    color: white; 
}

    .heading-section{
        color: whitesmoke;
}



    </style>
</head>
<body>
    

<div class="container">
<div class="booking-background">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section">Search and Book Parking Slot</h2>
    </div>

    <form method="POST" class="booking-form mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="search_date" class="form-label">Date</label>
                <input type="date" name="search_date" class="form-control" value="<?php echo htmlspecialchars($search_date); ?>" required>
            </div>
            <div class="col-md-4">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" value="<?php echo htmlspecialchars($start_time); ?>" required>
            </div>
            <div class="col-md-4">
                <label for="duration" class="form-label">Duration (hours)</label>
                <input type="number" name="duration" class="form-control" value="<?php echo $duration; ?>" min="1" required>
            </div>
        </div>
        <button type="submit" name="search_slots" class="btn btn-primary mt-3">Search Slots</button>
    </form>
</div>


    <div class="toast-container">
        <div id="notificationToast" class="toast <?php echo $toast_class; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header <?php echo $toast_class === 'toast-success' ? 'success-header' : 'danger-header'; ?>">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                <?php if (!empty($message)): ?>
                    <?php echo $message; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <?php if (count($slots) > 0): ?>
            <?php foreach ($slots as $row): ?>
                <div class="col-md-2 mb-3">
                    <div class="card text-center <?php echo $row['status'] == 'available' ? 'available' : 'occupied'; ?>">
                        <div class="card-body">
                            <h5 class="card-title">Slot <?php echo htmlspecialchars($row['slot_number']); ?></h5>
                            <p class="card-text">Type: <?php echo htmlspecialchars($row['slot_type']); ?></p>
                            <?php if ($row['status'] == 'available'): ?>
                                <button class="btn btn-dark btn-sm" onclick="selectSlot(<?php echo $row['slot_id']; ?>, '<?php echo $row['slot_number']; ?>')" data-toggle="modal" data-target="#bookingModal">Book Now</button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Occupied</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No slots available for the selected date and time.</p>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Confirm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="slot_id" id="slot_id">
                        <input type="hidden" name="start_time" value="<?php echo htmlspecialchars($start_time); ?>"> <!-- Hidden start time -->
                        <p>Confirm booking for <strong id="slot_number"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="book_slot" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    function selectSlot(slotId, slotNumber) {
        document.getElementById('slot_id').value = slotId;
        document.getElementById('slot_number').innerText = slotNumber;
    }

    // Show toast notification
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('notificationToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
