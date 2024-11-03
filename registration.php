<?php
include 'db_connect.php';  

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($conn) || $conn == null) {
        die("Database connection failed. Please check your database configuration.");
    }

    $username = trim($_POST['username']); 
    $vehicle_number = trim($_POST['vehicle_number']); 
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = 'user'; 
    $vehicle_type = $_POST['vehicle_type'];

    if (empty($username) || empty($full_name) || empty($password) || empty($email) || empty($role) || empty($vehicle_number) || empty($vehicle_type)) {
        $error_message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } elseif ($password !== $_POST['confirm_password']) {
        $error_message = "Passwords do not match!";
    } else {
        $sql_check = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "Username or Email already exists!";
        } else {
            $conn->begin_transaction();

            try {
                $password_hash = password_hash($password, PASSWORD_BCRYPT); 
                $sql_user = "INSERT INTO users (username, full_name, password, email, role) VALUES (?, ?, ?, ?, ?)";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("sssss", $username, $full_name, $password_hash, $email, $role);

                if ($stmt_user->execute()) {
                    $user_id = $conn->insert_id;

                    $sql_vehicle = "INSERT INTO vehicles (user_id, vehicle_number, vehicle_type) VALUES (?, ?, ?)";
                    $stmt_vehicle = $conn->prepare($sql_vehicle);
                    $stmt_vehicle->bind_param("iss", $user_id, $vehicle_number, $vehicle_type);

                    if ($stmt_vehicle->execute()) {
                        $conn->commit();
                        $success_message = "Registration successful! Redirecting to login...";
                    } else {
                        throw new Exception("Error inserting vehicle details: " . $stmt_vehicle->error);
                    }
                } else {
                    throw new Exception("Error inserting user details: " . $stmt_user->error);
                }
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = $e->getMessage();
            }

            $stmt_user->close();
            $stmt_vehicle->close();
        }

        $stmt_check->close();
    }

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
</head>
<body style="background-image: url('https://images.wallpaperscraft.com/image/single/parking_cars_lamps_177593_1280x1024.jpg'); background-size: cover; background-position: center; height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="card p-4 border-0 shadow-lg" style="max-width: 600px; width: 100%;">
            <div class="text-center mb-4">
                <i class="fas fa-car fs-1 text-primary"></i>
                <h2 class="fw-bold text-primary">Create Your Account</h2>
            </div>
            <form method="POST" action="registration.php">
                <div class="mb-3">
                    <label for="username" class="form-label">User Name *</label>
                    <input type="text" id="username" name="username" class="form-control" required />
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="vehicle_number" class="form-label">Vehicle Number*</label>
                        <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_type" class="form-label">Vehicle Type*</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="car">Car</option>
                            <option value="bike">Bike</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" id="email" name="email" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Fullname*</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password*</label>
                        <input type="password" id="password" name="password" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password*</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                    </div>
                </div>
                <?php if (!empty($error_message)) : ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)) : ?>
                    <div class="alert alert-success" id="success-message">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <div class="d-grid mb-3">
                    <button class="btn btn-primary" type="submit">Register</button>
                </div>
            </form>
            <div class="text-center">
                <p>Already have an account? <a href="login.php" class="text-decoration-none fw-bold text-primary">Login Now</a></p>
            </div>
        </div>
    </div>

    <script>
        <?php if (!empty($success_message)) : ?>
            setTimeout(function () {
                window.location.href = "login.php";
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>