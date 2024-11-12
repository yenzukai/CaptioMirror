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

$user_id = $_SESSION['user_id'];

// Save other preferences
$weather_location = $_POST['weather_location'] ?? '';
$date_time_format = $_POST['date_time_format'] ?? '';
$assistant_name = $_POST['assistant_name'] ?? '';
$voice_assistant = $_POST['voice_assistant'] ?? '';
$assistant_style = $_POST['assistant_style'] ?? '';

// Check if preferences already exist for the user
$sql = "SELECT id FROM user_preferences WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing preferences
    $sql = "UPDATE user_preferences SET weather_location = ?, date_time_format = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $weather_location, $date_time_format, $user_id);
} else {
    // Insert new preferences
    $sql = "INSERT INTO user_preferences (user_id, weather_location, date_time_format) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $weather_location, $date_time_format);
}

if (!$stmt->execute()) {
    header("Location: ../user.con.php?status=error");
    exit();
}

// Delete existing stock symbols
$sql = "DELETE FROM user_stock_symbols WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Insert new stock symbols
$stock_symbols = $_POST['stock_symbols'] ?? [];
$sql = "INSERT INTO user_stock_symbols (user_id, stock_symbol) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

foreach ($stock_symbols as $symbol) {
    $symbol = trim($symbol);
    if (!empty($symbol)) {
        $stmt->bind_param("is", $user_id, $symbol);
        $stmt->execute();
    }
}

// Handle AI Assistant preferences
$sql = "SELECT id FROM ai_assistant_preferences WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing AI Assistant preferences
    $sql = "UPDATE ai_assistant_preferences SET assistant_name = ?, voice_assistant = ?, assistant_style = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $assistant_name, $voice_assistant, $assistant_style, $user_id);
} else {
    // Insert new AI Assistant preferences
    $sql = "INSERT INTO ai_assistant_preferences (user_id, assistant_name, voice_assistant, assistant_style) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $assistant_name, $voice_assistant, $assistant_style);
}

if (!$stmt->execute()) {
    header("Location: ../user.con.php?status=error");
    exit();
}

header("Location: ../user.con.php?status=success");

// Set the reload flag
$sql = "SELECT id FROM reload_flags WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing reload flags
    $sql = "UPDATE reload_flags SET reload = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
} else {
    // Insert new reload flags entry
    $sql = "INSERT INTO reload_flags (user_id, reload) VALUES (?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

exit();

?>
