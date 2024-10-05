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

    // Check if the user already has an active booking
    $sql_check = "SELECT booking_id FROM Bookings WHERE user_id = ? AND (status = 'booked' OR status = 'active')";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        // Insert the new booking
        $sql = "INSERT INTO Bookings (user_id, vehicle_id, slot_id, start_time, end_time, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'booked', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $user_id, $vehicle_id, $slot_id, $start_time, $end_time);

        if ($stmt->execute()) {
            // Booking confirmed, now update the slot status to 'occupied'
            $sql_update_slot = "UPDATE ParkingSlots SET status = 'occupied' WHERE slot_id = ?";
            $stmt_update_slot = $conn->prepare($sql_update_slot);
            $stmt_update_slot->bind_param("i", $slot_id);

            if ($stmt_update_slot->execute()) {
                $message = "Booking confirmed and slot status updated!";
            } else {
                $message = "Booking confirmed but failed to update slot status.";
                $toast_class = "toast-danger"; // Change to red error toast
            }
        } else {
            $message = "Error: " . $stmt->error;
            $toast_class = "toast-danger"; // Change to red error toast
        }
    } else {
        $message = "You already have an active booking!";
        $toast_class = "toast-danger"; // Error toast
    }
}
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

        .booking-form {
            margin-top: 20px;
        }

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

        .card1 {
            margin: 15px;
            /* Margin between cards */
        }

        .heading-section {
            color: whitesmoke;
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
        }
    </style>
</head>

<body>


    <div class="container mt-2">
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

        <?php if (!empty($message)) : ?>
            <div class="toast-container">
                <div id="notificationToast" class="toast <?php echo $toast_class; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                    <div class="toast-header <?php echo $toast_class === 'toast-success' ? 'success-header' : 'danger-header'; ?>">
                        <strong class="me-auto">Notification</strong>
                    </div>
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <div class="row mt-4">
            <?php if (!empty($slots)): ?>
                <?php foreach ($slots as $slot): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card <?php echo $slot['status'] === 'available' ? 'available' : 'occupied'; ?>">
                            <div class="card-body">
                                <h5 class="card-title">Slot <?php echo htmlspecialchars($slot['slot_number']); ?></h5>
                                <p class="card-text">Type: <?php echo htmlspecialchars($slot['slot_type']); ?></p>
                                <p class="card-text">Status: <?php echo htmlspecialchars($slot['status']); ?></p>
                                <?php if ($slot['status'] === 'available'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="slot_id" value="<?php echo htmlspecialchars($slot['slot_id']); ?>">
                                        <input type="hidden" name="start_time" value="<?php echo htmlspecialchars($start_time); ?>">
                                        <button type="submit" name="book_slot" class="btn btn-primary">Book Slot</button>
                                    </form>
                                <?php else: ?>
                                    <p class="text-danger">Slot already booked!</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="container my-5">
    <h2 class="text-center mb-4">Our Premium Cars</h2>
    <div class="row">
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <img src="https://cdn.pixabay.com/photo/2023/02/07/17/49/supercar-7774683_640.jpg" class="card-img-top" alt="Supercar">
            <div class="card-body">
                <h5 class="card-title">Luxury Parking 1</h5>
                <p class="card-text">Secure your supercar in our premium parking facility, designed for high-end vehicles.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <img src="https://m.media-amazon.com/images/I/61Rx9tHudUL._AC_UF1000,1000_QL80_.jpg" class="card-img-top" alt="Supercar">
            <div class="card-body">
                <h5 class="card-title">Luxury Parking 2</h5>
                <p class="card-text">Enjoy exclusive access to our valet service, ensuring your car is always ready to go.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <img src="https://assets.architecturaldigest.in/photos/60004a09d68a278e29c86a11/16:9/w_2560%2Cc_limit/feature6-1366x768.jpg" class="card-img-top" alt="Supercar">
            <div class="card-body">
                <h5 class="card-title">Luxury Parking 3</h5>
                <p class="card-text">State-of-the-art security features to keep your premium car safe and sound.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <img src="https://www.lamborghini.com/sites/it-en/files/DAM/lamborghini/facelift_2019/homepage/families-gallery/2023/revuelto/revuelto_m.png" class="card-img-top" alt="Supercar">
            <div class="card-body">
                <h5 class="card-title">Luxury Parking 4</h5>
                <p class="card-text">Premium parking spaces equipped with climate control to protect your vehicle.</p>
            </div>
        </div>
    </div>
</div>


    <h3 class="text-center mb-4">Why Choose Us?</h3>
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="https://png.pngtree.com/png-vector/20220721/ourmid/pngtree-fast-service-vector-icon-express-start-service-vector-png-image_32829502.png" class="card-img-top rounded-circle" alt="Fast Service" style="width: 100px; height: 100px; object-fit: cover; margin: 20px auto;">
                <div class="card-body">
                    <h5 class="card-title">Fast Service</h5>
                    <p class="card-text">We ensure a quick and seamless booking experience.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="https://img.freepik.com/free-vector/pink-best-price-sticker-with-words-best-price-displayed-prominently_90220-2968.jpg?size=338&ext=jpg&ga=GA1.1.2008272138.1727913600&semt=ais_hybrid" class="card-img-top rounded-circle" alt="Affordable Prices" style="width: 100px; height: 100px; object-fit: cover; margin: 20px auto;">
                <div class="card-body">
                    <h5 class="card-title">Affordable Prices</h5>
                    <p class="card-text">Competitive rates for high-quality service.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="https://img.freepik.com/premium-vector/24-7-support-icon-online-support-twenty-four-seven-vector_608466-89.jpg" class="card-img-top rounded-circle" alt="24/7 Support" style="width: 100px; height: 100px; object-fit: cover; margin: 20px auto;">
                <div class="card-body">
                    <h5 class="card-title">24/7 Support</h5>
                    <p class="card-text">We are here to assist you anytime, day or night.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-4">
        <p>&copy; 2024 Car Parking Application. All rights reserved.</p>
        <p>Contact us: info@carparkingapp.com | Phone: +123 456 7890</p>
    </footer>
</div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.toast').toast('show');
        });
    </script>

</body>

</html>