<?php

session_start();
$_SESSION['just_logged_in'] = true;  // Set this flag during login
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_token = $_POST['session_token'];

    // Insert the session token into the database
    $query = "INSERT INTO session_tokens (token, logged_in) VALUES (?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $session_token);
    $stmt->execute();

    // Set the session token in the session and as a cookie
    $_SESSION['session_token'] = $session_token;

    // Set a cookie for the session token
    setcookie('session_token', $session_token, time() + (86400 * 30), "/"); // Expires in 30 days

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

?>
