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
    $username = trim($_POST['vehicle_number']); // Vehicle number as username
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $role = 'user'; // Role value
    $vehicle_type = $_POST['vehicle_type'];

    // Basic server-side validation
    if (empty($password) || empty($email) || empty($role) || empty($username) || empty($vehicle_type)) {
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
            $sql_user = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssss", $username, $password_hash, $email, $role);

            if ($stmt_user->execute()) {
                // Get the last inserted user_id
                $user_id = $conn->insert_id;

                // Insert vehicle details using the user_id
                $sql_vehicle = "INSERT INTO vehicles (user_id, vehicle_number, vehicle_type) VALUES (?, ?, ?)";
                $stmt_vehicle = $conn->prepare($sql_vehicle);
                $stmt_vehicle->bind_param("iss", $user_id, $username, $vehicle_type);

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
    <title>LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <section class="vh-100" style="background-image: url('https://png.pngtree.com/thumb_back/fw800/background/20230902/pngtree-cars-parked-in-an-underground-parking-garage-with-yellow-image_13136555.jpg'); background-size: cover; background-position: center;">
      <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col col-xl-6">
            <div class="card" style="border-radius: 1rem;">      
                  <div class="card-body p-4 p-lg-5 text-black">
                    <form method="POST" action="registration.php">
                    <div class="d-flex align-items-center mb-3 pb-1">
                      <i class="fas fa-star fa-2x me-3" style="color: #ff6219;"></i> 
                      <img src="admin/assets/img/logo.png" alt="Logo" style="height: 40px;">
                    </div>

                      <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Sign into your account</h5>

                      <div class="form-outline mb-4">
                        <label class="form-label" for="form2Example17">Vehicle Number*</label>
                        <input type="text" id="form2Example17" name="vehicle_number" class="form-control form-control-lg" required />
                      </div>

                      <div class="form-outline mb-4">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                                <option value="select">Select Vehicle Type</option>
                                <option value="car">Car</option>
                                <option value="bike">Bike</option>
                            </select>
                      </div>

                      <div class="form-outline mb-4">
                        <label class="form-label" for="form2Example17">Email*</label>
                        <input type="email" id="form2Example17" name="email" class="form-control form-control-lg" required />
                      </div>

                      <div class="row col-12">
                      <div class="col-6 form-outline mb-4">
                        <label class="form-label" for="form2Example27">Password</label>
                        <input type="password" id="form2Example27" name="password" class="form-control form-control-lg" required />
                      </div>

                      <div class="col-6 form-outline mb-4">
                        <label class="form-label" for="form2Example27">Confirm Password</label>
                        <input type="password" id="form2Example27" name="confirm_password" class="form-control form-control-lg" required />
                      </div>
                      </div>

                      <?php if (!empty($error_message)) : ?>
                        <p style="color: red;"><?php echo $error_message; ?></p>
                      <?php endif; ?>

                      <?php if (!empty($success_message)) : ?>
                        <p style="color: green;"><?php echo $success_message; ?></p>
                      <?php endif; ?>

                      <div class="pt-1 mb-4 d-flex justify-content-center">
                        <button class="btn btn-dark btn-lg" type="submit" style="width: 60%;">Register</button>
                      </div>
                    </form>
         
                    <div class="d-flex justify-content-center">
                        <p class="text-center m-0">Don't have an account? <a href="login.php" class="btn btn-link">Login Now</a></p>
                      </div>
                  </div>
                </div>
            </div>        
        </div>
      </div>
    </section>
</body>
</html>
