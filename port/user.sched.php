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

// Fetch schedules
$sql = "SELECT id, event_name, description, alarm_before, start_date, end_date FROM schedules WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>
<body>
<div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="user.cm.php">
                <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" class="back-arrow" alt="A Back Arrow">
            </a>
            <h1 class="mx-auto text-center mb-0">Schedules</h1>
            <div style="width: 40px;"></div>
        </div>

        <?php if (empty($schedules)): ?>
            <p class="text-center">You have no schedules at the moment</p>
        <?php else: ?>
            <!-- Responsive Table Wrapper -->
            <div class="table-responsive">
                <table class="table1 table-striped">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Description</th>
                            <th>Alarm Before</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?= htmlspecialchars($schedule['event_name']) ?></td>
                                <td><?= htmlspecialchars($schedule['description']) ?></td>
                                <td>
                                    <?php
                                        $alarm = $schedule['alarm_before'];
                                        echo ($alarm >= 60) ? ($alarm / 60) . " Hour(s) Before" : $alarm . " Minute(s) Before";
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($schedule['start_date']) ?></td>
                                <td><?= htmlspecialchars($schedule['end_date']) ?></td>
                                <td>
                                    <a href="func_core/edit_schedule.php?id=<?= $schedule['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-schedule-id="<?= $schedule['id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mb-3 text-center">
            <a href="func_core/add_schedule.php" class="btn btn-primary">Add Schedule</a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this event?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-no" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn-modal-yes" id="confirmDelete">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget; // Button that triggered the modal
            const scheduleId = button.getAttribute('data-schedule-id'); // Extract info from data-* attributes
            const confirmDelete = document.getElementById('confirmDelete');

            confirmDelete.onclick = function() {
                window.location.href = `func_core/delete_schedule.php?id=${scheduleId}`;
            };
        });
    </script>
</body>
</html>
