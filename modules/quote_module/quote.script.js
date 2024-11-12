window.addEventListener('load', function() {
    getRandomQuote();
    setInterval(getRandomQuote, 86400000); // 86400000 ms = 24 hours
});


function getRandomQuote() {
    fetch('https://quotes-api-self.vercel.app/quote')
        .then(response => response.json())
        .then(data => {
            // Handle the retrieved quote
            updateRandomQuote(data);
        })
        .catch(error => {
            // Handle any errors
            console.error(error);
        });
}

function updateRandomQuote(data) {
    // Update the DOM elements with the quote and author from the fetched data
    document.getElementById("quote").textContent = `"${data.quote}"`;
    document.getElementById("cite").textContent = `-${data.author}`;
}

