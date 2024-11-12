<?php
session_start();
require '../core/db_connect.php';

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

$userId = $_SESSION['user_id'];

// Mark all unread notifications as read
$updateSql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("i", $userId);
$updateStmt->execute();
$updateStmt->close();

// Fetch notifications for the user
$sql = "SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_cleared = 0 ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Back Arrow -->
            <a href="user.cm.php">
                <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" class="back-arrow" alt="A Back Arrow">
            </a>
            <!-- Centered Header -->
            <h1 class="mx-auto text-center mb-0">Notifications</h1>
            <div style="width: 40px;"></div>
        </div>
        
        <div class="container-fluid h-100 d-flex flex-column">
            <!-- Notification Content -->
            <div class="d-flex flex-column justify-content-center align-items-center">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-box0">
                            <h6><?php echo htmlspecialchars($notification['message']); ?></h6>
                            <small><?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?></small>
                            <button class="btn btn-danger btn-sm btn-clear" onclick="clearNotification(<?php echo $notification['id']; ?>)">Clear</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h3 class="no-notification">No new notifications</h3>
                <?php endif; ?>
            </div>

            <!-- Clear All Button -->
            <?php if (count($notifications) > 0): ?>
                <button class="btn btn-danger clear-all-btn" onclick="clearAllNotifications()">Clear All Notifications</button>
            <?php endif; ?>
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
                    <button type="button" class="btn btn-primary" id="modal-ok-button" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to show message modal with a specific message
        function showMessageModal(message) {
            document.getElementById('modal-message').innerText = message;
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        }

        // Clear individual notification
        function clearNotification(notificationId) {
            fetch('func_core/clear_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload the page to update the list of notifications
                } else {
                    showMessageModal('The system failed to clear notification.');
                }
            });
        }

        // Clear all notifications
        function clearAllNotifications() {
            fetch('func_core/clear_all_notification.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload the page to update the list of notifications
                } else {
                    showMessageModal('The system failed to clear all notifications.');
                }
            });
        }
    </script>

</body>

</html>
