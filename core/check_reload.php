<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['reloadDashboard' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Query the reload_flags table to check if the dashboard needs to be reloaded
$sql = "SELECT reload FROM reload_flags WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reloadDashboard = false;

if ($row = $result->fetch_assoc()) {

    if ($row['reload'] == 1) {
        $reloadDashboard = true;
        // Reset the reload flag to false after checking
        $resetSql = "UPDATE reload_flags SET reload = 0 WHERE user_id = ?";
        $resetStmt = $conn->prepare($resetSql);
        $resetStmt->bind_param("i", $user_id);
        $resetStmt->execute();
    }
}


echo json_encode(['reloadDashboard' => $reloadDashboard]);

$stmt->close();
$conn->close();
?>
