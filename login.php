<?php
include 'db_connect.php'; 


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data from the users table
    $stmt = $conn->prepare("SELECT user_id,full_name, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            // Fetch the vehicle_id associated with the user from the vehicles table
            $vehicle_stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE user_id = ?");
            $vehicle_stmt->bind_param("i", $user['user_id']);
            $vehicle_stmt->execute();
            $vehicle_result = $vehicle_stmt->get_result();

            if ($vehicle_result->num_rows > 0) {
                $vehicle = $vehicle_result->fetch_assoc();
                $_SESSION['vehicle_id'] = $vehicle['vehicle_id']; // Store vehicle_id in session
            }

            // Redirect based on user role
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

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-k6RqeWeci5ZR/Lv4MR0sA0FfDOM90yG7Y5ZrOvAAiJxL4+4x8t5pL1X5o4LOPDfUhh2gZjGVDWMIqzLv2M42FA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background: linear-gradient(135deg, #023C6E, #ff6219); /* Gradient background */
            font-family: 'Arial', sans-serif;
            overflow: hidden;
        }
        .login-container {
            max-width: 450px;
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
        .login-container::before {
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
        .login-container h2 {
            margin-bottom: 30px;
            color: #023C6E; /* Primary color */
            font-weight: bold;
            font-size: 2rem; /* Larger heading */
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
        <div class="login-container">
            <i class="fas fa-car icon"></i> <!-- Car icon -->
            <h2 class="text-center">Welcome Back!</h2>
            <form method="POST" action="index.php">
                <div class="mb-4">
                    <label for="formVehicleNumber" class="form-label">Vehicle Number*</label>
                    <input type="text" id="formVehicleNumber" name="email" class="form-control" required />
                </div>
                <div class="mb-4">
                    <label for="formPassword" class="form-label">Password</label>
                    <input type="password" id="formPassword" name="password" class="form-control" required />
                </div>
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <div class="d-grid">
                    <button class="btn btn-custom" type="submit">Login</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <p class="m-0">Don't have an account? <a href="registration.php" class="footer-link">Register Now</a></p>
            </div>
        </div>
    </div>
</body>
</html>

