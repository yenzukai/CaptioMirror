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
$password = $_POST['password'] ?? '';

// Verify the password
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (password_verify($password, $user['password'])) {
    // Start transaction
    $conn->begin_transaction();
    try {
        // Step 1: Copy user data to users_history table
        $historyStmt = $conn->prepare("INSERT INTO users_history (id, username, email, password, created_at) VALUES (?, ?, ?, ?, ?)");
        $historyStmt->bind_param("issss", $user['id'], $user['username'], $user['email'], $user['password'], $user['created_at']);
        $historyStmt->execute();

        // Step 2: Delete user-related records in various tables
        $tables = ['ai_assistant_preferences', 'user_modules', 'user_preferences', 'schedules', 'user_stock_symbols', 'user_todo_lists', 'notifications'];
        
        foreach ($tables as $table) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }

        // Step 3: Delete user record from the users table
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Clear session data
        session_unset();
        session_destroy();

        // Clear session token cookie
        if (isset($_COOKIE['session_token'])) {
            setcookie('session_token', '', time() - 3600, "/");
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete account.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
}
?>
