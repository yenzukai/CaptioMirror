<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
require 'func_connect.php';

$message = ''; // To store alert messages

// Get email and 'from' parameter
$email = isset($_POST['email']) ? $_POST['email'] : $_GET['email'];
$from = isset($_GET['from']) ? $_GET['from'] : null;

if ($from === 'signup' || $from === 'login') {
    // Generate and send verification code
    $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);

    // Send verification email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mjrojasforex@gmail.com'; // Replace with your email address
        $mail->Password = 'xsco larr yila xutj';   // Replace with your actual password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mjrojasforex@gmail.com', 'CaptioMirror');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your verification code';
        $mail->Body = '<p>Hi, <b style="font-weight: bold;">' . $email . '</b><br/><br/>
        Thank you for joining us. We are glad to have you on our CaptioMirror system.<br/>
        Your verification code is: <b style="font-size: 30px;">' . $verification_code . '</b></p>';

        $mail->send();

        // Update or insert the verification code in the database
        $update_query = "UPDATE users SET verification_code = ? WHERE email = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ss", $verification_code, $email);

        if ($stmt->execute()) {
            $message = "A verification code has been sent to you. Please check your email to proceed.d";
        } else {
            $message = "An error occured while updating your record: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $message = "The message could not be sent. Due to this mailer error: " . $mail->ErrorInfo;
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verification</title>
    <link rel="icon" href="../../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../user.cm.css">
</head>
<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="verification-container text-center">
            <div class="back-button mb-4">
                <a href="JavaScript: window.history.back();">
                    <img src="../../assets/svg/light-left-arrow-svgrepo-com.svg" alt="Back" class="back-arrow">
                </a>
            </div>
            <h2 class="mb-4">VERIFICATION</h2>
            <p>We’ve sent you the verification code on <span class="email"><?php echo isset($_POST['email']) ? $_POST['email'] : $_GET['email']; ?></span></p>
            <form id="verification-form" action="verify.process.php" method="post">
                <input type="hidden" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $_GET['email']; ?>" required>
                <input type="hidden" id="complete-code" name="complete_code" required>
                <div class="d-flex justify-content-center mb-3">
                    <input type="text" class="verification-code form-control mx-1" maxlength="1" required>
                    <input type="text" class="verification-code form-control mx-1" maxlength="1" required>
                    <input type="text" class="verification-code form-control mx-1" maxlength="1" required>
                    <input type="text" class="verification-code form-control mx-1" maxlength="1" required>
                    <input type="text" class="verification-code form-control mx-1" maxlength="1" required>
                    <input type="text" class="verification-code form-control mx-1" maxlength="1" required>
                </div>
                <button type="submit" class="btn btn-outline-light w-100">CONTINUE</button>
            </form>
        </div>
    </div>

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
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to show the modal with a custom message
        function showModal(message) {
            document.getElementById('modal-message').textContent = message;
            var modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        }

        // Show modal if there is a message from the PHP code
        document.addEventListener("DOMContentLoaded", function() {
            const message = "<?php echo $message; ?>";
            if (message) {
                showModal(message);
            }
        });

        // Move focus to the next input field after each digit is entered
        document.querySelectorAll('.verification-code').forEach((input, index, inputs) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === "Backspace" && input.value === "" && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Combine all 1-digit verification codes into one string and store it in a hidden input
        document.getElementById('verification-form').addEventListener('submit', (e) => {
            let completeCode = '';
            document.querySelectorAll('.verification-code').forEach(input => {
                completeCode += input.value;
            });
            document.getElementById('complete-code').value = completeCode;
        });
    </script>
</body>
</html>
