<?php
// Include the database connection file
include 'db_connect.php';  // Ensure this path is correct and db_connect.php sets up the $conn variable properly

// Initialize error messages
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if $conn is properly initialized
    if (!isset($conn) || $conn == null) {
        die("Database connection failed. Please check your database configuration.");
    }

    // Collect user details
    $vehicle_number = trim($_POST['vehicle_number']); // Vehicle number as username
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = 'user'; // Role value
    $vehicle_type = $_POST['vehicle_type'];

    // Basic server-side validation
    if (empty($full_name) || empty($password) || empty($email) || empty($role) || empty($vehicle_number) || empty($vehicle_type)) {
        $error_message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } elseif ($password !== $_POST['confirm_password']) {
        $error_message = "Passwords do not match!";
    } else {
        // If validation passes, start a transaction
        $conn->begin_transaction();

        try {
            // Insert user details
            $password_hash = password_hash($password, PASSWORD_BCRYPT); // Hash the password
            $sql_user = "INSERT INTO users (username, full_name, password, email, role) VALUES (?, ?, ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("sssss", $vehicle_number, $full_name, $password_hash, $email, $role);

            if ($stmt_user->execute()) {
                // Get the last inserted user_id
                $user_id = $conn->insert_id;

                // Insert vehicle details using the user_id
                $sql_vehicle = "INSERT INTO vehicles (user_id, vehicle_number, vehicle_type) VALUES (?, ?, ?)";
                $stmt_vehicle = $conn->prepare($sql_vehicle);
                $stmt_vehicle->bind_param("iss", $user_id, $vehicle_number, $vehicle_type);

                if ($stmt_vehicle->execute()) {
                    // Commit transaction
                    $conn->commit();
                    $success_message = "Registration successful! User and vehicle details added.";
                } else {
                    throw new Exception("Error inserting vehicle details: " . $stmt_vehicle->error);
                }
            } else {
                throw new Exception("Error inserting user details: " . $stmt_user->error);
            }
        } catch (Exception $e) {
            // Rollback transaction in case of an error
            $conn->rollback();
            $error_message = $e->getMessage();
        }

        // Close statements
        $stmt_user->close();
        $stmt_vehicle->close();
    }

    // Close database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-k6RqeWeci5ZR/Lv4MR0sA0FfDOM90yG7Y5ZrOvAAiJxL4+4x8t5pL1X5o4LOPDfUhh2gZjGVDWMIqzLv2M42FA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background: linear-gradient(135deg, #023C6E, #ff6219); /* Gradient background */
            font-family: 'Arial', sans-serif;
            overflow: hidden;
        }
        .registration-container {
            max-width: 600px;
            margin: auto;
            padding: 40px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px); /* Blur effect on background */
            transition: transform 0.3s; /* Smooth transform effect */
            position: relative;
            z-index: 1;
        }
        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1); /* Overlay effect */
            border-radius: 15px;
            z-index: -1;
        }
        .registration-container h2 {
            margin-bottom: 30px;
            color: #023C6E; /* Primary color */
            font-weight: bold;
            font-size: 2rem; /* Larger heading */
            text-align: center;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: border-color 0.2s, box-shadow 0.2s; /* Smooth transition */
        }
        .form-control:focus {
            border-color: #ff6219; /* Focus color */
            box-shadow: 0 0 5px rgba(255, 98, 25, 0.5);
        }
        .btn-custom {
            background-color: #ff6219; /* Button color */
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.2s; /* Smooth transition */
        }
        .btn-custom:hover {
            background-color: #023C6E; /* Hover color */
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Shadow effect */
            transform: scale(1.05); /* Slight scaling on hover */
        }
        .footer-link {
            color: #023C6E;
            text-decoration: none;
            font-weight: bold;
        }
        .footer-link:hover {
            text-decoration: underline; /* Underline on hover */
        }
        .alert {
            border-radius: 5px;
        }
        .icon {
            font-size: 4rem;
            color: #ff6219; /* Logo icon color */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="registration-container">
            <i class="fas fa-car icon"></i> <!-- Car icon -->
            <h2>Create Your Account</h2>
            <form method="POST" action="registration.php">
                <div class="row mb-4">
                    <div class="col-6">
                        <label for="vehicle_number" class="form-label">Vehicle Number*</label>
                        <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" required />
                    </div>
                    <div class="col-6">
                        <label for="vehicle_type" class="form-label">Vehicle Type*</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="car">Car</option>
                            <option value="bike">Bike</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" id="email" name="email" class="form-control" required />
                    </div>
                    <div class="col-6">
                        <label for="full_name" class="form-label">Fullname*</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required />
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <label for="password" class="form-label">Password*</label>
                        <input type="password" id="password" name="password" class="form-control" required />
                    </div>
                    <div class="col-6">
                        <label for="confirm_password" class="form-label">Confirm Password*</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                    </div>
                </div>
                <?php if (!empty($error_message)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)) : ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <div class="d-grid mb-4">
                    <button class="btn btn-custom" type="submit">Register</button>
                </div>
            </form>
            <div class="text-center">
                <p class="m-0">Already have an account? <a href="login.php" class="footer-link">Login Now</a></p>
            </div>
        </div>
    </div>
</body>
</html>
