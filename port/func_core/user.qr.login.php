<?php

session_start();
require 'func_connect.php';

header('Content-Type: application/json'); // Ensure the correct content type is sent

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $dashboard_token = $data['token'];

    // Check if the session token is valid
    $query = "SELECT * FROM session_tokens WHERE token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $dashboard_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $_SESSION['user_id'];

        // Update the session on the smart mirror to log the user in
        $update_query = "UPDATE session_tokens SET user_id = ?, logged_in = 1 WHERE token = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("is", $user, $dashboard_token);
        $update_stmt->execute();

        // Store the dashboard_token in the session
        $_SESSION['dashboard_token'] = $dashboard_token;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false]);
}

?>

