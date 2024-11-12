window.addEventListener('load', function() {
    getStockPrices(userPreferences.stock_symbols);
    setInterval(() => getStockPrices(userPreferences.stock_symbols), 60000);
});

function getStockPrices(symbols) {
    const apiKey = 'tI06EqQDRpCRjhQi6EzfPYzOBDA5rUNV';

    const fetchStockData = async (symbol) => {
        const url = `https://api.polygon.io/v1/open-close/${symbol}/2023-01-09?adjusted=true&apiKey=${apiKey}`;
        const response = await fetch(url);
        const data = await response.json();
        return data;
    };

    const stockDataPromises = symbols.filter(Boolean).map(fetchStockData); // Remove empty symbols
    Promise.all(stockDataPromises)
        .then(stockData => {
            updateStockPrices(stockData);
        })
        .catch(error => console.log("Error fetching stock data:", error));
}

function updateStockPrices(stockData) {
    const stockContainer = document.getElementById("stock-prices");
    stockContainer.innerHTML = ''; // Clear previous stock data

    stockData.forEach(data => {
        const stockElement = `
            <div>
                <h4>${data.symbol}</h4>
                <p>Price: ${data.close}</p>
                <p>High: ${data.high}</p>
                <p>Low: ${data.low}</p>
            </div>
        `;

        stockContainer.insertAdjacentHTML('beforeend', stockElement);
    });
}
