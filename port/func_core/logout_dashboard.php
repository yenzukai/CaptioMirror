<?php
require 'func_connect.php';
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dashboard_token = $_SESSION['dashboard_token'] ?? null; // Retrieve the dashboard token from the session
    $userId = $_POST['user_id'];

    if ($dashboard_token) {
        // Update the database to mark the session as logged out
        $query = "UPDATE session_tokens SET logged_in = 0 WHERE token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $dashboard_token);
        $stmt->execute();
        $stmt->close();
    }
    
    if ($userId) {
        // Check if a reload flag entry exists for the user
        $sql = "SELECT id FROM reload_flags WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update the existing reload flag
            $sql = "UPDATE reload_flags SET reload = 1 WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        } else {
            // Insert a new reload flag
            $sql = "INSERT INTO reload_flags (user_id, reload) VALUES (?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }

        // Send a success response back to the client
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID missing.']);
    }
}
?>