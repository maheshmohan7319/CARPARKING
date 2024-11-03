<?php
include 'db_connect.php';


if (session_status() === PHP_SESSION_NONE) {
session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$email = $_POST['username'];
$password = $_POST['password'];


$stmt = $conn->prepare("SELECT user_id,full_name, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
$user = $result->fetch_assoc();


if (password_verify($password, $user['password'])) {
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $user['role'];
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];


$vehicle_stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE user_id = ?");
$vehicle_stmt->bind_param("i", $user['user_id']);
$vehicle_stmt->execute();
$vehicle_result = $vehicle_stmt->get_result();

if ($vehicle_result->num_rows > 0) {
$vehicle = $vehicle_result->fetch_assoc();
$_SESSION['vehicle_id'] = $vehicle['vehicle_id']; 
}


if ($user['role'] == 'admin') {
header("Location: admin/admin_dashboard.php");
exit();
} else {
header("Location: client/user_dashboard.php");
exit();
}
} else {
$error = "Password Incorrect!";
}
} else {
$error = "User not found!";
}


$stmt->close();
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
                <h2 class="fw-bold text-primary">Welcome Back!</h2>
            </div>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">User Name *</label>
                    <input type="text" id="username" name="username" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required />
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
                    <button class="btn btn-primary" type="submit">Login</button>
                </div>
            </form>
            <div class="text-center">
                <p>Don't have an account? <a href="registration.php" class="text-decoration-none fw-bold text-primary">Register Now</a></p>
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