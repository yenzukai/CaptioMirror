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
$userId = $_SESSION['user_id'] ?? '';
$username = $_SESSION['username'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>
<body>
   <!-- Modal for Message Alerts (OK) -->
   <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-message">
                    <!-- Dynamic message content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal (Yes/No) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirm-modal-message">
                    <!-- Dynamic confirmation message -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="confirm-yes-button">Yes</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Password Confirmation Modal -->
    <div class="modal fade" id="confirmPasswordModal" tabindex="-1" aria-labelledby="confirmPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmPasswordModalLabel"><strong>Warning!</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <p>This action is irreversible and will permanently delete your account, including all your data and settings.</p>
                <p>You will no longer have access to our services, and your information cannot be recovered.</p>
                <p>However, after you delete your account, you will be able to create a new account with the same email address.</p>
                <p>Please enter your password to confirm:</p>

                    <input type="password" id="confirmPasswordInput" class="form-control" placeholder="Enter password" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmDeleteButton">Confirm</button>
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
            <h1 class="mx-auto text-center mb-0">Settings</h1>
            <div style="width: 40px;"></div>
        </div>

        <!-- Device Settings Section -->
        <div class="settings-section">
            <h5>Device Settings</h5>

            <div class="setting-item">
                <button class="btn btn-account" onclick="backgroundUpload()">Change Background</button>
            </div>

            <div class="setting-item">
                <button class="btn btn-account" onclick="refreshDashboard()">Refresh Dashboard</button>
            </div>
        </div>

        <!-- Account Settings Section -->
        <div class="settings-section">
            <h5>Account Settings</h5>

            <div class="setting-item">
                <button class="btn btn-account" onclick="logout()">Log Out Dashboard</button>
            </div>

            <div class="setting-item">
                <button class="btn btn-account" onclick="deleteAccount()">Delete Account</button>
            </div>
        </div>

        <!-- Hidden Form for Image Upload -->
        <form id="backgroundForm" action="upload_background.php" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" name="background_path" id="backgroundLogoInput" accept="image/*" style="display: none;">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            </form>
        </div>
    </div>

        <script>
            function showMessage(message) {
                document.getElementById('modal-message').innerHTML = message;
                $('#messageModal').modal('show');
            }

            function showConfirmation(message, yesCallback) {
                document.getElementById('confirm-modal-message').innerHTML = message;
                $('#confirmModal').modal('show');
                
                document.getElementById('confirm-yes-button').onclick = function() {
                    yesCallback();  // Execute the passed callback function
                    $('#confirmModal').modal('hide');
                };
            }

            function backgroundUpload() {
                document.getElementById('backgroundLogoInput').click();
                document.getElementById('backgroundLogoInput').onchange = function() {
                    document.getElementById('backgroundForm').submit();
                };
            }

            function refreshDashboard() {
                showConfirmation('Are you sure you want to refresh the dashboard?', function() {
                    // Perform the action if the user clicks Yes
                    fetch('func_core/refresh_dashboard.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ user_id: '<?php echo $userId; ?>' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Your dashboard has been refreshed!');
                        } else {
                            showMessage('Failed to refresh the dashboard. Please try again.');
                        }
                    })
                    .catch(error => showMessage('Error refreshing the dashboard: ' + error.message));
                });
            }

            function logout() {
                showConfirmation('Are you sure you want to log out to the CaptioMirror dashboard?', function() {
                    fetch('func_core/logout_dashboard.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ user_id: '<?php echo $userId; ?>' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('The dashboard is now logging out!');
                        } else {
                            showMessage('Failed to logout the dashboard. Please try again.');
                        }
                    })
                    .catch(error => showMessage('Error logging out the dashboard: ' + error.message));
                });
            }

            function deleteAccount() {
                // Show the password confirmation modal
                $('#confirmPasswordModal').modal('show');
                
                // Set the click event for the confirm button inside the modal
                document.getElementById('confirmDeleteButton').onclick = function() {
                    const password = document.getElementById('confirmPasswordInput').value;
                    
                    if (password) {
                        // Make a request to delete the account
                        fetch('func_core/delete_account.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({ password: password, user_id: '<?php echo $userId; ?>' })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage('Account deleted successfully.');
                                window.location.href = 'user.login.php'; // Redirect to login after deletion
                            } else {
                                showMessage('Incorrect password. Please try again.');
                            }
                        })
                        .catch(error => showMessage('Error deleting account: ' + error.message));
                        
                        // Hide the modal after submitting
                        $('#confirmPasswordModal').modal('hide');
                    } else {
                        showMessage('Password cannot be empty.');
                    }
                };
            }
        </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>

</body>
</html>
