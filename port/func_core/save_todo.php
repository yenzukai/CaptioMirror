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

// Function to update the reload flag in the database
function updateReloadFlag($conn, $userId) {
    // Check if a reload flag already exists for the user
    $sql = "SELECT id FROM reload_flags WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing reload flag
        $sql = "UPDATE reload_flags SET reload = 1 WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    } else {
        // Insert a new reload flag entry
        $sql = "INSERT INTO reload_flags (user_id, reload) VALUES (?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
}

// Handle the POST request (adding or removing tasks)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['task_done_id'])) {
        // Mark task as done (delete it from the database)
        $taskId = $_POST['task_done_id'];
        $sql = "DELETE FROM user_todo_lists WHERE user_id = ? AND id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $taskId);
        $stmt->execute();
        $stmt->close();

        // Set the reload flag
        updateReloadFlag($conn, $userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => 'The task has been removed successfully!']);
        exit();
    }

    // Add a new task
    $task = $_POST['task'];
    $sql = "INSERT INTO user_todo_lists (user_id, task) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $task);
    $stmt->execute();
    $stmt->close();

    // Set the reload flag
    updateReloadFlag($conn, $userId);

    header('Content-Type: application/json');
    echo json_encode(['success' => 'The task has been added successfully!']);
    exit();
}

// Handle the GET request (fetching tasks)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM user_todo_lists WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    $stmt->close();

    // Return tasks as JSON
    header('Content-Type: application/json');
    echo json_encode($tasks);
    exit();
}

?>