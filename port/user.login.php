<?php
session_start();
require '../core/db_connect.php';

$message = '';
$redirect_url = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rememberMe = isset($_POST['rememberMe']);

    // Check if the username exists
    $username_check_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($username_check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $message = "Username not found. Please, try again.";
    } else {
        $user = $result->fetch_object();

        // Check if the provided password matches the stored hashed password
        if (!password_verify($password, $user->password)) {
            $message = "Incorrect password. Please, try again.";
        } else {
            // Check if the email has been verified
            if ($user->email_verified_at == null) {
                header("Location: func_core/user.verify.php?email=" . urlencode($user->email) . "&from=login");
                exit();
            }

            // If login is successful, create session and session token
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['phone_number'] = $user->phone_number;
            $_SESSION['date_of_birth'] = $user->date_of_birth;

            // Store profile picture in session
            $_SESSION['profile_picture'] = $user->profile_picture ?? '../assets/svg/account-avatar-default.svg';

            // Generate a session token
            $_SESSION['session_token'] = bin2hex(random_bytes(16)); // Generates a 32-character token

            // Handle "Remember Me" functionality
            if ($rememberMe) {
                $cookie_token = bin2hex(random_bytes(32));
                $expiry_time = time() + (86400 * 30); // 30 days

                // Store the token in the database
                $update_token_query = "UPDATE users SET remember_token = ? WHERE id = ?";
                $stmt->prepare($update_token_query);
                $stmt->bind_param("si", $cookie_token, $user->id);
                $stmt->execute();

                // Set a cookie on the user's browser
                setcookie('remember_me', $cookie_token, $expiry_time, "/");
            }

            $message = "You have successfully logged into your CaptioMirror account.";
            $redirect_url = "user.cm.php"; // Set redirect URL after successful login
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>

<body>
    <div id="login-inf" class="container-fluid vh-100 d-flex justify-content-center align-items-center">
        <div class="login-container p-4">
            <div class="back-button">
                <a href="user.sign.html"><img src="../assets/svg/light-left-arrow-svgrepo-com.svg" alt="Back" class="back-arrow"></a>
            </div>
            <img src="../assets/images/cm2_logo2.png" alt="CaptioMirror Logo" class="logo mb-4">
            <h4 class="mb-3">LOGIN TO YOUR ACCOUNT</h4>
            <form action="user.login.php" method="post">
                <div class="form-group mb-3">
                    <label for="username" class="form-label">USERNAME</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Your Username" required>
                </div>
                <div class="form-group mb-3 password-container">
                    <label for="password" class="form-label">PASSWORD</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Your Password" required>
                    <span class="toggle-password0">
                        <img src="../assets/svg/eye-svgrepo-com.svg" alt="Show Password" id="togglePasswordIcon">
                    </span>
                </div>
                <div class="form-check1 mb-4">
                    <input type="checkbox" class="form-check-input1" id="rememberMe" name="rememberMe">
                    <label class="form-check-label1" for="rememberMe">REMEMBER ME</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">LOGIN</button>
            </form>
        </div>
    </div>

    <!-- Modal for Login Messages -->
    <div class="modal fade" id="loginStatusModal" tabindex="-1" aria-labelledby="loginStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginStatusModalLabel">Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-message">
                    <!-- The message will be injected dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-ok" id="modal-ok-btn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="sign.func.js"></script>
    <script>
        // Function to show the modal with a custom message
        function showModal(message, redirectUrl = null) {
            document.getElementById('modal-message').textContent = message;
            var modal = new bootstrap.Modal(document.getElementById('loginStatusModal'));
            modal.show();

            // When user clicks OK, redirect if necessary
            document.getElementById('modal-ok-btn').addEventListener('click', function() {
                modal.hide();
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
        }

        // Show modal if there is a message from the PHP code
        document.addEventListener("DOMContentLoaded", function() {
            const message = "<?php echo $message; ?>";
            const redirectUrl = "<?php echo $redirect_url; ?>"; // PHP-Generated redirect URL
            if (message) {
                showModal(message, redirectUrl); // Pass message and URL to the modal function
            }
        });
    </script>
</body>
</html>
