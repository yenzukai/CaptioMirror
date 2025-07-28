<?php if ($modules['Weather']['active']) { ?>
    <link rel="stylesheet" href="../modules/weather_module/weather.style.css">
    <!-- Weather Section -->
    <div class="col-md-6 d-flex flex-column align-items-end justify-content-start p-4 text-white">
        <div class="d-flex align-items-center justify-content-center mb-4">
            <i id="wind-icon" class="wi wi-wind fs-1"></i>
            <img id="weather-icon" alt="Weather Icon" class="ms-2" style="width: 64px;">
        </div>
        <h1 id="temperature" class="display-1">--°C</h1>
        <li id="location-info" class="text-uppercase mb-0">--</li>
        <div id="additional-info" class="mt-4">
            <li id="wind-speed" class="mb-1">Wind Speed: -- km/h</li>
            <li id="humidity" class="mb-1">Humidity: --%</li>
            <li id="heat-index" class="mb-1">Heat Index: --°C</li>
        </div>
        <!-- Forecast Section -->
        <div id="forecast-container" class="mt-4">
            <h3>3-Day Forecast</h3>
            <div id="forecast" class="d-flex justify-content-between">
                <!-- Forecast data will be dynamically inserted here -->
            </div>
        </div>
    </div>
<?php } ?>