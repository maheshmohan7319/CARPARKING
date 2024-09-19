<?php
include '../db_connect.php'; 
include 'header.php';
session_start();

// Default toast class
$toast_class = "toast-success"; 

// Check if there's a session message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $toast_class = $_SESSION['toast_class'] ?? "toast-success";
    // Unset the session message after displaying it once
    unset($_SESSION['message']);
    unset($_SESSION['toast_class']);
} else {
    $message = ""; // No message by default
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_cancel'])) {
    if (!isset($conn) || $conn == null) {
        die("Database connection failed. Please check your database configuration.");
    }

    // Handle cancellation form submission
    $booking_id = intval($_POST['booking_id']);
    $slot_id = intval($_POST['slot_id']);
    $current_time = new DateTime(); // Get current time
    $start_time = new DateTime($_POST['start_time']); // Get booking start time

    // Calculate the interval in minutes
    $interval = $start_time->getTimestamp() - $current_time->getTimestamp();
    $minutes_before_start = $interval / 60;

    // Check if the booking can be canceled (at least 1 hour before the start time)
    if ($minutes_before_start >= 60) {  // Allow cancellation if it's at least 60 minutes before the start time
        // Begin a transaction
        $conn->begin_transaction();
        try {
            // Update booking status to 'canceled'
            $sql_cancel_booking = "UPDATE Bookings SET status = 'canceled' WHERE booking_id = ? AND user_id = ?";
            $stmt_cancel = $conn->prepare($sql_cancel_booking);
            $stmt_cancel->bind_param("ii", $booking_id, $user_id);
            $stmt_cancel->execute();

            // Check if booking was successfully updated
            if ($stmt_cancel->affected_rows === 0) {
                throw new Exception("Failed to cancel the booking. It may not exist or you may not have permission.");
            }

            // Free up the parking slot
            $sql_free_slot = "UPDATE ParkingSlots SET status = 'available' WHERE slot_id = ?";
            $stmt_free_slot = $conn->prepare($sql_free_slot);
            $stmt_free_slot->bind_param("i", $slot_id);
            $stmt_free_slot->execute();

            // Commit the transaction
            $conn->commit();

            // Set the session message for successful cancellation
            $_SESSION['message'] = "Booking canceled successfully. You can now book another slot.";
            $_SESSION['toast_class'] = "toast-success";
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            // Set the session message for error
            $_SESSION['message'] = "Error canceling booking: " . $e->getMessage();
            $_SESSION['toast_class'] = "toast-danger";
        }

        // Close statements
        $stmt_cancel->close();
        $stmt_free_slot->close();

        // Redirect to avoid form re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Set session message for invalid cancel attempt
        $_SESSION['message'] = "You can only cancel a booking at least 1 hour before the start time.";
        $_SESSION['toast_class'] = "toast-danger";
        // Redirect to avoid form re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch user's current booking
$sql_user_booking = "SELECT * FROM Bookings WHERE user_id = ? AND status = 'booked'";
$stmt_user_booking = $conn->prepare($sql_user_booking);
$stmt_user_booking->bind_param("i", $user_id);
$stmt_user_booking->execute();
$result_user_booking = $stmt_user_booking->get_result();
$current_booking = $result_user_booking->fetch_assoc();
$stmt_user_booking->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Booking Status</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .booking-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
        <h2 class="heading-section">Ongoing Booking Status</h2>
    </div>

    <?php if (!empty($message)): ?>
        <div class="toast-container">
            <div id="notificationToast" class="toast <?php echo $toast_class; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header <?php echo $toast_class === 'toast-success' ? 'success-header' : 'danger-header'; ?>">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    <?php echo $message; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($current_booking)): ?>
        <div class="booking-card">
            <h4>Your Current Booking:</h4>
            <p><strong>Slot Number:</strong> <?php echo htmlspecialchars($current_booking['slot_id']); ?></p>
            <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($current_booking['booking_date']); ?></p>
            <p><strong>Start Time:</strong> <?php echo htmlspecialchars($current_booking['start_time']); ?></p>
            <p><strong>End Time:</strong> <?php echo htmlspecialchars($current_booking['end_time']); ?></p>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmCancelModal" 
                onclick="setCancelBooking(<?php echo $current_booking['booking_id']; ?>, <?php echo $current_booking['slot_id']; ?>, '<?php echo $current_booking['start_time']; ?>')">Cancel Booking</button>
        </div>
    <?php else: ?>
        <p>You have no ongoing bookings.</p>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="confirmCancelModal" tabindex="-1" role="dialog" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmCancelModalLabel">Confirm Cancellation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this booking?
            </div>
            <div class="modal-footer">
                <form action="" method="POST">
                    <input type="hidden" name="booking_id" id="modal_booking_id">
                    <input type="hidden" name="slot_id" id="modal_slot_id">
                    <input type="hidden" name="start_time" id="modal_start_time">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    <button type="submit" name="confirm_cancel" class="btn btn-danger">Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Initialize toast
$(document).ready(function () {
    $('.toast').toast('show');
});

// Set the booking ID, slot ID, and start time for cancellation
function setCancelBooking(booking_id, slot_id, start_time) {
    document.getElementById('modal_booking_id').value = booking_id;
    document.getElementById('modal_slot_id').value = slot_id;
    document.getElementById('modal_start_time').value = start_time;
}
</script>
</body>
</html>
