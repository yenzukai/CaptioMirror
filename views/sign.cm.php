<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CaptioMirror</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="sign.cm.css">
    <script type="text/javascript" src="../assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="../assets/js/qrcode.js"></script>
</head>

<body>
    <div class="d-flex flex-column justify-content-center align-items-center vh-100 text-center">
        <div id="cm-logo">
            <img src="../assets/images/cm2_logo2.png" class="img-fluid" alt="The logo of the CaptioMirror">
        </div>
        <div id="sign-in-container" class="mt-4">
            <a class="btn btn-primary mx-2" onclick="createSessionToken()">SIGN IN</a>
        </div>
        <div id="qrcode-container" class="mt-4" style="display: none;">
            <div id="qrcode" style="width: 279px; height: 279px; background-color: #ffffff; border: 15px solid white;"></div>
        </div>
        <div id="scan-message" class="mt-2" style="display: none; font-size: 1.70rem; font-weight: 650; letter-spacing: 2.5px; color: white;">SCAN ME NOW</div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script type="text/javascript">
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width: 250,
            height: 250
        });

        function createSessionToken() {
        const array = new Uint8Array(16);
        window.crypto.getRandomValues(array);
        const token = Array.from(array, byte => ('0' + byte.toString(16)).slice(-2)).join('');

            // Store the session token in the database
            $.ajax({
                type: "POST",
                url: "../core/store_session.php",
                data: { session_token: token },
                success: function(response) {
                    console.log('Store token response:', response);
                    // Check if response is in JSON format
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Failed to parse response:', e);
                        return;
                    }

                    if (response.success) {
                        // Use makeCode to generate the QR code from the token
                        qrcode.makeCode(token);

                        // Hide the sign-in button
                        document.getElementById("sign-in-container").style.display = "none";

                        // Show the QR code container
                        document.getElementById("qrcode-container").style.display = "block";
                        document.getElementById("qrcode").style.margin = "0 auto";

                        // Show the "SCAN ME NOW" message
                        document.getElementById("scan-message").style.display = "block";
                        pollForLogin(token);
                    }
                    },
                    error: function(error) {
                        console.error("Error storing session token:", error);
                    }
                });
            }

            function pollForLogin(token) {
                setInterval(function() {
                $.ajax({
                    type: "POST",
                    url: "../core/check_login.php",
                    data: { session_token: token },
                    dataType: "json", // Expect JSON response
                    success: function(response) {
                        // No need to parse JSON here
                        console.log('Login status response:', response);

                        if (response.logged_in) {
                            window.location.href = "index.php"; // Redirect to a logged-in dashboard
                        }
                    },
                    error: function(error) {
                        console.error("Error checking login status:", error);
                    }
                });
                }, 3000); // Poll every 3 seconds
            }
    </script>

    <script>
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();

        recognition.onstart = function () {
            console.log('Speech recognition started. Say "Sign-in" to trigger the sign-in function.');
        };

        recognition.onresult = function (event) {
            const recognizedText = event.results[0][0].transcript.toLowerCase();
            console.log('Recognized Text:', recognizedText);

            if (recognizedText.includes("sign in") || recognizedText.includes("sign-in")) {
                createSessionToken(); // Trigger sign-in function
            }
        };

        recognition.onerror = function (event) {
            console.error('Speech recognition error:', event.error);
        };

        recognition.onend = function () {
            recognition.start();
        };

        window.addEventListener('load', function () {
            recognition.start();
        });
    </script>

</body>
</html>
