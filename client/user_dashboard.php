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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($conn) || $conn == null) {
        die("Database connection failed. Please check your database configuration.");
    }

    // Handle booking form submission
    if (isset($_POST['book_slot'])) {
        // Check if user already has an active booking
        $sql_check_booking = "SELECT * FROM Bookings WHERE user_id = ? AND status != 'completed'";
        $stmt_check = $conn->prepare($sql_check_booking);
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // User already has an active booking
            $message = "You already have an active booking. Please complete it before booking another slot.";
            $toast_class = "toast-danger"; // Change to red danger toast
        } else {
            // Proceed with new booking
            $slot_id = intval($_POST['slot_id']);
            $start_time = $_POST['start_time'];
            $duration = intval($_POST['duration']);
            $end_time = date('H:i:s', strtotime("+$duration hours", strtotime($start_time)));     

            // Calculate total price (20 Rupees per hour)
            $total_price = $duration * 20;

            // Update slot status to 'occupied'
            $sql_update_slot = "UPDATE ParkingSlots SET status = 'occupied' WHERE slot_id = ?";
            $stmt = $conn->prepare($sql_update_slot);
            $stmt->bind_param("i", $slot_id);

            if ($stmt->execute()) {
                // Insert booking record
                $sql_insert_booking = "INSERT INTO Bookings (user_id, vehicle_id, slot_id, booking_date, start_time, end_time, status, created_at) VALUES (?, ?, ?, CURDATE(), ?, ?, 'booked', NOW())";
                $stmt_booking = $conn->prepare($sql_insert_booking);
                $stmt_booking->bind_param("iiiss", $user_id, $vehicle_id, $slot_id, $start_time, $end_time);

                if ($stmt_booking->execute()) {
                    $message = "Booking confirmed! Total price: ₹" . $total_price . ".";
                    $toast_class = "toast-success"; // Green success toast
                } else {
                    $message = "Error booking slot: " . $stmt_booking->error;
                }

                $stmt_booking->close();
            } else {
                $message = "Error updating slot status: " . $stmt->error;
            }

            $stmt->close();
        }

        $stmt_check->close();
    }
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
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="col-md-6 text-center mb-5">
        <h2 class="heading-section">Select Parking Slot</h2>
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

    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
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
            <?php endwhile; ?>
        <?php else: ?>
            <p>No slots available.</p>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book Parking Slot</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="bookingForm" method="POST" class="booking-form">
                    <div class="modal-body">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="book_slot">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function selectSlot(slotId, slotNumber) {
        document.getElementById('slot_id').value = slotId;
        document.getElementById('slot_number_display').value = slotNumber;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if ("<?php echo $message; ?>".trim() !== "") {
            var toastElement = document.getElementById('notificationToast');
            var toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
