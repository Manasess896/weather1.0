<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"="width=device-width, initial-scale=1.0">
    <title>WeatherVoyager - Find Your Perfect Destination</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header/Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cloud-sun-rain me-2"></i>
                WeatherVoyager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">                    <li class="nav-item">
                        <a class="nav-link active" href="home">Home</a>
                    </li>                    <li class="nav-item">
                        <a class="nav-link" href="about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="py-5 text-center hero-section">
        <div class="container">
            <h1 class="display-4">Find Your Perfect Weather Destination</h1>
            <p class="lead">Tell us your ideal weather, and we'll find the best cities for your next trip.</p>
        </div>
    </div>

    <!-- Weather Preference Selection Form -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">What's your ideal weather?</h2>
                        <form id="weather-form" method="POST" action="api/recommendations">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="sunny" id="sunny">
                                        <label class="form-check-label" for="sunny">
                                            <i class="fas fa-sun text-warning me-2"></i> Sunny Weather
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="cool_breeze" id="cool_breeze">
                                        <label class="form-check-label" for="cool_breeze">
                                            <i class="fas fa-wind text-info me-2"></i> Cool Breeze
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="low_humidity" id="low_humidity">
                                        <label class="form-check-label" for="low_humidity">
                                            <i class="fas fa-water text-primary me-2"></i> Low Humidity
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="no_rain" id="no_rain">
                                        <label class="form-check-label" for="no_rain">
                                            <i class="fas fa-cloud-rain text-secondary me-2"></i> No Rain
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="moderate_temp" id="moderate_temp">
                                        <label class="form-check-label" for="moderate_temp">
                                            <i class="fas fa-temperature-low text-success me-2"></i> Moderate Temperature
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="clear_sky" id="clear_sky">
                                        <label class="form-check-label" for="clear_sky">
                                            <i class="fas fa-cloud-sun text-info me-2"></i> Clear Sky
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5>Temperature Range</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="min_temp" class="form-label">Minimum (°C)</label>
                                            <input type="number" class="form-control" id="min_temp" name="min_temp" value="15">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="max_temp" class="form-label">Maximum (°C)</label>
                                            <input type="number" class="form-control" id="max_temp" name="max_temp" value="30">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5>Select Continents</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="europe" id="europe" >
                                                <label class="form-check-label" for="europe">
                                                    <i class="fas fa-landmark me-2"></i> Europe
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="north_america" id="north_america" >
                                                <label class="form-check-label" for="north_america">
                                                    <i class="fas fa-mountain me-2"></i> North America
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="asia" id="asia" >
                                                <label class="form-check-label" for="asia">
                                                    <i class="fas fa-torii-gate me-2"></i> Asia
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="australia_oceania" id="australia_oceania" >
                                                <label class="form-check-label" for="australia_oceania">
                                                    <i class="fas fa-water me-2"></i> Australia & Oceania
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="africa" id="africa">
                                                <label class="form-check-label" for="africa">
                                                    <i class="fas fa-tree me-2"></i> Africa
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="south_central_america" id="south_central_america" >
                                                <label class="form-check-label" for="south_central_america">
                                                    <i class="fas fa-drum me-2"></i> South & Central America
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-search me-2"></i> Find My Destinations
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section (initially hidden) -->
    <div class="container mb-5" id="results-container" style="display: none;">
        <h2 class="text-center mb-4">Your Top Destination Matches</h2>
        
        <!-- View toggle buttons -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <div class="btn-group" role="group" aria-label="View toggle">
                    <button type="button" class="btn btn-primary active" id="card-view-btn">Card View</button>
                    <button type="button" class="btn btn-outline-primary" id="map-view-btn">Map View</button>
                </div>
            </div>
        </div>
        
        <!-- Card view (default) -->
        <div class="row" id="results-row">
            <!-- Results will be inserted here by JavaScript -->
        </div>
        
        <!-- Map view (initially hidden) -->
        <div id="map-container" style="display:none;">
            <div id="results-map" style="height: 500px; width: 100%;"></div>
        </div>
        
        <!-- Pagination Controls -->
        <div class="mt-4" id="pagination-controls">
            <!-- Pagination will be inserted here by JavaScript -->
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Finding your perfect weather destinations...</p>
    </div>

   

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cloud-sun-rain me-2"></i> WeatherVoyager</h5>
                    <p>Finding your perfect weather destination since 2025.</p>
                </div>
                <div class="col-md-3">
                    <h5>Links</h5>                    <ul class="list-unstyled">
                        <li><a href="home" class="text-white">Home</a></li>
                        <li><a href="pages\about.php" class="text-white">About</a></li>
                        <li><a href="pages\contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Data Sources</h5>
                    <ul class="list-unstyled">
                        <li>OpenWeather API</li>
                        <li>Open-Meteo API</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="mb-0">&copy; 2025 WeatherVoyager. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>