<?php
// Load environment variables if needed
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About WeatherVoyager - Your Weather Destination Guide</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/style-fixes.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }
    </style>
</head>

<body>
    <!-- Header/Navigation -->
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php">Weather News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="py-5 text-center" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
        <div class="container">
            <h1 class="display-4" style="color: #14b8a6;">About WeatherVoyager</h1>
            <p class="lead" style="color: #5eead4;">Learn more about our mission to help you find your perfect weather destination</p>
        </div>
    </div>

    <!-- About Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg" style="background: #1a1a1a; border: 1px solid #14b8a6;">
                    <div class="card-body" style="padding: 2.5rem;">
                        <h2 class="mb-4" style="color: #14b8a6;">Our Story</h2>
                        <p style="color: white; line-height: 1.6;">WeatherVoyager was founded with a simple mission: to help travelers find destinations with their ideal weather conditions. We understand that weather plays a crucial role in trip planning and can make or break your vacation experience.</p>

                        <p style="color: white; line-height: 1.6;">Our team of meteorology enthusiasts and travel experts have combined their knowledge to create a platform that matches your weather preferences with global destinations, helping you make informed travel decisions.</p>

                        <h3 class="mt-5 mb-3" style="color: #14b8a6;">How It Works</h3>
                        <p style="color: white; line-height: 1.6;">Our sophisticated algorithm analyzes weather data from reliable sources around the world. When you tell us your preferred weather conditions, we cross-reference these preferences with current and historical weather patterns to suggest destinations that match your ideal conditions.</p>

                        <div class="row mt-5">
                            <div class="col-md-4 text-center mb-4">
                                <div class="p-4 rounded" style="background: linear-gradient(135deg, #2d2d2d, #1a1a1a); border: 1px solid #14b8a6;">
                                    <i class="fas fa-sliders-h fa-3x mb-3" style="color: #14b8a6;"></i>
                                    <h5 style="color: #14b8a6;">Select Preferences</h5>
                                    <p class="small" style="color: #5eead4;">Tell us your ideal temperature, humidity, and other weather factors</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-4">
                                <div class="p-4 rounded" style="background: linear-gradient(135deg, #2d2d2d, #1a1a1a); border: 1px solid #14b8a6;">
                                    <i class="fas fa-search-location fa-3x mb-3" style="color: #14b8a6;"></i>
                                    <h5 style="color: #14b8a6;">Analyze Data</h5>
                                    <p class="small" style="color: #5eead4;">Our system searches global weather patterns and forecasts</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-4">
                                <div class="p-4 rounded" style="background: linear-gradient(135deg, #2d2d2d, #1a1a1a); border: 1px solid #14b8a6;">
                                    <i class="fas fa-map-marked-alt fa-3x mb-3" style="color: #14b8a6;"></i>
                                    <h5 style="color: #14b8a6;">Get Recommendations</h5>
                                    <p class="small" style="color: #5eead4;">Receive personalized destination suggestions with detailed weather info</p>
                                </div>
                            </div>
                        </div>

                        <h3 class="mt-5 mb-3" style="color: #14b8a6;">Our Data Sources</h3>
                        <p style="color: white; line-height: 1.6;">WeatherVoyager uses data from multiple reliable meteorological sources including OpenWeather API and Open-Meteo API to ensure accuracy. We also provide the latest weather news through Google News RSS feeds to keep you informed about global weather patterns.</p>

                        <h3 class="mt-5 mb-3" style="color: #14b8a6;">Features</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul style="color: #5eead4;">
                                    <li>🌡️ Temperature range preferences</li>
                                    <li>💨 Wind and breeze conditions</li>
                                    <li>💧 Humidity level selection</li>
                                    <li>☀️ Sky condition preferences</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul style="color: #5eead4;">
                                    <li>🗺️ Continental filtering</li>
                                    <li>📍 Interactive map view</li>
                                    <li>📰 Latest weather news</li>
                                    <li>📊 Detailed weather forecasts</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-5 p-4 rounded" style="background: linear-gradient(135deg, #2d2d2d, #1a1a1a); border-left: 4px solid #14b8a6;">
                            <h4 style="color: #14b8a6; margin-bottom: 1rem;">🚀 Created by Code Craft Website Solutions</h4>
                            <p style="color: #5eead4; margin-bottom: 1rem;">This innovative weather destination platform was professionally designed and developed by Code Craft Website Solutions, specialists in creating cutting-edge web applications.</p>
                            <p style="color: white; margin-bottom: 1rem;">At Code Craft, we transform ideas into powerful digital solutions. From custom web applications to comprehensive business platforms, we craft websites that deliver exceptional user experiences.</p>
                            <div class="text-center">
                                <a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/index.php" target="_blank" class="btn btn-lg px-4 py-2" style="background: linear-gradient(135deg, #14b8a6, #0d9488); color: white; text-decoration: none; border: none; border-radius: 25px;">
                                    <i class="fas fa-external-link-alt me-2"></i>Visit Code Craft Website Solutions
                                </a>
                            </div>
                        </div>

                        <h3 class="mt-5 mb-3" style="color: #14b8a6;">Contact & Support</h3>
                        <p style="color: white; line-height: 1.6;">Have questions or suggestions? We'd love to hear from you! Our team is dedicated to continuously improving WeatherVoyager to serve your travel planning needs better.</p>
                        <div class="text-center mt-4">
                            <a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php" class="btn btn-outline-light btn-lg px-4" style="border-color: #14b8a6; color: #14b8a6;">
                                <i class="fas fa-envelope me-2"></i>Get in Touch
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-5" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cloud-sun-rain me-2" style="color: #14b8a6;"></i> WeatherVoyager</h5>
                    <p style="color: #5eead4;">Your perfect weather destination finder</p>
                    <p style="color: #5eead4; font-size: 0.9em;">Created by <a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/index.php" target="_blank" style="color: #14b8a6; text-decoration: none;">Code Craft Website Solutions</a></p>
                </div>
                <div class="col-md-3">
                    <h5 style="color: #14b8a6;">Navigation</h5>
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
            <div class="text-center mt-4 pt-3" style="border-top: 1px solid #14b8a6;">
                <p class="mb-0" style="color: #5eead4;">&copy; <?php echo date('Y'); ?> WeatherVoyager. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>