<?php
session_start();
require 'func_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;
$newTextSize = $data['text_size'] ?? null;

if (!$userId || !$newTextSize) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$sql = "UPDATE user_preferences SET text_size = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newTextSize, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}

$stmt->close();
$conn->close();
?>
