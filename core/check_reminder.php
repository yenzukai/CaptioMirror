<?php

session_start();
date_default_timezone_set('Asia/Manila');

require 'db_connect.php';
$currentTime = date('Y-m-d H:i:s');

// Query for upcoming reminders 
$sql = "SELECT u.username, s.event_name, s.description, s.start_date, s.alarm_before 
        FROM schedules s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.start_date >= ? 
        AND s.start_date <= DATE_ADD(?, INTERVAL s.alarm_before MINUTE)
        AND s.notified = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $currentTime, $currentTime);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            'username' => $row['username'],
            'event_name' => $row['event_name'],
            'description' => $row['description'],
            'start_date' => $row['start_date'],
            'alarm_before' => $row['alarm_before']
        ];
    }
}

$stmt->close();
$conn->close();

// Send reminders as JSON
header('Content-Type: application/json');
echo json_encode($reminders);

?>
