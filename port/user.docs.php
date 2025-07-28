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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Documentation</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css_core/help.docs.css">
</head>
<body onclick="closeSidebar(event)">

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Back Button -->
            <a href="user.cm.php">
                <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" alt="Back" class="back-arrow">
            </a>

            <!-- Centered Header -->
            <h1 class="mx-auto text-center mb-0">Documentation</h1>

            <!-- Burger Menu Icon -->
            <img src="../assets/svg/light-burger-advanced-svgrepo-com.svg" class="burger-menu" alt="A Burger Menu" onclick="toggleSidebar(event)">
        <div style="width: 10px;"></div>
    </div>

    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <nav id="sidebar-menu" class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item my-2">
                    <a class="nav-link text-light" href="#getting-started">Getting Started</a>
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link text-light" href="#introduction">Introduction</a></li>
                        <li><a class="nav-link text-light" href="#requirements">Requirements</a></li>
                        <li><a class="nav-link text-light" href="#installation">Installation</a></li>
                    </ul>
                </li>
                <li class="nav-item my-2">
                    <a class="nav-link text-light" href="#modules">Modules</a>
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link text-light" href="#module-intro">Introduction</a></li>
                        <li><a class="nav-link text-light" href="#module-development">Module Development</a></li>
                    </ul>
                </li>
                <li class="nav-item my-2">
                    <a class="nav-link text-light" href="#commands">Voice Commands</a>
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link text-light" href="#commands-intro">Introduction</a></li>
                        <li><a class="nav-link text-light" href="#schedule">Set Schedule</a></li>
                        <li><a class="nav-link text-light" href="#add-todo">Add To Do List</a></li>
                        <li><a class="nav-link text-light" href="#remove-todo">Remove To Do List</a></li>
                        <li><a class="nav-link text-light" href="#weather">Change Weather Location</a></li>
                        <li><a class="nav-link text-light" href="#datetime">Current Time or Date</a></li>
                        <li><a class="nav-link text-light" href="#music">Music Player</a></li>
                    </ul>
                </li>
                <li class="nav-item my-2">
                    <a class="nav-link text-light" href="#about">About</a>
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link text-light" href="#developer">Developer</a></li>
                        <li><a class="nav-link text-light" href="#support">Support</a></li>
                        <li><a class="nav-link text-light" href="#whats-new">What's New!</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="container0">
            <!-- Getting Started Section -->
            <section id="getting-started">
                <h2>1. Getting Started</h2>
                <div class="section-content">
                    <h3 id="introduction">1.1 Introduction</h3>
                    <p>
                    The CaptioMirror is an IoT-based smart mirror platform designed to be used into every daily routine of users, transforming our household mirrors into interactive personal assistants. 
                    With CaptioMirror, users can access information, set reminders, manage tasks and connect with the digital world right from a bedroom or bathroom mirror, making technology more accessible and personalized within familiar spaces.
                    </p>

                    <h3 id="requirements">1.2 Requirements</h3>
                    <h4>1.2.1 Hardware</h4>
                    <ul>
                        <li>Raspberry Pi 4 Model B (4GB or much higher RAM)</li>
                        <li>Display Monitor (24" or 32" Dimension)</li>
                        <li>5V 3A Charger Adapter and USB Type-C Cable</li>
                        <li>USB Omnidirectional Microphone</li>
                        <li>MicroSD Card (32GB or much higher storage capacity)</li>
                        <li>Micro-HDMI cable (HDMI Type-D to Type-A connector)</li>
                    </ul>

                    <h4>1.2.2 Operating System</h4>
                    <ul>
                        <li>Recommended: Raspbian OS (Latest Version)</li>
                    </ul>

                    <h3 id="installation">1.3 Installation (Raspbian OS)</h3>
                    <ul>
                    <p>1. Download and Install <strong>Apache2</strong> and <strong>MariaDB</strong>:</p>
                    <pre><code>sudo apt install apache2 mariadb-server</code></pre>

                    <p>2. Check if <strong>Git</strong> is installed by executing <code>git --version</code>. If it’s not installed, install it with:</p>
                    <pre><code>sudo apt install git</code></pre>

                    <p>3. Clone the CaptioMirror repository from GitHub:</p>
                    <pre><code>git clone https://github.com/yenzukai/CaptioMirror</code></pre>

                    <p>4. Copy the CaptioMirror repository to the Apache2 directory:</p>
                    <pre><code>sudo cp -r CaptioMirror /var/www/html/</code></pre>

                    <p>5. Make sure the Apache2 service is running. Start it if it's not:</p>
                    <pre><code>sudo systemctl start apache2</code></pre>

                    <p>6. Configure MariaDB with the following credentials:</p>
                    <ul>
                        <li>Username: <code>root</code></li>
                        <li>Password: <code>captiosus2024</code></li>
                    </ul>
                    <p>To start MariaDB if it's not running:</p>
                    <pre><code>sudo systemctl start mariadb</code></pre>

                    <p>7. Log into the MariaDB console:</p>
                    <pre><code>sudo mysql -u root -p</code></pre>

                    <p>8. Create a database named <code>CaptioMirror</code>:</p>
                    <pre><code>CREATE DATABASE CaptioMirror;</code></pre>

                    <p>9. Import the <code>captiomirror.sql</code> file into MariaDB:</p>
                    <pre><code>mysql -u root -p CaptioMirror < /path/to/CaptioMirror/sql/captiomirror.sql</code></pre>
                    <p>(Replace <code>/path/to/</code> with the actual path to the <code>captiomirror.sql</code> file in the cloned repository.)</p>

                    <p>10. Download and install <strong>ngrok</strong> from <a href="https://ngrok.com/download">ngrok.com/download</a>. Follow the instructions on the website for your OS.</p>

                    <p>11. Start ngrok to expose the Apache server to the internet:</p>
                    <pre><code>ngrok http 80</code></pre>
                    </ul>
                </div>
            </section>

            <!-- Modules Section -->
            <section id="modules">
                <h2>2. Modules</h2>
                <div class="section-content">
                    <h3 id="module-intro">2.1 Introduction</h3>
                    <h4>The following modules are installed by default.</h4>
                    <ul>
                        <li>Assistant</li>
                        <li>DateTime</li>
                        <li>Quote of the Day</li>
                        <li>Scheduler</li>
                        <li>Stock Prices</li>
                        <li>Todo Lists</li>
                        <li>Weather</li>
                        <li>Music Player</li>
                    </ul>

                    <h3 id="module-development">2.2 Module Development</h3>

                    <p>CaptioMirror provides opportunities for students like us to contribute to the project by integrating their own third-party modules.</p>

                    <p>To develop a module for CaptioMirror, follow these steps:</p>

                    <ul>
                    <p>1. In the <code>CaptioMirror/modules</code> directory, create a new folder for your module. Name it to reflect the module's purpose.</p>

                    <p>2. Inside your module’s folder, create a JavaScript (.js), CSS (.css), and PHP (.php) file. Each file should have the same name as the folder. For example, if the folder is named <code>datetime_module</code>, name the files <code>datetime.script.js</code>, <code>datetime.style.css</code>, and <code>datetime.view.php</code>.</p>

                    <p>3. Add a record in the <code>modules</code> table of the database. Set the following columns:
                    - <code>id</code> – Unique identifier.
                    - <code>name</code> – The name of the module.
                    - <code>active</code> – Set to <code>1</code> to activate the module.</p>

                    <p>4. Program the JavaScript, CSS, and PHP files according to your module’s purpose, following the provided templates below.</p>
                    </ul>
                    
                    <h4>4.1. Dashboard Configuration (index.php)</h4>
                    <p>To integrate your module with the dashboard, add the following code in <code>index.php</code>:</p>

                    <p>Inside the <code>index.php</code> body, enclosed in <code>&lt;?php ?&gt;</code> tags:</p>
                    <pre><code>&lt;?php if ($modules['DateTime']['active']) { include '../modules/datetime_module/datetime.view.php'; } ?&gt;</code></pre>

                    <p>Under the “Module Scripts” comment in <code>index.php</code>:</p>
                    <pre><code>&lt;?php if ($modules['DateTime']['active']) { ?&gt;
                    &lt;script src="../modules/datetime_module/datetime.script.js"&gt;&lt;/script&gt;
                    &lt;?php } ?&gt;</code></pre>

                    <h4>4.2. PHP Configuration</h4>
                    <p>Use this PHP template to define JavaScript configurations for your module:</p>
                    <pre><code>&lt;?php if ($modules['DateTime']['active']) { ?&gt;
                        &lt;link rel="stylesheet" href="../modules/datetime_module/datetime.style.css"&gt;
                        &lt;div class="row"&gt;
                            &lt;div class="text-center p-4"&gt;
                                &lt;p id="datetime"&gt;Your date and time here&lt;/p&gt;
                            &lt;/div&gt;
                        &lt;/div&gt;      
                    &lt;?php } ?&gt;
                    </code></pre>

                    <h4>4.3. JavaScript Configuration</h4>
                    <p>Define JavaScript functionality with this template:</p>
                    <pre><code>window.addEventListener('load', function() {
                        updateDateTime();
                        setInterval(updateDateTime, 1000); // Update every second
                    });</code></pre>

                    <h4>4.4. CSS Configuration</h4>
                    <p>Style your module with the following CSS template:</p>
                    <pre><code>/* Date and Time Module */
                    #datetime {
                        display: block;
                        font-weight: bold;
                        font-size: 1.5rem;
                        text-align: center;
                    }</code></pre>
                </div>
            </section>

            <!-- Voice Commands -->
            <section id="commands">
                <h2>3. Voice Commands</h2>
                <div class="section-content">
                    <h3 id="commands-intro">3.1 Introduction</h3>
                    <p>These are the following default voice commands:</p>
                    <ul>
                        <li>set schedule</li>
                        <li>add to do list</li>
                        <li>remove to do list or delete to do list</li>
                        <li>change weather location</li>
                        <li>current time or current date</li>
                        <li>music player</li>
                    </ul>

                    <h3 id="schedule">3.2 Set Schedule</h3>
                    <p>This voice command allows you to set a new schedule on the Smart Mirror System.</p>
                    <p><strong>Usage:</strong> Say "<i>Set schedule</i>" to start the scheduling process. The assistant will guide you through each detail required for the schedule:</p>
                    <ul>
                        <li><strong>Event Name:</strong> Specify the name of the event.</li>
                        <li><strong>Start Date and Time:</strong> Provide the year, month, day, and time for the event start.</li>
                        <li><strong>End Date and Time:</strong> Specify the year, month, day, and time for the event end.</li>
                        <li><strong>Confirmation:</strong> The assistant will repeat the event details for confirmation. Respond with "Yes" to save or "No" to cancel.</li>
                    </ul>

                    <h3 id="add-todo">3.3 Add to do list</h3>
                    <p>This command allows you to add a task to your to-do list.</p>
                    <p><strong>Usage:</strong> Say "<i>Add to do list</i>". The assistant will ask for the task name. After providing the name, the assistant will confirm your addition with "<i>Should I proceed?</i>". You can respond with "Yes" to save the task or "No" to cancel.</p>

                    <h3 id="remove-todo">3.4 Remove to do list or delete to do list</h3>
                    <p>This command lets you remove a task from your to-do list.</p>
                    <p><strong>Usage:</strong> Say "<i>Remove to do list</i>" or "<i>Delete to do list</i>". The assistant will ask which task to remove. Provide the task name, and the assistant will confirm the removal. You can respond with "Yes" to delete or "No" to keep the task.</p>

                    <h3 id="weather">3.5 Change weather location</h3>
                    <p>This command changes the weather location displayed on the Smart Mirror.</p>
                    <p><strong>Usage:</strong> Say "<i>Change weather location</i>". The assistant will ask for the new location. Provide the name of the city or region. The assistant will confirm the change, and you can respond with "Yes" to update or "No" to cancel.</p>

                    <h3 id="datetime">3.6 Current time or current date</h3>
                    <p>These commands allow you to request the current time or date displayed on the Smart Mirror.</p>
                    <p><strong>Usage:</strong> Say "<i>Current time</i>" to hear the current time, or "<i>Current date</i>" to hear today's date. The assistant will respond accordingly.</p>

                    <h3 id="music">3.6 Music Player</h3>
                    <p>The following commands allow you to control the music player:</p>
                    <ul>
                        <li><i>play song</i> - Starts playing the currently queued music from the local song folder.</li>
                        <li><i>pause song</i> - Pauses the music, retaining its current playback position.</li>
                        <li><i>stop song</i> - Stops the music and resets the playback to the beginning.</li>
                        <li><i>next song</i> - Skips to the next song in the playlist and plays it automatically.</li>
                        <li><i>previous song</i> - Rewinds to the previous song in the playlist and plays it.</li>
                    </ul>
                </div>
            </section>

            <!-- About Section -->
            <section id="about">
                <h2>4. About</h2>
                <div class="section-content">
                    <h3 id="developer">4.1 Developer</h3>
                    <p>CaptioMirror is developed by a group of Partido State University students, who are very passionate about IoT-based projects and solutions.</p>
                    <ul>
                        <li>Martin James P. Rojas - Capstone Leader</li>
                        <li>Rogel T. Navarro - Programmer</li>
                        <li>Ma. Dolores L. Gabay - Capstone Writer</li>
                    </ul>

                    <p>Note: We would like to remind our users that this capstone project "CaptioMirror" was created solely for educational purposes.</p>

                    <h3 id="support">4.2 Support</h3>
                    <p>If you have any inquiries about our project, please contact us in this email address: <a href="mailto:mjrojasforex@gmail.com">mjrojasforex@gmail.com</a></p>

                    <h3 id="whats-new">4.3 What's New!</h3>
                    <p>Stay updated with the latest features and fixes in CaptioMirror's regular updates.</p>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById('sidebar-menu');
            const toggleButton = document.querySelector('.burger-menu');

            // Function to toggle the sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
            }

            // Close sidebar if clicked outside of it
            document.addEventListener('click', function(event) {
                if (sidebar.classList.contains('active') &&
                    !sidebar.contains(event.target) &&
                    !toggleButton.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            });

            // Event listener for the burger menu button
            toggleButton.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>
