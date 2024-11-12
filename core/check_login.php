<?php

header('Content-Type: application/json'); // JSON response

session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_token = $_POST['session_token'];

    $query = "SELECT logged_in FROM session_tokens WHERE token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $session_token);
    $stmt->execute();
    $result = $stmt->get_result();

    $response = ['logged_in' => false]; // Default response

    if ($row = $result->fetch_assoc()) {
        $response['logged_in'] = $row['logged_in'];
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
} else {
    echo json_encode(['logged_in' => false]);
}

?>
