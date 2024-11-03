<?php
session_start();
include '../db_connect.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['full_name'])) {
        $full_name = $_POST['full_name'];
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Always update full name
            $update_query = "UPDATE users SET full_name=? WHERE user_id=?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $full_name, $user_id);
            $update_stmt->execute();
            
            // If password fields are filled, update password
            if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
                // Verify current password
                $verify_query = "SELECT password FROM users WHERE user_id = ?";
                $verify_stmt = $conn->prepare($verify_query);
                $verify_stmt->bind_param("i", $user_id);
                $verify_stmt->execute();
                $result = $verify_stmt->get_result();
                $user_data = $result->fetch_assoc();
                
                if (!password_verify($current_password, $user_data['password'])) {
                    throw new Exception("Current password is incorrect.");
                }
                
                // Verify new passwords match
                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match.");
                }
                
                // Validate password strength (at least 8 characters)
                if (strlen($new_password) < 8) {
                    throw new Exception("New password must be at least 8 characters long.");
                }
                
                // Hash new password and update
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_query = "UPDATE users SET password=? WHERE user_id=?";
                $password_stmt = $conn->prepare($password_query);
                $password_stmt->bind_param("si", $hashed_password, $user_id);
                $password_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            $message = "Profile updated successfully.";
            
            // Refresh user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    } else {
        $error = "Full name is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-card {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .profile-card h3 {
            color: #023C6E;
            font-weight: bold;
        }
        .profile-details p {
            font-size: 1.1em;
            color: #333;
        }
        .edit-form {
            display: none;
        }
        .btn-custom {
            background-color: #023C6E;
            color: #ffffff;
        }
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050;
        }
        .password-section {
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
            padding-top: 20px;
        }
    </style>
    <script>
        function toggleEditForm() {
            document.querySelector('.edit-form').style.display = 'block';
            document.querySelector('.view-details').style.display = 'none';
        }
    </script>
</head>
<body>

<div class="container">
    <div class="profile-card text-center">
        <h3>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h3>

        <!-- Profile Details View -->
        <div class="view-details mt-4">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <button class="btn btn-custom mt-3" onclick="toggleEditForm()">Edit Profile</button>
        </div>

        <!-- Edit Form -->
        <div class="edit-form">
            <form method="post" action="">
                <div class="form-group text-left">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small class="form-text text-muted">Username cannot be changed</small>
                </div>
                <div class="form-group text-left">
                    <label for="full_name">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <!-- Password Change Section -->
                <div class="password-section text-left">
                    <h5 class="mb-3">Change Password</h5>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="form-text text-muted">Password must be at least 8 characters long</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">Save Changes</button>
                <button type="button" class="btn btn-secondary mt-3" onclick="window.location.reload();">Cancel</button>
            </form>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger mt-3" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container">
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
        <div class="toast-header">
            <strong class="mr-auto text-success">Success</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            <?php echo $message; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        <?php if ($message): ?>
        $('.toast').toast('show');
        <?php endif; ?>
    });
</script>
</body>
</html>