<?php
// Load environment variables
require_once __DIR__ . '/../config/env_loader.php';
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
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <!-- Header/Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">            <a class="navbar-brand" href="../home">
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
                        <a class="nav-link active" href="../about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="py-5 text-center hero-section">
        <div class="container">
            <h1 class="display-4">About WeatherVoyager</h1>
            <p class="lead">Learn more about our mission to help you find your perfect weather destination</p>
        </div>
    </div>

    <!-- About Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-4">Our Story</h2>
                        <p>WeatherVoyager was founded with a simple mission: to help travelers find destinations with their ideal weather conditions. We understand that weather plays a crucial role in trip planning and can make or break your vacation experience.</p>
                        
                        <p>Our team of meteorology enthusiasts and travel experts have combined their knowledge to create a platform that matches your weather preferences with global destinations, helping you make informed travel decisions.</p>
                        
                        <h3 class="mt-4">How It Works</h3>
                        <p>Our sophisticated algorithm analyzes weather data from reliable sources around the world. When you tell us your preferred weather conditions, we cross-reference these preferences with current and historical weather patterns to suggest destinations that match your ideal conditions.</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-sliders-h fa-2x text-primary mb-2"></i>
                                    <h5>Select Preferences</h5>
                                    <p class="small">Tell us your ideal temperature, humidity, and other weather factors</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-search-location fa-2x text-primary mb-2"></i>
                                    <h5>Analyze Data</h5>
                                    <p class="small">Our system searches global weather patterns</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-map-marked-alt fa-2x text-primary mb-2"></i>
                                    <h5>Get Recommendations</h5>
                                    <p class="small">Receive personalized destination suggestions</p>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="mt-4">Our Data</h3>
                        <p>WeatherVoyager uses data from multiple reliable meteorological sources to ensure accuracy. We analyze both current conditions and historical trends to provide you with the most reliable recommendations possible.</p>
                        
                        <h3 class="mt-4">Contact Us</h3>
                        <p>Have questions or suggestions? We'd love to hear from you! Visit our <a href="contact.php">Contact page</a> to get in touch with our team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cloud-sun-rain me-2"></i> WeatherVoyager</h5>
                    <p class="small">Your perfect weather destination finder</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="small">&copy; <?php echo date('Y'); ?> WeatherVoyager. All rights reserved.</p>                    <div class="small">
                        <a href="index.php" class="text-white me-2">Home</a>
                        <a href="../about" class="text-white me-2">About</a>
                        <a href="pages/contact" class="text-white">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
