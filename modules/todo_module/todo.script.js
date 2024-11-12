document.addEventListener("DOMContentLoaded", () => {
    const listContainer = document.getElementById("list-container");

    // Fetch tasks from the server
    function fetchTasks() {
        fetch('../port/func_core/save_todo.php', { method: 'GET' })
            .then(response => response.json())
            .then(tasks => {
                listContainer.innerHTML = '';
                tasks.forEach(task => {
                    const li = document.createElement('li');
                    li.textContent = task.task;
                    listContainer.appendChild(li);
                });
            })
            .catch(error => console.error('Error fetching tasks:', error));
    }

    fetchTasks();
});
