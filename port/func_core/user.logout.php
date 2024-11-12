<?php

session_start();
require 'func_connect.php';

$dashboard_token = $_SESSION['dashboard_token'] ?? null; // Retrieve the dashboard token from the session
$userId = $_SESSION['user_id'] ?? null; // Retrieve user_id from the session

if (!$userId) {
    die('Error: user_id is currently missing in your session right now');
}

// Clear the remember_token from the database for this user
$clearTokenQuery = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
$clearTokenQuery->bind_param("i", $userId);
$clearTokenQuery->execute();
$clearTokenQuery->close();

// Clear session data
session_unset();
session_destroy();

// Clear session token cookie
if (isset($_COOKIE['session_token'])) {
    setcookie('session_token', '', time() - 3600, "/");
}

// Redirect to the login page
header("Location: ../user.login.php");
exit();

?>
