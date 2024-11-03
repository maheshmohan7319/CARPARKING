<?php
include '../db_connect.php';
include 'header.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Set default toast class
$toast_class = "toast-success"; 
$message = "";

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $toast_class = $_SESSION['toast_class'] ?? "toast-success";
    unset($_SESSION['message']);
    unset($_SESSION['toast_class']);
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    $booking_id = $_POST['booking_id'];

    $sql_cancel = "UPDATE Bookings SET status = 'cancelled' WHERE booking_id = ? AND user_id = ?";
    $stmt_cancel = $conn->prepare($sql_cancel);
    $stmt_cancel->bind_param("ii", $booking_id, $user_id);

    if ($stmt_cancel->execute()) {
        $message = "Booking has been canceled successfully.";
        $toast_class = "toast-success";
    } else {
        $message = "Failed to cancel the booking.";
        $toast_class = "toast-danger";
    }
    $stmt_cancel->close();
}

// Fetch ongoing booking
$sql_user_booking = "SELECT * FROM Bookings WHERE user_id = ? AND status = 'booked'";
$stmt_user_booking = $conn->prepare($sql_user_booking);
$stmt_user_booking->bind_param("i", $user_id);
$stmt_user_booking->execute();
$result_user_booking = $stmt_user_booking->get_result();
$current_booking = $result_user_booking->fetch_assoc();
$stmt_user_booking->close();

// Fetch all booking history
$sql = "SELECT b.booking_id, b.booking_date, b.start_time, b.end_time, b.status, s.slot_number 
        FROM Bookings b 
        JOIN ParkingSlots s ON b.slot_id = s.slot_id
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$booking_history = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 1050; }
        .toast-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .toast-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-badge { color: white !important; }
        .table-container { background-color: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .table th { background-color: #023C6E; color: white; }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if (!empty($message)): ?>
        <div class="toast-container">
            <div class="toast <?php echo $toast_class; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-body"><?php echo $message; ?></div>
            </div>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs justify-content-center" id="bookingTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="ongoing-tab" data-toggle="tab" href="#ongoing" role="tab" aria-controls="ongoing" aria-selected="true">Ongoing Booking</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="history-tab" data-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="false">Booking History</a>
        </li>
    </ul>

    <div class="tab-content mt-4" id="bookingTabsContent">
        <!-- Ongoing Booking Section -->
        <div class="tab-pane fade show active" id="ongoing" role="tabpanel" aria-labelledby="ongoing-tab">
            <?php if ($current_booking): ?>
                <div class="table-container">
                    <h4>Your Current Booking</h4>
                    <p><strong>Slot Number:</strong> <?php echo htmlspecialchars($current_booking['slot_id']); ?></p>
                    <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($current_booking['booking_date']); ?></p>
                    <p><strong>Start Time:</strong> <?php echo htmlspecialchars($current_booking['start_time']); ?></p>
                    <p><strong>End Time:</strong> <?php echo htmlspecialchars($current_booking['end_time']); ?></p>
                    <button class="btn btn-danger" data-toggle="modal" data-target="#confirmCancelModal" 
                            onclick="setCancelBooking(<?php echo $current_booking['booking_id']; ?>)">Cancel Booking</button>
                </div>
            <?php else: ?>
                <p>No ongoing bookings.</p>
            <?php endif; ?>
        </div>

  <!-- Booking History Section --> 
<div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
    <div class="table-container">
        <h4>Your Booking History</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Slot Number</th>
                    <th>Booking Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $booking_history->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['slot_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                    echo ($row['status'] === 'canceled') ? 'badge-danger' : 
                                         (($row['status'] === 'completed') ? 'badge-success' : 'badge-warning'); 
                                ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="confirmCancelModal" tabindex="-1" role="dialog" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Cancellation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to cancel this booking?</div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="booking_id" id="modal_booking_id">
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
$(document).ready(function () { $('.toast').toast('show'); });

function setCancelBooking(booking_id) {
    document.getElementById('modal_booking_id').value = booking_id;
}
</script>
</body>
</html>
