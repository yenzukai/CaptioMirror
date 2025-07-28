<?php if ($modules['Todo Lists']['active']) { ?>
    <link rel="stylesheet" href="../modules/todo_module/todo.style.css">
    <!-- Todo Lists Section -->
    <div id="todo-list" class="mt-4 text-white">
        <h1>To-Do List:</h1> 
        <ul id="list-container" style="list-style-type: none; padding-left: 0;">
            <?php foreach ($tasks as $task) { ?>
                <li class="todo-item">
                    <input type="checkbox" <?= $task['checked'] ? "checked" : "" ?> disabled>
                    <span class="<?= $task['checked'] ? "completed-task" : "" ?>">
                        <?= htmlspecialchars($task['task']) ?>
                    </span>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
