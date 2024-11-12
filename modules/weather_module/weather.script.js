window.addEventListener('load', function() {
    getWeather(userPreferences.weather_location);
    setInterval(() => getWeather(userPreferences.weather_location), 600000);
});

function getWeather(location) { 
    const apiKey = "d2faaf298cd14b91a5073152242308";
    const currentWeatherURL = `https://api.weatherapi.com/v1/forecast.json?key=${apiKey}&q=${location}&days=3`;
    fetch(currentWeatherURL)
        .then(response => response.json())
        .then(data => {
            updateWeatherWidget(data);
        })
        .catch(error => console.log("Error fetching weather data:", error));
}

function updateWeatherWidget(data) {
    const tempC = data.current.temp_c;
    const locationInfo = `${data.location.name}, ${data.location.region}, ${data.location.country}`;
    const weatherIcon = data.current.condition.icon;
    const windDirection = data.current.wind_dir;
    const windSpeed = data.current.wind_kph;
    const humidity = data.current.humidity;
    const heatIndex = data.current.heatindex_c;

    document.getElementById("temperature").textContent = `${tempC}°C`;
    document.getElementById("location-info").textContent = locationInfo;
    document.getElementById("weather-icon").src = `http:${weatherIcon}`;
    document.getElementById("wind-icon").className = `wi wi-wind wi-towards-${windDirection.toLowerCase()} fs-1`;

    document.getElementById("wind-speed").textContent = `Wind Speed: ${windSpeed} km/h`;
    document.getElementById("humidity").textContent = `Humidity: ${humidity}%`;
    document.getElementById("heat-index").textContent = `Heat Index: ${heatIndex}°C`;

    // Update forecast data
    updateForecast(data.forecast.forecastday);
}

function updateForecast(forecastDays) {
    const forecastContainer = document.getElementById("forecast-container");
    forecastContainer.innerHTML = ''; // Clear previous forecast data

    forecastDays.forEach(day => {
        const date = new Date(day.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        const icon = day.day.condition.icon;
        const maxTemp = day.day.maxtemp_c;
        const minTemp = day.day.mintemp_c;

        // Create forecast element
        const forecastElement = `
            <div class="col">
                <p>${date}</p>
                <img src="http:${icon}" alt="Forecast Icon" style="width: 32px; margin-top: -18px;">
                <p>${maxTemp}°C / ${minTemp}°C</p>
            </div>
        `;

        // Append to the container
        forecastContainer.insertAdjacentHTML('beforeend', forecastElement);
    });
}
