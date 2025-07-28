<?php
session_start();
require 'func_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;
$showBackground = $data['show_background'] ?? null;

if (!$userId || $showBackground === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$sql = "UPDATE user_preferences SET show_background = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $showBackground, $userId);


if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}

$stmt->close();
$conn->close();
?>
