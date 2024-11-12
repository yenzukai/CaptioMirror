<?php
session_start();
require '../core/db_connect.php';

if (!isset($_SESSION['session_token'])) {
    // Check if "remember_me" cookie is set
    if (isset($_COOKIE['remember_me'])) {
        $cookie_token = $_COOKIE['remember_me'];

        // Validate the cookie token against the database
        $query = "SELECT * FROM users WHERE remember_token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $cookie_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Cookie is valid, log the user in
            $user = $result->fetch_object();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['phone_number'] = $user->phone_number ?? '';
            $_SESSION['date_of_birth'] = $user->date_of_birth ?? '';
            $_SESSION['profile_picture'] = $user->profile_picture ?? '../assets/svg/account-avatar-default.svg';
            $_SESSION['session_token'] = bin2hex(random_bytes(16)); // Generate a new session token

            // Optional: Regenerate a new `remember_me` token for added security
            $new_cookie_token = bin2hex(random_bytes(32));
            $expiry_time = time() + (86400 * 30); // 30 days
            setcookie('remember_me', $new_cookie_token, $expiry_time, "/");

            // Update the database with the new token
            $update_token_query = "UPDATE users SET remember_token = ? WHERE id = ?";
            $stmt = $conn->prepare($update_token_query);
            $stmt->bind_param("si", $new_cookie_token, $user->id);
            $stmt->execute();
        } else {
            // Invalid token, redirect to login
            header("Location: user.login.php");
            exit();
        }

        $stmt->close();
    } else {
        // No session and no remember_me cookie, redirect to login
        header("Location: user.login.php");
        exit();
    }
}

$user_id = $_SESSION['user_id'];

// Check and set default user preferences
$sql = "SELECT weather_location, date_time_format FROM user_preferences WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_preferences = $stmt->get_result()->fetch_assoc();

if (!$user_preferences) {
    // Insert default preferences if not found
    $default_weather_location = 'New York';
    $default_date_time_format = 'en-US';
    
    $sql = "INSERT INTO user_preferences (user_id, weather_location, date_time_format) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $default_weather_location, $default_date_time_format);
    $stmt->execute();
    
    // Check if the record was inserted successfully
    if ($stmt->affected_rows > 0) {
        // Fetch again after inserting defaults
        $sql = "SELECT weather_location, date_time_format FROM user_preferences WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_preferences = $stmt->get_result()->fetch_assoc();
    } else {
        die("Error: Unable to insert default preferences.");
    }
}

// Check and set default stock symbols
$sql = "SELECT stock_symbol FROM user_stock_symbols WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stock_symbols = [];
while ($row = $result->fetch_assoc()) {
    $stock_symbols[] = $row['stock_symbol'];
}

if (empty($stock_symbols)) {
    // Insert default stock symbols if none exist
    $default_stock_symbols = ['AAPL', 'GOOGL', 'TSLA'];
    $sql = "INSERT INTO user_stock_symbols (user_id, stock_symbol) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($default_stock_symbols as $symbol) {
        $stmt->bind_param("is", $user_id, $symbol);
        $stmt->execute();
    }
    
    // Check if the records were inserted successfully
    if ($stmt->affected_rows > 0) {
        // Fetch again after inserting defaults
        $sql = "SELECT stock_symbol FROM user_stock_symbols WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stock_symbols = [];
        while ($row = $result->fetch_assoc()) {
            $stock_symbols[] = $row['stock_symbol'];
        }
    } else {
        die("Error: Unable to insert default stock symbols.");
    }
}

// Check and set default AI Assistant preferences
$sql = "SELECT assistant_name, voice_assistant, assistant_style FROM ai_assistant_preferences WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ai_preferences = $stmt->get_result()->fetch_assoc();

if (!$ai_preferences) {
    // Insert default AI assistant preferences if not found
    $default_assistant_name = 'Alex';
    $default_voice_assistant = 'henry';
    $default_assistant_style = 'Friendly Assistant';
    
    $sql = "INSERT INTO ai_assistant_preferences (user_id, assistant_name, voice_assistant, assistant_style) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $default_assistant_name, $default_voice_assistant, $default_assistant_style);
    $stmt->execute();
    
    // Check if the record was inserted successfully
    if ($stmt->affected_rows > 0) {
        // Fetch again after inserting defaults
        $sql = "SELECT assistant_name, voice_assistant, assistant_style FROM ai_assistant_preferences WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $ai_preferences = $stmt->get_result()->fetch_assoc();
    } else {
        die("Error: Unable to insert default AI preferences.");
    }
}

// Check if there's a status message to display
$status = $_GET['status'] ?? null;

?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control Panel</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Back Arrow on the left -->
            <a href="user.cm.php">
                <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" class="back-arrow" alt="A Back Arrow">
            </a>

            <!-- Centered Header -->
            <h1 class="mx-auto text-center mb-0">User Preferences</h1>

            <!-- Empty div for flexbox alignment to create space on the right -->
            <div style="width: 40px;"></div>
        </div>

        <?php if ($status == 'success'): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('modal-message').innerText = 'User preferences updated successfully!';
                    var myModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    myModal.show();
                });
            </script>
        <?php elseif ($status == 'error'): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('modal-message').innerText = 'An error occurred while updating user preferences. Please try again.';
                    var myModal = new bootstrap.Modal(document.getElementById('messageModal'));
                    myModal.show();
                });
            </script>
        <?php endif; ?>

        <form action="func_core/save_preferences.php" method="post">
            <div class="mb-3">
                <label for="weatherLocation" class="form-label">Weather Location</label>
                <input type="text" class="form-control" id="weatherLocation" name="weather_location" value="<?= htmlspecialchars($user_preferences['weather_location']) ?>">
            </div>
            <div class="mb-3">
                <label for="dateTimeFormat" class="form-label">Date/Time Format</label>
                <select class="form-select" id="dateTimeFormat" name="date_time_format">
                <option value="en-US" <?= $user_preferences['date_time_format'] == 'en-US' ? 'selected' : '' ?>>en-US (Default)</option>
                    <option value="en-CA" <?= $user_preferences['date_time_format'] == 'en-CA' ? 'selected' : '' ?>>en-CA</option>
                    <option value="en-GB" <?= $user_preferences['date_time_format'] == 'en-GB' ? 'selected' : '' ?>>en-GB</option>
                    <option value="fr-FR" <?= $user_preferences['date_time_format'] == 'fr-FR' ? 'selected' : '' ?>>fr-FR</option>
                    <option value="it-IT" <?= $user_preferences['date_time_format'] == 'it-IT' ? 'selected' : '' ?>>it-IT</option>
                    <option value="es-ES" <?= $user_preferences['date_time_format'] == 'es-ES' ? 'selected' : '' ?>>es-ES</option>
                    <option value="nl-NL" <?= $user_preferences['date_time_format'] == 'nl-NL' ? 'selected' : '' ?>>nl-NL</option>
                    <option value="da-DK" <?= $user_preferences['date_time_format'] == 'da-DK' ? 'selected' : '' ?>>da-DK</option>
                    <option value="ja-JP" <?= $user_preferences['date_time_format'] == 'ja-JP' ? 'selected' : '' ?>>ja-JP</option>
                    <option value="th-TH" <?= $user_preferences['date_time_format'] == 'th-TH' ? 'selected' : '' ?>>th-TH</option>
                    <option value="zh-CN" <?= $user_preferences['date_time_format'] == 'zh-CN' ? 'selected' : '' ?>>zh-CN</option>
                    <option value="en-AU" <?= $user_preferences['date_time_format'] == 'en-AU' ? 'selected' : '' ?>>en-AU</option>
                    <option value="es-CL" <?= $user_preferences['date_time_format'] == 'es-CL' ? 'selected' : '' ?>>es-CL</option>
                    <option value="pt-BR" <?= $user_preferences['date_time_format'] == 'pt-BR' ? 'selected' : '' ?>>pt-BR</option>
                </select>
                </div>
                <div class="mb-3">

        <h3>AI Assistant</h3>
            <label for="assistantName" class="form-label">Assistant Name</label>
            <input type="text" class="form-control" id="assistantName" name="assistant_name" value="<?= htmlspecialchars($ai_preferences['assistant_name']) ?>">
        </div>
        <div class="mb-3">
            <label for="voiceAssistant" class="form-label">Assistant Voices</label>
            <select class="form-select" id="voiceAssistant" name="voice_assistant">
                <option value="Will" <?= $ai_preferences['voice_assistant'] == 'Will' ? 'selected' : '' ?>>en-US-Male-Will</option>
                <option value="Scarlett" <?= $ai_preferences['voice_assistant'] == 'Scarlett' ? 'selected' : '' ?>>en-US-Female-Scarlett</option>
                <option value="Dan" <?= $ai_preferences['voice_assistant'] == 'Dan' ? 'selected' : '' ?>>en-US-Male-Dan</option>
                <option value="Liv" <?= $ai_preferences['voice_assistant'] == 'Liv' ? 'selected' : '' ?>>en-US-Female-Liv</option>
                <option value="Amy" <?= $ai_preferences['voice_assistant'] == 'Amy' ? 'selected' : '' ?>>en-US-Female-Amy</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="assistantStyle" class="form-label">Assistant Style</label>
            <select class="form-select" id="assistantStyle" name="assistant_style">
                <option value="A friendly assistant who loves to chat and assist with the user" <?= $ai_preferences['assistant_style'] == 'A friendly assistant who loves to chat and assist with the user' ? 'selected' : '' ?>>Friendly Assistant (Default)</option>
                <option value="A math tutor who helps students of all levels understand and solve mathematical problems" <?= $ai_preferences['assistant_style'] == 'A math tutor who helps students of all levels understand and solve mathematical problems' ? 'selected' : '' ?>>Math Tutor</option>
                <option value="A career counselor, offering advice and guidance to users seeking to make informed decisions about their professional lives" <?= $ai_preferences['assistant_style'] == 'A career counselor, offering advice and guidance to users seeking to make informed decisions about their professional lives' ? 'selected' : '' ?>>Career Counselor</option>
                <option value="A personal finance advisor, providing guidance on budgeting, saving, investing, and managing debt" <?= $ai_preferences['assistant_style'] == 'A personal finance advisor, providing guidance on budgeting, saving, investing, and managing debt' ? 'selected' : '' ?>>Personal Financial Advisor</option>
                <option value="An AI programming assistant. Follow the requirements of the user carefully and to the letter" <?= $ai_preferences['assistant_style'] == 'An AI programming assistant. Follow the requirements of the user carefully and to the letter' ? 'selected' : '' ?>>Programming Assistant</option>
                <option value="A knowledgeable fitness coach, providing advice on workout routines, nutrition, and healthy habits" <?= $ai_preferences['assistant_style'] == 'A knowledgeable fitness coach, providing advice on workout routines, nutrition, and healthy habits' ? 'selected' : '' ?>>Fitness Coach</option>
                <option value="A Machine Learning Tutor AI, dedicated to guiding senior software engineers in their journey to become proficient machine learning engineers" <?= $ai_preferences['assistant_style'] == 'A Machine Learning Tutor AI, dedicated to guiding senior software engineers in their journey to become proficient machine learning engineers' ? 'selected' : '' ?>>Machine Learning Tutor</option>
                <option value="A tutor that always responds in the Socratic style. You never give the student the answer, but always try to ask just the right question to help them learn to think for themselves" <?= $ai_preferences['assistant_style'] == 'A tutor that always responds in the Socratic style. You never give the student the answer, but always try to ask just the right question to help them learn to think for themselves' ? 'selected' : '' ?>>Socratic Tutor</option>
                <option value="An expert in various scientific disciplines, including physics, chemistry, and biology. Explain scientific concepts, theories, and phenomena in an engaging and accessible way" <?= $ai_preferences['assistant_style'] == 'An expert in various scientific disciplines, including physics, chemistry, and biology. Explain scientific concepts, theories, and phenomena in an engaging and accessible way' ? 'selected' : '' ?>>Science Expert</option>
                <option value="An expert in world history, knowledgeable about different eras, civilizations, and significant events. Provide detailed historical context and explanations when answering questions" <?= $ai_preferences['assistant_style'] == 'An expert in world history, knowledgeable about different eras, civilizations, and significant events. Provide detailed historical context and explanations when answering questions' ? 'selected' : '' ?>>Historical Expert</option>
            </select>
        </div>

        <h3>Stock Prices</h3>
        <div id="stockSymbolsContainer">
            <?php foreach ($stock_symbols as $index => $symbol): ?>
                <div class="mb-3">
                    <label for="stockSymbol<?= $index ?>" class="form-label">Stock Symbol <?= $index + 1 ?></label>
                    <input type="text" class="form-control" id="stockSymbol<?= $index ?>" name="stock_symbols[]" value="<?= htmlspecialchars($symbol) ?>">
                </div>
            <?php endforeach; ?>
        </div>
                    <button type="button" class="btn btn-secondary" onclick="addStockSymbol()">Add Symbol</button>
        <div class="mb-3 text-center">
            <button type="submit" class="btn btn-primary">APPLY</button>
        </div>
                </form>
            </div>

        <!-- Modal for Messages -->
        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel">Notice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-message">
                        <!-- The message will be injected dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="modal-ok-button" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addStockSymbol() {
            const container = document.getElementById('stockSymbolsContainer');
            const index = container.children.length;
            const newSymbolHTML = `
                <div class="mb-3">
                    <label for="stockSymbol${index}" class="form-label">Stock Symbol ${index + 1}</label>
                    <input type="text" class="form-control" id="stockSymbol${index}" name="stock_symbols[]" value="">
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newSymbolHTML);
        }
    </script>

</body>
</html>
