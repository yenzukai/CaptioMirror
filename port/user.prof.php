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

// Fetch user profile data from session
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone_number = $_SESSION['phone_number'] ?? '';
$date_of_birth = $_SESSION['date_of_birth'] ?? '';
$profilePicture = $_SESSION['profile_picture'] ?? '../assets/svg/account-avatar-default.svg';

$phone_message = '';
$dob_message = '';

if (empty($phone_number)) {
    $phone_message = 'It looks like your phone number has still not been filled in yet. Please, update your phone number.';
}

if (empty($date_of_birth)) {
    $dob_message = 'It looks like your date of birth has still not been filled in yet. Please, update your date of birth.';
}

?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>

<body>
    <div class="d-flex justify-content-center align-items-center min-vh-100"> <!-- Centers vertically and horizontally -->
        <!-- Top Menu -->
        <div class="back-button">
            <a href="user.cm.php"><img src="../assets/svg/light-left-arrow-svgrepo-com.svg" alt="Back" class="back-arrow"></a>
        </div>
        <!-- Profile Edit Form -->
        <div class="d-flex flex-column align-items-center flex-grow-1">
            <form class="mx-auto" style="width: 100%; max-width: 400px;" action="func_core/update_profile.php" method="post">
                <div class="mb-4 text-center">
                    <div class="profile-pic-container">
                        <img src="<?php echo $_SESSION['profile_picture'] ?? '../assets/svg/account-avatar-default.svg'; ?>" alt="User Profile Image" class="profile-img0" id="profileImg">
                        <div class="edit-icon-container">
                            <img src="../assets/svg/dark-edit-svgrepo-com.svg" class="edit-icon" alt="Edit Icon" id="editIcon">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="username" class="form-label">USERNAME</label>
                    <input type="text" class="form-control fixed-width-input" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">EMAIL</label>
                    <input type="email" class="form-control fixed-width-input" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="phone_number" class="form-label">PHONE NUMBER</label>
                    <input type="tel" class="form-control fixed-width-input" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>">
                    <?php if ($phone_message): ?>
                        <small class="text-danger"><?php echo $phone_message; ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-3">
                    <label for="date_of_birth" class="form-label">DATE OF BIRTH</label>
                    <input type="date" class="form-control fixed-width-input" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>">
                    <?php if ($dob_message): ?>
                        <small class="text-danger"><?php echo $dob_message; ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-3 password-container">
                    <label for="password" class="form-label">PASSWORD</label>
                    <input type="password" class="form-control fixed-width-input" id="password" name="password" placeholder="Leave blank if unchanged">
                    <span class="toggle-password2">
                        <img src="../assets/svg/eye-svgrepo-com.svg" alt="Show Password" id="togglePasswordIcon">
                    </span>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary fixed-width-input mx-auto">UPDATE</button>
                </div>
            </form>
        </div>
    </div>
            <!-- Hidden Form for Image Upload -->
            <form id="profilePicForm" action="upload_profile.php" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" name="profile_picture" id="profilePictureInput" accept="image/*" style="display: none;">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="sign.func.js"></script>
    <script>
        document.getElementById('editIcon').addEventListener('click', function() {
        document.getElementById('profilePictureInput').click();
        });

        document.getElementById('profilePictureInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                document.getElementById('profilePicForm').submit();
            }
        });
    </script>
</body>

</html>
