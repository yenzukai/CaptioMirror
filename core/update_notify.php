<?php

session_start();
require 'db_connect.php';

// Get the data from the request
$data = json_decode(file_get_contents("php://input"), true);
$eventName = $data['event_name'];
$userName = $_SESSION['username'];

// Retrieve the event details
$sql = "SELECT * FROM schedules WHERE event_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $eventName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $eventDetails = $result->fetch_assoc();

    // Check if the same event already exists in the 'schedules_history' table
    $sqlCheckHistory = "SELECT COUNT(*) AS count FROM schedules_history WHERE event_name = ? AND start_date = ?";
    $stmtCheckHistory = $conn->prepare($sqlCheckHistory);
    $stmtCheckHistory->bind_param("ss", $eventDetails['event_name'], $eventDetails['start_date']);
    $stmtCheckHistory->execute();
    $stmtCheckHistory->bind_result($countHistory);
    $stmtCheckHistory->fetch();
    $stmtCheckHistory->close();

    // Only insert into 'schedules_history' if no such entry exists
    if ($countHistory == 0) {
        $sqlInsertHistory = "INSERT INTO schedules_history (user_id, event_name, description, start_date, alarm_before, created_at)
                            VALUES (?, ?, ?, ?, ?, NOW())";
        $stmtInsert = $conn->prepare($sqlInsertHistory);
        $stmtInsert->bind_param(
            "isssi",
            $eventDetails['user_id'],
            $eventDetails['event_name'],
            $eventDetails['description'],
            $eventDetails['start_date'],
            $eventDetails['alarm_before']
        );
        $stmtInsert->execute();
        $stmtInsert->close();
    }

    // Update the 'notified' column in the 'schedules' table
    $sqlUpdate = "UPDATE schedules SET notified = 1 WHERE event_name = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("s", $eventName);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Check if the same notification already exists in the 'notifications' table
    $sqlCheckNotification = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND message = ?";
    $stmtCheck = $conn->prepare($sqlCheckNotification);
    $message = "A reminder for you, $userName: " . $eventDetails['event_name'] . " is scheduled at " . $eventDetails['start_date'];
    $stmtCheck->bind_param("is", $eventDetails['user_id'], $message);
    $stmtCheck->execute();
    $stmtCheck->bind_result($count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    // Only insert the notification if it doesn't already exist
    if ($count == 0) {
        $sqlInsertNotification = "INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())";
        $stmtNotif = $conn->prepare($sqlInsertNotification);
        $stmtNotif->bind_param("is", $eventDetails['user_id'], $message);
        $stmtNotif->execute();
        $stmtNotif->close();
    }

}

$stmt->close();
$conn->close();

?>
