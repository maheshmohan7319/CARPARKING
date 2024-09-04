<?php
// Include the database connection file
include '../db_connect.php';  // Adjust the path if necessary
include 'nav.php'; 
// Start the session
session_start();

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. You do not have permission to view this page.");
}

// Handle vehicle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['vehicle_id'])) {
    $vehicle_id = intval($_GET['vehicle_id']);
    
    // Delete the vehicle from the database
    $sql = "DELETE FROM Vehicles WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    
    if ($stmt->execute()) {
        $success_message = "Vehicle deleted successfully.";
    } else {
        $error_message = "Error deleting vehicle: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Fetch vehicle data from the database
$sql = "SELECT vehicle_id, user_id, vehicle_number, vehicle_type FROM Vehicles";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Error fetching vehicle data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Vehicle List</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
	<link rel="stylesheet" href="assets/css/ready.css">
	<link rel="stylesheet" href="assets/css/demo.css">
    <style>
        .container {
            max-width: 900px;
        }
    </style>
    <script>
        function confirmDelete(vehicleId) {
            var result = confirm("Are you sure you want to delete this vehicle?");
            if (result) {
                window.location.href = "vehicles.php?action=delete&vehicle_id=" + vehicleId;
            }
        }
    </script>
</head>
<body>
<div class="container mt-5" style="width: 50%; margin-left: 280px; ">
    <h2 class="mb-4">Registered Vehicles</h2>
    
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
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Vehicle Number</th>
                <th>Vehicle Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vehicle_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                        <td>
                            <a href="edit_vehicle.php?vehicle_id=<?php echo urlencode($row['vehicle_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo htmlspecialchars($row['vehicle_id']); ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No vehicles found</td>
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
