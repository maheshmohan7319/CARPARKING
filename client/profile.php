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
    if (!empty($_POST['username']) && !empty($_POST['full_name'])) {
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];

        $update_query = "UPDATE users SET username=?, full_name=? WHERE user_id=?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $username, $full_name, $user_id);

        if ($update_stmt->execute()) {
            $message = "Profile updated successfully.";
            $stmt->execute(); // Refresh user data after update
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    } else {
        $error = "All fields are required.";
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
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group text-left">
                    <label for="full_name">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <button type="submit" class="btn btn-success mt-3">Save Changes</button>
                <button type="button" class="btn btn-secondary mt-3" onclick="window.location.reload();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
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
