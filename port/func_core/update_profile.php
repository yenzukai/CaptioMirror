<?php
session_start();
require 'func_connect.php';

if (!isset($_SESSION['session_token'])) {
    // Check if "remember_me" cookie is set
    if (isset($_COOKIE['remember_me'])) {
        $cookie_token = $_COOKIE['remember_me'];

        // Validate the cookie token against the database
        $query = "SELECT * FROM users WHERE remember_token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $cookie_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Cookie is valid, log the user in
            $user = $result->fetch_object();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['phone_number'] = $user->phone_number ?? '';
            $_SESSION['date_of_birth'] = $user->date_of_birth ?? '';
            $_SESSION['profile_picture'] = $user->profile_picture ?? '../assets/svg/account-avatar-default.svg';
            $_SESSION['session_token'] = bin2hex(random_bytes(16)); // Generate a new session token

            // Optional: Regenerate a new `remember_me` token for added security
            $new_cookie_token = bin2hex(random_bytes(32));
            $expiry_time = time() + (86400 * 30); // 30 days
            setcookie('remember_me', $new_cookie_token, $expiry_time, "/");

            // Update the database with the new token
            $update_token_query = "UPDATE users SET remember_token = ? WHERE id = ?";
            $stmt = $conn->prepare($update_token_query);
            $stmt->bind_param("si", $new_cookie_token, $user->id);
            $stmt->execute();
        } else {
            // Invalid token, redirect to login
            header("Location: user.login.php");
            exit();
        }

        $stmt->close();
    } else {
        // No session and no remember_me cookie, redirect to login
        header("Location: user.login.php");
        exit();
    }
}

// Get the current user's ID
$user_id = $_SESSION['user_id'];

// Get the form data
$username = $_POST['username'];
$email = $_POST['email'];
$phone_number = $_POST['phone_number'];
$date_of_birth = $_POST['date_of_birth'];
$password = $_POST['password'];

// Prepare the update statement
$update_query = "UPDATE users SET username = ?, email = ?, phone_number = ?, date_of_birth = ? WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("ssssi", $username, $email, $phone_number, $date_of_birth, $user_id);

// Prepare the message and redirect URL
$message = '';
$redirect_url = '../user.prof.php';

// Execute the statement
if ($stmt->execute()) {
    // Update session variables
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone_number;
    $_SESSION['date_of_birth'] = $date_of_birth;

    // If the password was changed, update it separately
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $password_update_query = "UPDATE users SET password = ? WHERE id = ?";
        $password_stmt = $conn->prepare($password_update_query);
        $password_stmt->bind_param("si", $hashed_password, $user_id);
        $password_stmt->execute();
        $password_stmt->close();
    }

    $message = "Your profile has been successfully updated!";
} else {
    $message = "An error occurred while updating your profile. Please, try again.";
}

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Update</title>
    <link rel="icon" href="../../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../user.cm.css">
</head>
<body>
    <!-- Modal for Messages -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-message">
                    <!-- The message will be injected dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="modal-ok-button" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to show the modal with a custom message
        function showModal(message, redirectUrl = null) {
            document.getElementById('modal-message').textContent = message;
            var modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();

            // If a redirect URL is provided, handle the redirection when OK button is clicked
            if (redirectUrl) {
                document.getElementById('modal-ok-button').addEventListener('click', function () {
                    window.location.href = redirectUrl;
                });
            }
        }

        // Show modal if there is a message from the PHP code
        document.addEventListener("DOMContentLoaded", function() {
            const message = "<?php echo addslashes($message); ?>";
            const redirect = "<?php echo $redirect_url; ?>"; // Get redirect URL if exists
            if (message) {
                showModal(message, redirect);
            }
        });
    </script>
</body>
</html>
