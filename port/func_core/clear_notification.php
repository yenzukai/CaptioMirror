<?php
session_start();
require 'func_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['id'];

$sql = "UPDATE notifications SET is_cleared = 1 WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $notificationId, $_SESSION['user_id']);
$stmt->execute();

$response = [];
if ($stmt->affected_rows > 0) {
    $response['success'] = true;
} else {
    $response['success'] = false;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
