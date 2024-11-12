window.addEventListener('load', function() {
    updateDateTime(userPreferences.date_time_format);
    setInterval(() => updateDateTime(userPreferences.date_time_format), 1000);
});

function updateDateTime(format) {
    const now = new Date();

    // Options for formatting the date and time
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: format === 'en-US' };

    // Format the date and time
    const formattedDate = now.toLocaleDateString(format, dateOptions);
    const formattedTime = now.toLocaleTimeString(format, timeOptions);

    // Update the DOM elements with the formatted date and time
    document.getElementById("date").textContent = formattedDate;
    document.getElementById("time").textContent = formattedTime;
}