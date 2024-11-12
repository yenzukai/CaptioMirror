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

$userId = $_SESSION['user_id'];

// Check if the ID is provided
if (!isset($_GET['id'])) {
    header("Location: user.sched.php");
    exit();
}

$scheduleId = $_GET['id'];

// Fetch the existing schedule
$sql = "SELECT event_name, description, alarm_before, start_date, end_date FROM schedules WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $scheduleId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: user.sched.php");
    exit();
}

$schedule = $result->fetch_assoc();
$stmt->close();

$message = ''; // To store alert messages
$redirect = ''; // To store redirection URL

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = $_POST['event_name'];
    $description = $_POST['description'];
    $alarmBefore = $_POST['alarm_before'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Validate inputs
    if (empty($eventName) || empty($alarmBefore) || empty($startDate) || empty($endDate)) {
        $message = "Please fill in all required fields";
    } else {
        // Update the schedule
        $sql = "UPDATE schedules SET event_name = ?, description = ?, alarm_before = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissii", $eventName, $description, $alarmBefore, $startDate, $endDate, $scheduleId, $userId);
        if ($stmt->execute()) {
            $message = "An event has been updated successfully!";
            $redirect = "../user.sched.php"; // Redirect URL for after the modal
        } else {
            $message = "An error occured while updating the event. Please, try again";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule</title>
    <link rel="icon" href="../../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../user.cm.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Back Arrow on the left -->
            <a href="../user.sched.php">
                <img src="../../assets/svg/light-left-arrow-svgrepo-com.svg" class="back-arrow" alt="A Back Arrow">
            </a>

            <!-- Centered Header -->
            <h1 class="mx-auto text-center mb-0">Edit Schedule</h1>

            <!-- Empty div for flexbox alignment to create space on the right -->
            <div style="width: 40px;"></div>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label for="eventName" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="eventName" name="event_name" value="<?= htmlspecialchars($schedule['event_name']) ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($schedule['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="alarmBefore" class="form-label">Alarm Before</label>
                <select class="form-select" id="alarmBefore" name="alarm_before">
                    <option value="null" <?= $schedule['alarm_before'] == null ? 'selected' : '' ?>>None</option>
                    <option value="1" <?= $schedule['alarm_before'] == 1 ? 'selected' : '' ?>>1 Minute Before</option>
                    <option value="15" <?= $schedule['alarm_before'] == 15 ? 'selected' : '' ?>>15 Minutes Before</option>
                    <option value="30" <?= $schedule['alarm_before'] == 30 ? 'selected' : '' ?>>30 Minutes Before</option>
                    <option value="60" <?= $schedule['alarm_before'] == 60 ? 'selected' : '' ?>>1 Hour Before</option>
                    <option value="300" <?= $schedule['alarm_before'] == 300 ? 'selected' : '' ?>>5 Hours Before</option>
                    <option value="720" <?= $schedule['alarm_before'] == 720 ? 'selected' : '' ?>>12 Hours Before</option>
                    <option value="1440" <?= $schedule['alarm_before'] == 1440 ? 'selected' : '' ?>>1 Day Before</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="startDate" class="form-label">Start Date & Time</label>
                <input type="datetime-local" class="form-control" id="startDate" name="start_date" value="<?= htmlspecialchars($schedule['start_date']) ?>">
            </div>
            <div class="mb-3">
                <label for="endDate" class="form-label">End Date & Time</label>
                <input type="datetime-local" class="form-control" id="endDate" name="end_date" value="<?= htmlspecialchars($schedule['end_date']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Schedule</button>
        </form>
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
            const message = "<?php echo addslashes($message); ?>"; // Escape message for JS
            const redirect = "<?php echo $redirect; ?>"; // Get redirect URL if exists
            if (message) {
                showModal(message, redirect);
            }
        });
    </script>
</body>
</html>
