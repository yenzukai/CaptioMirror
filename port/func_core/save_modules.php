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

// Fetch user ID
$userId = $_SESSION['user_id'];

// Read and decode JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['modules'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$modules = $input['modules'];
$success = true;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("REPLACE INTO user_modules (user_id, module_id, active) VALUES (?, ?, ?)");

    foreach ($modules as $module) {
        $stmt->bind_param("iii", $userId, $module['id'], $module['active']);
        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $success = false;
}

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

echo json_encode(['success' => $success]);

?>
