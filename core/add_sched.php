<?php 
session_start();
require 'db_connect.php';

// Ensure the user is authorized before proceeding
if (!isset($_SESSION['session_token'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$userId = $_SESSION['user_id'];

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = $_POST['event_name'] ?? null;
    $description = $_POST['description'] ?? '';  // Set default as empty string
    $alarmBefore = $_POST['alarm_before'] ?? null;
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    
    // Function to check if date includes a year
    function hasYear($dateString) {
        return preg_match('/\b\d{4}\b/', $dateString);
    }

    // Function to check if time is included in the date string
    function hasTime($dateString) {
        return preg_match('/\d{1,2}:\d{2}/', $dateString);
    }

    // Get the current date and year
    $currentYear = date('Y');
    $currentDate = date('Y-m-d H:i:s');
    $defaultTime = '12:00 AM';

    // Check and append current year if not provided in startDate
    if (!hasYear($startDate)) {
        $startDate .= " $currentYear";
    }

    // Check and append current year if not provided in endDate
    if (!hasYear($endDate)) {
        $endDate .= " $currentYear";
    }

    // Check and append default time if not provided in startDate
    if (!hasTime($startDate)) {
        $startDate .= " $defaultTime";
    }

    // Check and append default time if not provided in endDate
    if (!hasTime($endDate)) {
        $endDate .= " $defaultTime";
    }

    // Convert human-readable date to MySQL format
    $startDate = date('Y-m-d H:i:s', strtotime($startDate));
    $endDate = date('Y-m-d H:i:s', strtotime($endDate));

    // If the event is already passed, schedule for the next year
    if ($startDate < $currentDate) {
        $nextYear = $currentYear + 1;
        $startDate = date('Y-m-d H:i:s', strtotime(str_replace($currentYear, $nextYear, $startDate)));
        $endDate = date('Y-m-d H:i:s', strtotime(str_replace($currentYear, $nextYear, $endDate)));
    }

    // Validate inputs
    if (empty($eventName) || empty($startDate) || empty($endDate)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit();
    }

    // Insert into schedules table
    $sql = "INSERT INTO schedules (user_id, event_name, description, alarm_before, start_date, end_date) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'SQL prepare error', 'error' => $conn->error]);
        exit();
    }

    // Now bind all 6 parameters: user_id, event_name, description, alarm_before, start_date, end_date
    $stmt->bind_param("isssss", $userId, $eventName, $description, $alarmBefore, $startDate, $endDate);

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'An event has been added successfully!']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while adding the event. Please try again.', 'error' => $stmt->error]);
    }

    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
