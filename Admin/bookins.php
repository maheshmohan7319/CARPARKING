<?php
include '../db_connect.php';
include 'header.php'; 
include 'nav.php';

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch all bookings
$sql = "SELECT booking_id, user_id, vehicle_id, slot_id, booking_date, start_time, end_time, status, created_at FROM bookings";
$result = $connection->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Bookings</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Booking Management</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User ID</th>
                    <th>Vehicle ID</th>
                    <th>Slot ID</th>
                    <th>Booking Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['booking_id'] . "</td>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . $row['vehicle_id'] . "</td>";
                        echo "<td>" . $row['slot_id'] . "</td>";
                        echo "<td>" . $row['booking_date'] . "</td>";
                        echo "<td>" . $row['start_time'] . "</td>";
                        echo "<td>" . $row['end_time'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>
                            <form method='post' action='update_booking_status.php'>
                                <input type='hidden' name='booking_id' value='" . $row['booking_id'] . "'>
                                <select name='status' class='form-control'>
                                    <option value='booked'" . ($row['status'] == 'booked' ? ' selected' : '') . ">Booked</option>
                                    <option value='completed'" . ($row['status'] == 'completed' ? ' selected' : '') . ">Completed</option>
                                    <option value='cancelled'" . ($row['status'] == 'cancelled' ? ' selected' : '') . ">Cancelled</option>
                                </select>
                                <button type='submit' class='btn btn-primary mt-2'>Update</button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No bookings found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$connection->close();
?>
