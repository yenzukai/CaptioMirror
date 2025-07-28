<?php
session_start();
require '../core/db_connect.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => 'User not authenticated.']);
    exit();
}

$sql = "SELECT show_background FROM user_preferences WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['showBackground' => $row['show_background']]);
} else {
    echo json_encode(['error' => 'User not found.']);
}

$stmt->close();
$conn->close();
?>
