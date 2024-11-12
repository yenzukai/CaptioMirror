<?php
// Check if session token is set, if not, check the cookie
if (!isset($_SESSION['session_token']) && isset($_COOKIE['session_token'])) {
    $_SESSION['session_token'] = $_COOKIE['session_token'];
}

if (!isset($_SESSION['session_token'])) {
    header("Location: sign.cm.php");
    exit();
}

$session_token = $_SESSION['session_token'];

// Verify the session token and check if the user is still logged in
$query = "SELECT user_id, logged_in FROM session_tokens WHERE token = ? AND logged_in = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $session_token);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['logged_in'] == 0) {
        header("Location: sign.cm.php");
        exit();
    }
    $userId = $row['user_id'];
    $_SESSION['user_id'] = $userId;
} else {
    header("Location: sign.cm.php");
    exit();
}

$stmt->close();

// Fetch user module preferences
$sql = "SELECT m.name, COALESCE(um.active, 0) as active
        FROM modules m
        LEFT JOIN user_modules um ON m.id = um.module_id AND um.user_id = ?
        ORDER BY m.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$modules = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $modules[$row['name']] = [
            'active' => (bool) $row['active']
        ];
    }
}

// Fetch user preferences
$sql = "SELECT weather_location, date_time_format
        FROM user_preferences
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$userPreferences = [];

if ($row = $result->fetch_assoc()) {
    $userPreferences['weather_location'] = $row['weather_location'];
    $userPreferences['date_time_format'] = $row['date_time_format'];
}

// Fetch user stock symbols
$sql = "SELECT stock_symbol
        FROM user_stock_symbols
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$stock_symbols = [];
while ($row = $result->fetch_assoc()) {
    $stock_symbols[] = $row['stock_symbol'];
}

$userPreferences['stock_symbols'] = $stock_symbols;

// Fetch AI Assistant preferences
$sql = "SELECT assistant_name, voice_assistant, assistant_style
        FROM ai_assistant_preferences
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$aiPreferences = [];

if ($row = $result->fetch_assoc()) {
    $aiPreferences['assistant_name'] = $row['assistant_name'];
    $aiPreferences['voice_assistant'] = $row['voice_assistant'];
    $aiPreferences['assistant_style'] = $row['assistant_style'];
}

$userPreferences['assistant'] = $aiPreferences;
$userPreferences['assistant_active'] = (bool) $modules['Assistant']['active'];
$userPreferences['scheduler_active'] = (bool) $modules['Scheduler']['active'];

?>