<?php
// Include the database connection file
include '../db_connect.php';  // Adjust the path as needed

// Start the session
session_start();

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. You do not have permission to view this page.");
}

// Handle slot creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_slot'])) {
    $slot_number = trim($_POST['slot_number']);
    $slot_type = $_POST['slot_type'];

    // Validate input
    if (empty($slot_number) || empty($slot_type)) {
        $error_message = "All fields are required!";
    } else {
        $sql = "INSERT INTO ParkingSlots (slot_number, slot_type) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $slot_number, $slot_type);

        if ($stmt->execute()) {
            $success_message = "Parking slot created successfully.";
        } else {
            $error_message = "Error creating parking slot: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Handle slot update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_slot'])) {
    $slot_id = intval($_POST['slot_id']);
    $status = $_POST['status'];

    // Validate input
    if (empty($status)) {
        $error_message = "Status is required!";
    } else {
        $sql = "UPDATE ParkingSlots SET status = ? WHERE slot_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $slot_id);

        if ($stmt->execute()) {
            $success_message = "Parking slot updated successfully.";
        } else {
            $error_message = "Error updating parking slot: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Handle slot cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $slot_id = intval($_POST['slot_id']);

    // Update slot status to available
    $sql = "UPDATE ParkingSlots SET status = 'available' WHERE slot_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $slot_id);

    if ($stmt->execute()) {
        $success_message = "Booking cancelled and slot status updated to available.";
    } else {
        $error_message = "Error cancelling booking: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch parking slots data from the database
$sql = "SELECT slot_id, slot_number, slot_type, status FROM ParkingSlots";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Error fetching slot data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Parking Slots</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
        }
    </style>
    <script>
        function confirmCancellation(slotId) {
            var result = confirm("Are you sure you want to cancel the booking for this slot?");
            if (result) {
                document.getElementById('cancel_slot_id').value = slotId;
                document.getElementById('cancel_form').submit();
            }
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Parking Slots Management</h2>
    
    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Slot Creation Form -->
    <form method="POST" action="parking_slots.php">
        <h4>Create Parking Slot</h4>
        <div class="mb-3">
            <label for="slot_number" class="form-label">Slot Number</label>
            <input type="text" id="slot_number" name="slot_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="slot_type" class="form-label">Slot Type</label>
            <select id="slot_type" name="slot_type" class="form-select" required>
                <option value="">Select Slot Type</option>
                <option value="car">Car</option>
                <option value="bike">Bike</option>
            </select>
        </div>
        <button type="submit" name="create_slot" class="btn btn-primary">Create Slot</button>
    </form>

    <!-- Slot Update Form -->
    <form method="POST" action="parking_slots.php">
        <h4 class="mt-5">Update Parking Slot</h4>
        <div class="mb-3">
            <label for="slot_id" class="form-label">Slot ID</label>
            <input type="number" id="slot_id" name="slot_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
                <option value="">Select Status</option>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="reserved">Reserved</option>
            </select>
        </div>
        <button type="submit" name="update_slot" class="btn btn-warning">Update Slot</button>
    </form>

    <!-- Cancel Booking Form -->
    <form method="POST" id="cancel_form" action="parking_slots.php" style="display: none;">
        <input type="hidden" id="cancel_slot_id" name="slot_id">
        <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel Booking</button>
    </form>

    <!-- Parking Slots Table -->
    <h4 class="mt-5">Parking Slots List</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Slot Number</th>
                <th>Slot Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['slot_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['slot_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['slot_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="confirmCancellation(<?php echo htmlspecialchars($row['slot_id']); ?>)">Cancel Booking</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No slots found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
