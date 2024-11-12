    <?php
    session_start();
    require '../core/db_connect.php';
    require '../core/auth.php';

    // Fetch user name from the database or session
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $userName = $row['username'];
    } else {
        $userName = "User";
    }

    $_SESSION['username'] = $userName; // Store in session

    // Fetch the current background path
    $sql = "SELECT background_path FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $backgroundPath = $row['background_path'] ?? "../assets/images/cm2_logo2.png";
    }

    $stmt->close();
    $conn->close();
    ?>

    <!doctype html>
    <html lang="en" data-bs-theme="auto">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <link rel="icon" href="../assets/images/cm2_logo2.png"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="dashboard.css">

        <script>
            // Function to check if the session is still valid
            function checkSession() {
                const sessionToken = '<?= $session_token ?>';

                fetch('../core/check_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        session_token: sessionToken
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        // If the session is no longer valid, redirect to the login page
                        window.location.href = 'sign.cm.php';
                    }
                })
                .catch(error => console.error('Error checking session:', error));
            }

            // Check session validity
            setInterval(checkSession, 300000); // 300000ms = 5 minutes

            // Initial check when page loads
            checkSession();
        </script>

        <script>
            const userName = '<?= $_SESSION['username'] ?>';
            const userPreferences = <?= json_encode($userPreferences) ?>;
        </script>
        
        <script>
            function checkReload() {
            fetch('../core/check_reload.php')
                .then(response => response.json())
                .then(data => {
                    if (data.reloadDashboard) {
                        window.location.reload();
                    }
                })
                .catch(error => console.error('Error checking reload flag:', error));
            }
            setInterval(checkReload, 2000);
        </script>

    </head>
    <body>
        <div class="container-fluid vh-100 d-flex flex-column justify-content-between">
            
            <!-- Top Section: DateTime, Weather, Stock Prices, and ToDo List -->
            <div class="row">
                <?php if ($modules['DateTime']['active']) { include '../modules/datetime_module/datetime.view.php'; } ?>
                <?php if ($modules['Stock Prices']['active']) { include '../modules/stock_module/stock.view.php'; } ?>
                <?php if ($modules['Todo Lists']['active']) { include '../modules/todo_module/todo.view.php'; } ?>
            </div>

            <div id="weather-container">
                <?php if ($modules['Weather']['active']) { include '../modules/weather_module/weather.view.php'; } ?>
            </div>

            <!-- Center: Logo -->
            <div id="logo-container">
                <img src="<?php echo htmlspecialchars($backgroundPath); ?>" alt="CaptioMirror Logo" class="img-fluid">
            </div>

            <!-- Assistance User Interface -->
            <?php if ($modules['Assistant']['active']) { include '../modules/assistant_module/assistant.view.php'; } ?>

            <!-- Bottom Section: Quote of the Day -->
            <div class="row">
                <?php if ($modules['Quote of the Day']['active']) { include '../modules/quote_module/quote.view.php'; } ?>
            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

        <!-- Module Scripts -->
        <?php if ($modules['DateTime']['active']) { ?>
                <script src="../modules/datetime_module/datetime.script.js"></script>
        <?php } ?>
        <?php if ($modules['Weather']['active']) { ?>
            <script src="../modules/weather_module/weather.script.js"></script>
        <?php } ?>
        <?php if ($modules['Stock Prices']['active']) { ?>
            <script src="../modules/stock_module/stock.script.js"></script>
        <?php } ?>
        <?php if ($modules['Todo Lists']['active']) { ?>
            <script src="../modules/todo_module/todo.script.js"></script>
        <?php } ?>
        <?php if ($modules['Assistant']['active']) { ?>
            <script src="../modules/assistant_module/assistant.script.js"></script>
        <?php } ?>
        <?php if ($modules['Quote of the Day']['active']) { ?>
            <script src="../modules/quote_module/quote.script.js"></script>
        <?php } ?>
        <?php if ($modules['Scheduler']['active']) { ?>
            <script src="../modules/schedule_module/schedule.script.js"></script>
        <?php } ?>
        
        <script>
            // Play greeting message when the user logs in using Unreal Speech API
            window.addEventListener('load', async function() {
                <?php if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) { ?>
                    const userName = '<?= $_SESSION['username'] ?>';
                    const greetingMessage = `You have successfully logged into the Smart Mirror. Welcome, ${userName}, to our system!`;

                    // Use Unreal Speech API to speak the greeting
                    async function SpeechSpeak(text) {
                        const API_BASE_URL = "https://api.v7.unrealspeech.com/stream";
                        const API_KEY = "Bearer 7Yfzp1sQCgAYVzit3FezIFz5e2Ubn5clyEW3j1wp6I1IHb5iRd11Gr"; 
                        const VOICE_ID = '<?= $aiPreferences['voice_assistant'] ?>' || 'Will';

                        try {
                            const response = await fetch(API_BASE_URL, {
                                method: "POST",
                                headers: {
                                    "Authorization": API_KEY,
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({
                                    Text: text,
                                    VoiceId: VOICE_ID,
                                    Bitrate: '192k',
                                    Speed: '0',
                                    Pitch: '1',
                                    Codec: 'libmp3lame'
                                })
                            });

                            if (!response.ok) {
                                throw new Error(`${response.status} ${response.statusText}\n${await response.text()}`);
                            }

                            const audioBlob = await response.blob();
                            const audioUrl = URL.createObjectURL(audioBlob);  // Create a blob URL

                            // Create a new audio element to play the greeting
                            const audio = new Audio(audioUrl);
                            audio.play();
                        } catch (error) {
                            console.error('Error with Unreal Speech TTS:', error);
                        }
                    }

                    // Call the function to speak the greeting
                    SpeechSpeak(greetingMessage);

                    <?php
                    // Reset the login flag to avoid greeting on refresh
                    unset($_SESSION['just_logged_in']);
                    ?>
                <?php } ?>
            });
        </script>

    </body>
    </html>
