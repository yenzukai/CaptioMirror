<?php
session_start();
require '../core/db_connect.php';

$message = '';
$redirect_url = 'user.set.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['background_path'])) {
    $uploadDir = '../uploads/background_logo/';
    $username = $_POST['username'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        header("Location: user.login.php");
        exit();
    }

    // Define allowed file extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($_FILES['background_path']['name'], PATHINFO_EXTENSION));

    // Validate file extension
    if (!in_array($fileExtension, $allowedExtensions)) {
        $message = "Invalid file type. Please upload a jpg, jpeg, png, or gif image.";
    } else {
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique file name
        $fileName = $username . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['background_path']['tmp_name'], $filePath)) {
            $_SESSION['background_path'] = $filePath;

            $update_query = "UPDATE users SET background_path = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $filePath, $userId);

            if ($stmt->execute()) {
                $message = "Your background image has been successfully updated.";
                // Set the reload flag
                $sql = "SELECT id FROM reload_flags WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Update existing reload flags
                    $sql = "UPDATE reload_flags SET reload = 1 WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                } else {
                    // Insert new reload flags entry
                    $sql = "INSERT INTO reload_flags (user_id, reload) VALUES (?, 1)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                }
            } else {
                $message = "The system failed to update your background image.";
            }
            $stmt->close();
        } else {
            $message = "The system failed to upload the file.";
        }
    }
    $conn->close();
} else {
    header("Location: user.set.php");
    exit();
}
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Background Logo Upload</title>
    <link rel="icon" href="../../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
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
            const message = "<?php echo addslashes($message); ?>"; // Safely escape the message
            const redirect = "<?php echo $redirect_url; ?>"; // Get redirect URL if exists
            if (message) {
                showModal(message, redirect);
            }
        });
    </script>
</body>
</html>
