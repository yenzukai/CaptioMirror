document.addEventListener("DOMContentLoaded", () => {
    const listContainer = document.getElementById("list-container");

    // Fetch tasks from the server
    function fetchTasks() {
        fetch('../port/func_core/save_todo.php', { method: 'GET' })
            .then(response => response.json())
            .then(tasks => {
                listContainer.innerHTML = ''; // Clear previous tasks

                tasks.forEach(task => {
                    if (task.removed) return; // Skip removed tasks

                    const li = document.createElement('li');
                    li.classList.add('todo-item');
                    li.innerHTML = `
                        <input type="checkbox" ${task.checked ? "checked" : ""} disabled>
                        <span class="${task.checked ? "completed-task" : ""}">
                            ${task.task}
                        </span>
                    `;
                    listContainer.appendChild(li);
                });
            })
            .catch(error => console.error('Error fetching tasks:', error));
    }

    fetchTasks(); // Initial fetch
});
