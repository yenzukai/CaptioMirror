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

    // Fetch show_background and text size from the database or session
    $sql = "SELECT show_background, text_size FROM user_preferences WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $showBackground = $row['show_background'] ?? 1;
        $textSize = $row['text_size'] ?? 'normal';
    } else {
        $showBackground = 1;
        $textSize = 'normal';
    }

    $_SESSION['text_size'] = $textSize; // Store text size preference

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

        <script>
            // Poll for text size preference updates
            setInterval(() => {
                fetch('../core/check_text_size.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.textSize !== undefined) {
                            // Determine the scaling factor based on user preference
                            const scaleFactor = 
                                data.textSize === 'small' ? 0.75 :  // Reduce size for 'small'
                                (data.textSize === 'large' ? 1.25 : 1); // Increase size for 'large', 1 is normal

                            // Apply scaling factor to font size
                            document.body.style.fontSize = `${scaleFactor * 18}px`;  // Default base font size is 16px

                            document.querySelectorAll('h3, h4, h5, h6').forEach((el) => {
                                el.style.fontSize = `${scaleFactor * 24}px`; // For larger headings
                            });

                            document.querySelectorAll('h2, li').forEach((el) => {
                                el.style.fontSize = `${scaleFactor * 16}px`; // For smaller text (paragraphs, list items, etc.)
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching text size:', error));
            }, 2000); // Check every 2 seconds
        </script>

    </head>
    <body>
        <div class="container-fluid vh-100 d-flex flex-column justify-content-between">

            <?php if ($modules['Scheduler']['active']) { include '../modules/schedule_module/schedule.view.php'; } ?>

            <!-- Top Section: DateTime, Weather, Stock Prices, and ToDo List -->
            <div class="row">
                <?php if ($modules['DateTime']['active']) { include '../modules/datetime_module/datetime.view.php'; } ?>
                <?php if ($modules['Stock Prices']['active']) { include '../modules/stock_module/stock.view.php'; } ?>
                <?php if ($modules['Todo Lists']['active']) { include '../modules/todo_module/todo.view.php'; } ?>
            </div>

            <div id="weather-container">
                <?php if ($modules['Weather']['active']) { include '../modules/weather_module/weather.view.php'; } ?>
            </div>
            <div id="music-container">
                <?php if ($modules['Music Player']['active']) { include '../modules/mplayer_module/mplayer.view.php'; } ?>
            </div>
            
            <!-- Background Logo -->
            <div id="logo-container" class="<?= $showBackground ? '' : 'hidden'; ?>">
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
        <?php if ($modules['Music Player']['active']) { ?>
            <script src="../modules/mplayer_module/mplayer.script.js"></script>
        <?php } ?>

        <script>
            // Fetch initial show/hide state and apply
            function toggleBackground(show) {
                const logoContainer = document.getElementById('logo-container');
                if (show) {
                    logoContainer.classList.remove('hidden');
                } else {
                    logoContainer.classList.add('hidden');
                }
            }

            // Poll for changes to show_background
            setInterval(() => {
                fetch('../core/check_show_background.php')
                    .then(response => response.json())
                    .then(data => toggleBackground(data.showBackground === 1))
                    .catch(error => console.error('Error fetching show background:', error));
            }, 2000);
        </script>
        
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
