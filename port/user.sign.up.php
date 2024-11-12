<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../core/db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    // Check if the email already exists
    $check_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "It looks like an account with this email address already exists. Please, try to use another email address.";
    } else {
        // Save user details in the database
        $encrypted_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, phone_number, password, verification_code, email_verified_at)
                  VALUES (?, ?, ?, ?, NULL, NULL)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $phone_number, $encrypted_password);
    
        if ($stmt->execute() === TRUE) {
            $success = "Your account has been created successfully! Please, login to verify your account.";
        } else {
            $error = "An error occured while creating your account: " . $stmt->error;
        }
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
    <title>Create Account</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>
<body>
    <div class="d-flex justify-content-center align-items-center">
        <div class="position-absolute top-0 start-0 m-3">
            <a href="user.sign.html" class="text-white">
                <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" alt="Back" class="back-arrow">
            </a>
        </div>
        <div class="sign-container p-4">
            <div class="mb-4">
                <img src="../assets/images/cm2_logo2.png" alt="CaptioMirror Logo" class="logo img-fluid">
                <h4 class="mb-3">CREATE YOUR ACCOUNT</h4>
            </div>

            <!-- Form for Sign-up -->
            <form action="user.sign.up.php" method="post">
                <div class="form-group mb-3">
                    <label for="username" class="form-label">USERNAME<span>*</span></label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">EMAIL ADDRESS<span>*</span></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" required>
                </div>
                <div class="form-group mb-3">
                    <label for="phone_number" class="form-label">PHONE NUMBER<span>*</span></label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="09123456789" required>
                </div>
                <div class="form-group mb-3 password-container">
                    <label for="password" class="form-label">PASSWORD<span>*</span></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Your Password" required>
                    <span class="toggle-password1">
                        <img src="../assets/svg/eye-svgrepo-com.svg" alt="Show Password" id="togglePasswordIcon">
                    </span>
                </div>
                <button type="submit" class="btn1 btn-primary w-100" id="register" name="register">SIGN-UP</button>
            </form>
        </div>
    </div>

    <!-- Modal for Success/Error Messages -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($error)): ?>
                        <?= htmlspecialchars($error); ?>
                    <?php elseif (!empty($success)): ?>
                        <?= htmlspecialchars($success); ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-ok" data-bs-dismiss="modal" id="modalOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="sign.func.js"></script>
    <script>
        // Show modal on page load if any message exists
        document.addEventListener("DOMContentLoaded", function() {
            var modal = new bootstrap.Modal(document.getElementById('statusModal'));
            if ("<?= !empty($error) || !empty($success) ?>") {
                modal.show();
            }
        });

        // Handle redirection when user clicks OK
        document.getElementById('modalOkBtn').addEventListener('click', function() {
            <?php if (!empty($success)): ?>
                window.location.href = 'user.login.php';
            <?php endif; ?>
        });
    </script>
</body>
</html>
