<?php 
require 'func_connect.php';

$message = ''; // To store alert messages
$redirect = ''; // To store redirection URL

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture the email or username from the form
    $email = $_POST['email'];
    $verification_code = $_POST['complete_code'];

    // Prepare and bind the verification query
    $stmt = $conn->prepare("UPDATE users SET email_verified_at = NOW() WHERE email = ? AND verification_code = ?");
    $stmt->bind_param("ss", $email, $verification_code);

    $stmt->execute();
    
    // Check if any rows were affected
    if ($stmt->affected_rows == 0) {
        $message = "The verification process has failed. Please, try again or request a new code.";
    } else {
        // Fetch the user information after successful verification
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch user data
            $user = $result->fetch_object();

            // If login is successful, create session and session token
            session_start();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['phone_number'] = $user->phone_number;
            $_SESSION['date_of_birth'] = $user->date_of_birth;

            // Store profile picture in session
            $_SESSION['profile_picture'] = $user->profile_picture ?? '../assets/svg/account-avatar-profile-user-11.svg';

            // Generate a session token
            $_SESSION['session_token'] = bin2hex(random_bytes(16)); // Generates a 32-character token

            // Handle "Remember Me" functionality if enabled
            if (isset($_POST['rememberMe'])) {
                $cookie_token = bin2hex(random_bytes(32));
                $expiry_time = time() + (86400 * 30);

                // Store the token in the database
                $update_token_query = "UPDATE users SET remember_token = ? WHERE id = ?";
                $stmt->prepare($update_token_query);
                $stmt->bind_param("si", $cookie_token, $user->id);
                $stmt->execute();

                // Set a cookie on the user's browser
                setcookie('remember_me', $cookie_token, $expiry_time, "/");
            }

            $message = "You have successfully verified your account. You will be redirected to the homepage right now!";
            $redirect = "../user.cm.php"; // Redirect URL for after the modal
        } else {
            $message = "User cannot be found in our system.";
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
    <title>Verification Process</title>
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
            const message = "<?php echo $message; ?>";
            const redirect = "<?php echo $redirect; ?>"; // Get redirect URL if exists
            if (message) {
                showModal(message, redirect);
            }
        });
    </script>
</body>
</html>
