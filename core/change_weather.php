<?php

session_start();
require 'db_connect.php'; 

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['weather_location'])) {
        $location = $_POST['weather_location'];
        $sql = "UPDATE user_preferences SET weather_location = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $location, $userId);
        if ($stmt->execute()) {
            // Set the reload flag
            updateReloadFlag($conn, $userId);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        exit();
    }
}

?>