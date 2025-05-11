<?php
// Load environment variables
require_once __DIR__ . '/config/env_loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - WeatherVoyager</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/weather1.0/css/styles.css">
</head>
<body>
    <!-- Header/Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/weather1.0/home">
                <i class="fas fa-cloud-sun-rain me-2"></i>
                WeatherVoyager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/weather1.0/home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/weather1.0/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/weather1.0/contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 404 Content -->
    <div class="container my-5 py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div class="display-1 text-primary mb-3">
                    <i class="fas fa-cloud-showers-heavy"></i> 404
                </div>
                <h1 class="mb-4">Page Not Found</h1>
                <p class="lead mb-5">Oops! The weather forecast for this page isn't looking good. The page you're looking for may have been moved, deleted, or might never have existed.</p>
                <div class="d-flex justify-content-center">
                    <a href="/weather1.0/home" class="btn btn-primary me-2">
                        <i class="fas fa-home me-2"></i> Return Home
                    </a>
                    <a href="/weather1.0/contact" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i> Contact Us
                    </a>
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
                    <p class="small">&copy; <?php echo date('Y'); ?> WeatherVoyager. All rights reserved.</p>
                    <div class="small">
                        <a href="/weather1.0/home" class="text-white me-2">Home</a>
                        <a href="/weather1.0/about" class="text-white me-2">About</a>
                        <a href="/weather1.0/contact" class="text-white">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
