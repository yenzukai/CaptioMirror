<?php if ($modules['Todo Lists']['active']) { ?>
    <link rel="stylesheet" href="../modules/todo_module/todo.style.css">
    <!-- Todo Lists Section -->
    <div id="todo-list" class="mt-4 text-white">
        <h3>To-Do List</h3> 
        <ul id="list-container" style="list-style-type: none; padding-left: 0;">
            <?php foreach ($tasks as $task) { ?>
                <!-- Add your tasks data here -->
                <li><?= htmlspecialchars($task['task']) ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>