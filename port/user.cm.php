<?php
session_start();
require '../core/db_connect.php';

// If no session token, check for "remember_me" cookie
if (!isset($_SESSION['session_token']) && isset($_COOKIE['remember_me'])) {
    $cookie_token = $_COOKIE['remember_me'];

    // Verify the cookie token with the database
    $query = "SELECT * FROM users WHERE remember_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cookie_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_object();

        // Recreate session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['phone_number'] = $user->phone_number;
        $_SESSION['date_of_birth'] = $user->date_of_birth;
        $_SESSION['profile_picture'] = $user->profile_picture ?? '../assets/svg/account-avatar-default.svg';
        $_SESSION['session_token'] = bin2hex(random_bytes(16));

        // Refresh the remember token in the database for added security
        $new_cookie_token = bin2hex(random_bytes(32));
        $update_token_query = "UPDATE users SET remember_token = ? WHERE id = ?";
        $stmt->prepare($update_token_query);
        $stmt->bind_param("si", $new_cookie_token, $user->id);
        $stmt->execute();

        // Update the "remember_me" cookie with the new token
        setcookie('remember_me', $new_cookie_token, time() + (86400 * 30), "/");
    } else {
        // If the token is invalid, redirect to login page
        header("Location: user.login.php");
        exit();
    }

    $stmt->close();
    $result->close();
}

// Redirect to login page if neither session nor valid cookie is present
if (!isset($_SESSION['session_token'])) {
    header("Location: user.login.php");
    exit();
}

// User-specific code, such as fetching unread notifications
$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];
$email = $_SESSION['email'];
$profilePicture = $_SESSION['profile_picture'] ?? '../assets/svg/account-avatar-default.svg';

$message = '';
if (isset($_GET['alert'])) {
    $message = ($_GET['alert'] == 'success') ? "You have successfully logged in!" : "An error occurred. Please try again.";
}

// Fetch unread notifications count
$sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0 AND is_cleared = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$unreadCount = $result->fetch_assoc()['unread_count'];

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>

<body>
    <div class="container-fluid h-100 d-flex flex-column">
        <!-- Top Menu -->
        <div class="d-flex justify-content-between align-items-center p-3">
            <div id="menu-left-container">
                <img src="../assets/svg/light-burger-menu-svgrepo-com.svg" class="burger-menu" alt="A Burger Menu" onclick="toggleSidebar()">
            </div>
            <div id="menu-right-container" class="d-flex align-items-center">
                <button type="button" class="btn btn me-3" onclick="openQrScanner()">
                    <img src="../assets/svg/qr-code-svgrepo-com.svg" alt="QR Code Icon" width="30" height="30">
                </button>
                <a href="user.notif.php"><img src="../assets/svg/<?php echo ($unreadCount > 0) ? 'light-notification-alert-svgrepo-com.svg' : 'light-notification-svgrepo-com.svg'; ?>" class="notification" alt="A Notification Bell"></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <div id="sidebar-menu" class="sidebar-menu">
            <div class="profile-section">
                <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="User Profile Image" class="profile-img" id="profileImg">
                <div class="profile-info">
                    <h6><?php echo htmlspecialchars($username); ?></h6>
                    <p><?php echo htmlspecialchars($email); ?></p>
                </div>
            </div>
            <ul class="menu-list">
                <li><a href="user.prof.php">Profile</a></li>
                <li><a href="user.sched.php">Schedules</a></li>
                <li><a href="user.todo.php">Tasks</a></li>
                <li><a href="user.mod.php">Modules</a></li>
                <li><a href="user.con.php">Controls</a></li>
                <li><a href="user.set.php">Settings</li>
                <li><a href="user.docs.php">Docs</li>
                <li><a href="func_core/user.logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- QR Code Scanner Modal -->
        <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #151515; color: white;">
                    <div class="modal-header" style="border-top: 3px solid #ffffff; border-right: 3px solid #ffffff; border-left: 3px solid #ffffff; border-radius: 0px;">
                        <h5 class="modal-title" id="qrScannerModalLabel">QR Login Scanner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
                    </div>
                    <div class="modal-body" style="border: 3px solid #ffffff; border-radius: 0px; position: relative;">
                        <div id="qr-scanner-overlay">
                            <img src="../assets/svg/light-square-dashed-2-svgrepo-com.svg" alt="QR Scanner Frame" id="qr-frame">
                            <div id="scanner-line"></div> <!-- Animated scanning line -->
                            <video id="qr-video" class="w-100" style="position: relative; z-index: 1;"></video>
                        </div>
                        <button type="button" class="btn btn-secondary mt-3" id="flip-camera-btn" onclick="flipCamera()">
                            <img src="../assets/svg/flip-camera-svgrepo-com.svg" alt="Flip Camera Icon" width="30" height="30">
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo -->
        <div class="d-flex flex-column justify-content-center align-items-center vh-100 text-center">
            <div id="cm-logo">
                <img src="../assets/images/cm2_logo2.png" class="img-fluid" alt="The logo of the CaptioMirror">
            </div>
        </div>
    </div>

    <!-- Modal for Custom Messages -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-message">
                    <?php echo $message; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="modal-ok-button" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/qr-scanner.umd.min.js"></script>
    <script>
        // Show modal if there is a message
        document.addEventListener("DOMContentLoaded", function() {
            const message = "<?php echo addslashes($message); ?>"; // Use addslashes to escape quotes
            if (message) {
                var modal = new bootstrap.Modal(document.getElementById('messageModal'));
                modal.show();
            }
        });

        // Existing JavaScript functions
        let currentStream;
        let currentCamera = 'environment';

        function openQrScanner() {
            const qrScannerModal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
            qrScannerModal.show();
            startScanner();
        }

        function startScanner() {
            const video = document.getElementById('qr-video');
            console.log('Initializing QR Scanner...');
            navigator.mediaDevices.getUserMedia({ video: { facingMode: currentCamera } })
                .then(stream => {
                    currentStream = stream; // Save the stream to stop it later
                    video.srcObject = stream;
                    const qrScanner = new QrScanner(video, result => {
                        console.log('Decoded QR Code:', result);
                        qrScanner.stop(); // Stop scanning once a QR code is detected
                        stopScanner(); // Stop the camera
                        handleQrCode(result);
                    });

                    qrScanner.start().then(() => {
                        console.log('QR Scanner started successfully.');
                    }).catch(err => {
                        console.error('Failed to start QR Scanner:', err);
                        showCustomAlert('The system could not start the QR scanner. Please try again.');
                    });
                })
                .catch(err => {
                    console.error('Failed to access the camera:', err);
                    showCustomAlert('Camera access is required to scan QR codes. Please enable camera permissions and try again.');
                });
        }

        function flipCamera() {
            currentCamera = currentCamera === 'environment' ? 'user' : 'environment'; // Toggle between front and rear
            stopScanner(); // Stop the current stream
            startScanner(); // Restart the scanner with the new camera
        }

        function stopScanner() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop()); // Stop the camera
            }
        }

        function handleQrCode(token) {
            fetch('func_core/user.qr.login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCustomAlert('You have successfully logged into our system!', "user.cm.php");
                } else {
                    showCustomAlert('You have failed to logged into our system! Please try again.');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function showCustomAlert(message, redirectUrl = null) {
            document.getElementById('modal-message').textContent = message;
            var modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
            document.getElementById('modal-ok-button').onclick = function() {
                if (redirectUrl) {
                    window.location.href = redirectUrl; // Redirect if a URL is provided
                }
            };
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById('sidebar-menu');
            const toggleButton = document.querySelector('.burger-menu');

            // Function to toggle the sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
            }

            // Close sidebar if clicked outside of it
            document.addEventListener('click', function(event) {
                if (sidebar.classList.contains('active') &&
                    !sidebar.contains(event.target) &&
                    !toggleButton.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            });

            // Event listener for the burger menu button
            toggleButton.addEventListener('click', toggleSidebar);
        });
    </script>

</body>
</html>
