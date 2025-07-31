<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>WeatherVoyager - Find Your Perfect Destination</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- CSS Fixes -->
    <link rel="stylesheet" href="css/style-fixes.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cloud-sun-rain me-2"></i>
                WeatherVoyager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php">Weather News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="py-5 text-center hero-section" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
        <div class="container">
            <h1 class="display-4" style="color: #14b8a6;">Find Your Perfect Weather Destination</h1>
            <p class="lead" style="color: #5eead4;">Tell us your ideal weather, and we'll find the best cities for your next trip.</p>
        </div>
    </div>


    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow" style="background: #1a1a1a; border: 1px solid #14b8a6;">
                    <div class="card-body" style="padding: 2rem;">
                        <h2 class="card-title text-center mb-4" style="color: #14b8a6;">What's your ideal weather?</h2>
                        <form id="weather-form" method="POST" action="api/recommendations" style="transition: none !important; transform: none !important;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="sunny" id="sunny" style="accent-color: #14b8a6;">
                                        <label class="custom-checkbox-label" for="sunny" style="color: white; transition: none !important; transform: none !important;">
                                            <i class="fas fa-sun text-warning me-2"></i> Sunny Weather
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="cool_breeze" id="cool_breeze" style="accent-color: #14b8a6;">
                                        <label class="custom-checkbox-label" for="cool_breeze" style="color: white; transition: none !important; transform: none !important;">
                                            <i class="fas fa-wind" style="color: #14b8a6;" class="me-2"></i> Cool Breeze
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="low_humidity" id="low_humidity" style="accent-color: #14b8a6;">
                                        <label class="custom-checkbox-label" for="low_humidity" style="color: white; transition: none !important; transform: none !important;">
                                            <i class="fas fa-water me-2" style="color: #14b8a6;"></i> Low Humidity
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="no_rain" id="no_rain" style="accent-color: #14b8a6;">
                                        <label class="custom-checkbox-label" for="no_rain" style="color: white; transition: none !important; transform: none !important;">
                                            <i class="fas fa-cloud-rain text-secondary me-2"></i> No Rain
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="moderate_temp" id="moderate_temp" style="accent-color: #14b8a6;">
                                        <label class="custom-checkbox-label" for="moderate_temp" style="color: white; transition: none !important; transform: none !important;">
                                            <i class="fas fa-temperature-low me-2" style="color: #22c55e;"></i> Moderate Temperature
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="clear_sky" id="clear_sky" style="accent-color: #14b8a6;">
                                        <label class="custom-checkbox-label" for="clear_sky" style="color: white; transition: none !important; transform: none !important;">
                                            <i class="fas fa-cloud-sun me-2" style="color: #14b8a6;"></i> Clear Sky
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5 style="color: #14b8a6;">Temperature Range</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="min_temp" class="form-label" style="color: white;">Minimum (°C)</label>
                                            <input type="number" class="form-control" id="min_temp" name="min_temp" value="15" style="background: #2d2d2d; border: 1px solid #14b8a6; color: white;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="max_temp" class="form-label" style="color: white;">Maximum (°C)</label>
                                            <input type="number" class="form-control" id="max_temp" name="max_temp" value="30" style="background: #2d2d2d; border: 1px solid #14b8a6; color: white;">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5 style="color: #14b8a6;">Select Continents</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="europe" id="europe" style="accent-color: #14b8a6;">
                                                <label class="custom-checkbox-label" for="europe" style="color: white; transition: none !important; transform: none !important;">
                                                    <i class="fas fa-landmark me-2" style="color: #14b8a6;"></i> Europe
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="north_america" id="north_america" style="accent-color: #14b8a6;">
                                                <label class="custom-checkbox-label" for="north_america" style="color: white; transition: none !important; transform: none !important;">
                                                    <i class="fas fa-mountain me-2" style="color: #14b8a6;"></i> North America
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="asia" id="asia" style="accent-color: #14b8a6;">
                                                <label class="custom-checkbox-label" for="asia" style="color: white; transition: none !important; transform: none !important;">
                                                    <i class="fas fa-torii-gate me-2" style="color: #14b8a6;"></i> Asia
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="australia_oceania" id="australia_oceania" style="accent-color: #14b8a6;">
                                                <label class="custom-checkbox-label" for="australia_oceania" style="color: white; transition: none !important; transform: none !important;">
                                                    <i class="fas fa-water me-2" style="color: #14b8a6;"></i> Australia & Oceania
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="africa" id="africa" style="accent-color: #14b8a6;">
                                                <label class="custom-checkbox-label" for="africa" style="color: white; transition: none !important; transform: none !important;">
                                                    <i class="fas fa-tree me-2" style="color: #22c55e;"></i> Africa
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-checkbox" style="transition: none !important; transform: none !important;">
                                                <input class="form-check-input" type="checkbox" name="continents[]" value="south_central_america" id="south_central_america" style="accent-color: #14b8a6;">
                                                <label class="custom-checkbox-label" for="south_central_america" style="color: white; transition: none !important; transform: none !important;">
                                                    <i class="fas fa-drum me-2" style="color: #14b8a6;"></i> South & Central America
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-lg px-5" style="background: linear-gradient(135deg, #14b8a6, #0d9488); border: none; color: white; border-radius: 25px; transition: all 0.3s ease;">
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
    <div class="container mb-5" id="results-container" style="display: none;">
        <h2 class="text-center mb-4">Your Top Destination Matches</h2>
        <div class="row mb-4">
            <div class="col-12 text-center">
                <div class="btn-group" role="group" aria-label="View toggle">
                    <button type="button" class="btn btn-primary active" id="card-view-btn">Card View</button>
                    <button type="button" class="btn btn-outline-primary" id="map-view-btn">Map View</button>
                </div>
            </div>
        </div>
        <div class="row" id="results-row">
        </div>
        <div id="map-container" style="display:none;">
            <div id="results-map" style="height: 500px; width: 100%;"></div>
        </div>
        <div class="mt-4" id="pagination-controls">
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Finding your perfect weather destinations...</p>
    </div>
    <footer class="py-4 mt-5" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cloud-sun-rain me-2" style="color: #14b8a6;"></i> WeatherVoyager</h5>
                    <p style="color: #5eead4;">Finding your perfect weather destination since 2025.</p>
                    <p style="color: #5eead4; font-size: 0.9em;">Created by <a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/index.php" target="_blank" style="color: #14b8a6; text-decoration: none;">Code Craft Website Solutions</a></p>
                </div>
                <div class="col-md-3">
                    <h5 style="color: #14b8a6;">Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" style="color: #5eead4; text-decoration: none;">Home</a></li>
                        <li><a href="news.php" style="color: #5eead4; text-decoration: none;">Weather News</a></li>
                        <li><a href="about.php" style="color: #5eead4; text-decoration: none;">About</a></li>
                        <li><a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php" style="color: #5eead4; text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 style="color: #14b8a6;">Data Sources</h5>
                    <ul class="list-unstyled">
                        <li style="color: #5eead4;">OpenWeather API</li>
                        <li style="color: #5eead4;">Open-Meteo API</li>
                        <li style="color: #5eead4;">Google News RSS</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="mb-0" style="color: #5eead4;">&copy; 2025 WeatherVoyager. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
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