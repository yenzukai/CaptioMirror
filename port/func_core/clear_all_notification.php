<?php
session_start();
require 'func_connect.php';

$sql = "UPDATE notifications SET is_cleared = 1 WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
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
