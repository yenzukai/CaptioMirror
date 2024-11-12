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

// Fetch user ID
$userId = $_SESSION['user_id'];

// Fetch current module states for the user
$sql = "SELECT m.id, m.name, COALESCE(um.active, 0) as active
        FROM modules m
        LEFT JOIN user_modules um ON m.id = um.module_id AND um.user_id = ?
        ORDER BY m.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$modules = $stmt->get_result();

$activeModules = [];
$inactiveModules = [];

while ($module = $modules->fetch_assoc()) {
    if ($module['active']) {
        $activeModules[] = $module;
    } else {
        $inactiveModules[] = $module;
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
    <title>Modules</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
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

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Back Arrow -->
            <a href="user.cm.php">
                <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" class="back-arrow" alt="A Back Arrow">
            </a>
            <!-- Centered Header -->
            <h1 class="mx-auto text-center mb-0">Manage Modules</h1>
            <div style="width: 40px;"></div>
        </div>

        <!-- Modules Form -->
        <form id="module-form">
            <div class="d-flex justify-content-between">
                <!-- Active Modules Table -->
                <div class="module-section1">
                    <h5>Active Modules:</h5>
                    <table class="table1 table-bordered">
                        <thead>
                            <tr><th>Module</th></tr>
                        </thead>
                        <tbody id="active-modules">
                            <?php foreach ($activeModules as $module): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="module-checkbox" data-id="<?= $module['id']; ?>" checked>
                                    <?= htmlspecialchars($module['name']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Move Buttons -->
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <button type="button" class="btn btn-light mb-2" id="move-to-active"><img src="../assets/svg/light-left-arrow-svgrepo-com.svg"></button>
                    <button type="button" class="btn btn-light" id="move-to-inactive"><img src="../assets/svg/light-right-arrow-svgrepo-com.svg"></button>
                </div>

                <!-- Inactive Modules Table -->
                <div class="module-section1">
                    <h5>Inactive Modules:</h5>
                    <table class="table1 table-bordered">
                        <thead>
                            <tr><th>Module</th></tr>
                        </thead>
                        <tbody id="inactive-modules">
                            <?php foreach ($inactiveModules as $module): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="module-checkbox" data-id="<?= $module['id']; ?>">
                                    <?= htmlspecialchars($module['name']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">APPLY</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Move selected module from inactive to active
        document.getElementById('move-to-active').addEventListener('click', function() {
            moveModules('inactive-modules', 'active-modules');
        });

        // Move selected module from active to inactive
        document.getElementById('move-to-inactive').addEventListener('click', function() {
            moveModules('active-modules', 'inactive-modules');
        });

        // Function to move selected modules between tables
        function moveModules(fromTableId, toTableId) {
            const fromTable = document.getElementById(fromTableId);
            const toTable = document.getElementById(toTableId);
            const selectedModules = fromTable.querySelectorAll('input[type="checkbox"]:checked');

            selectedModules.forEach(function(module) {
                const row = module.closest('tr');
                toTable.appendChild(row);
                module.checked = false;  // Uncheck after moving
            });
        }

        // Submit form and update the module states
        document.getElementById('module-form').addEventListener('submit', function(event) {
            event.preventDefault();

            let modules = [];
            document.querySelectorAll('.module-checkbox').forEach(function(checkbox) {
                modules.push({
                    id: checkbox.getAttribute('data-id'),
                    active: checkbox.closest('tbody').id === 'active-modules' ? 1 : 0
                });
            });

            fetch('func_core/save_modules.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ modules: modules })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModal('Your modules has been updated successfully!');
                } else {
                    showModal('You have failed to update your modules.');
                }
            })
            .catch(error => {
                console.error('An error occured in updating your modules:', error);
            });
        });

        // Function to show the modal with a custom message
        function showModal(message) {
            document.getElementById('modal-message').textContent = message;
            var modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        }
    </script>



</body>
</html>
