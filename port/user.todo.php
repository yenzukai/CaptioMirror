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
    <title>Manage Task</title>
    <link rel="icon" href="../assets/images/cm2_logo2.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="user.cm.css">
    <script>
        let lastMessage = '';

        // Function to fetch and display tasks
        function loadTasks() {
            fetch('func_core/save_todo.php', { method: 'GET' })
                .then(response => response.json())
                .then(tasks => {
                    const taskList = document.getElementById('task-list');
                    taskList.innerHTML = ''; // Clear the list

                    tasks.forEach(task => {
                        const listItem = document.createElement('li');
                        listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
                        
                        listItem.style.backgroundColor = '#151515';
                        listItem.style.color = '#ffffff';
                        listItem.style.border = '2px solid';
                        listItem.style.borderRadius = '0px';

                        listItem.innerHTML = `
                            <span>
                                <input type="checkbox" onchange="markTaskDone(${task.id})" class="form-check-input me-2"> 
                                ${task.task}
                            </span>
                        `;
                        taskList.appendChild(listItem);
                    });
                });
        }

        // Function to mark a task as done
        function markTaskDone(taskId) {
            fetch('func_core/save_todo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `task_done_id=${taskId}`
            })
            .then(response => response.json())
            .then(data => {
                lastMessage = data.success;
                loadTasks();
                showModal(lastMessage); // Show message after updating tasks
            });
        }

        // Function to add a new task
        function addTask(event) {
            event.preventDefault();
            const taskInput = document.getElementById('task');
            const task = taskInput.value;

            fetch('func_core/save_todo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `task=${encodeURIComponent(task)}`
            })
            .then(response => response.json())
            .then(data => {
                lastMessage = data.success;
                loadTasks();
                taskInput.value = '';
                showModal(lastMessage); // Show message after adding task
            });
        }

        // Load tasks when the page is loaded
        document.addEventListener('DOMContentLoaded', loadTasks);

        // Function to show the modal with a custom message
        function showModal(message) {
            const modalMessage = document.getElementById('modal-message');
            modalMessage.textContent = message;
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        }
    </script>
</head>

<body class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="user.cm.php">
            <img src="../assets/svg/light-left-arrow-svgrepo-com.svg" class="back-arrow" alt="A Back Arrow">
        </a>
        <h1 class="mx-auto text-center mb-0">Manage Tasks</h1>
        <div style="width: 40px;"></div>
    </div>
    
    <form onsubmit="addTask(event)" class="mb-4">
        <div class="input-group">
            <input type="text" id="task" name="task" class="form-control" placeholder="Enter a new task" required>
            <button type="submit" class="btn btn-primary">Add Task</button>
        </div>
    </form>
    <h4 class="mb-4">List of Current Tasks: </h4>
    <ul id="task-list" class="list-group"></ul>

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
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
