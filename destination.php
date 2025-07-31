<?php
require_once 'includes/config.php';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$country = isset($_GET['country']) ? $_GET['country'] : '';

if (empty($city) || empty($country)) {
  header('Location: index.php');
  exit;
}
$cityFound = false;
$cityInfo = null;

foreach (CITIES as $info) {
  if ($info[0] === $city && $info[1] === $country) {
    $cityInfo = $info;
    $cityFound = true;
    break;
  }
}
if (!$cityFound) {
  $error = "City not found: $city, $country";
} else {
  list($city, $country, $lat, $lon) = $cityInfo;
  $weatherUrl = OPENWEATHER_API_URL . "/weather?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
  $forecastUrl = OPENWEATHER_API_URL . "/forecast?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
  $currentResponse = file_get_contents($weatherUrl);
  $currentData = json_decode($currentResponse, true);
  $forecastResponse = file_get_contents($forecastUrl);
  $forecastData = json_decode($forecastResponse, true);

  if (!$currentData || !$forecastData) {
    $error = 'Failed to retrieve weather information';
  } else {
    $destinationData = [
      'lat' => $lat,
      'lng' => $lon,
      'current' => [
        'temp' => round($currentData['main']['temp']),
        'condition' => $currentData['weather'][0]['main'],
        'humidity' => $currentData['main']['humidity'],
        'wind_speed' => round($currentData['wind']['speed'] * 3.6, 1),
        'uv_index' => 2 // Default since UV index requires separate API call
      ],
      'forecast' => []
    ];
    $processedDates = [];
    foreach ($forecastData['list'] as $forecast) {
      $date = date('Y-m-d', $forecast['dt']);
      if (!in_array($date, $processedDates) && count($processedDates) < 5) {
        $destinationData['forecast'][] = [
          'date' => $date,
          'temp_max' => round($forecast['main']['temp_max']),
          'temp_min' => round($forecast['main']['temp_min']),
          'condition' => $forecast['weather'][0]['main']
        ];
        $processedDates[] = $date;
      }
    }
  }
}
function getWeatherIconClass($condition)
{
  $condition = strtolower($condition);
  switch ($condition) {
    case 'clear':
    case 'sunny':
      return 'fas fa-sun text-warning';
    case 'clouds':
    case 'cloudy':
      return 'fas fa-cloud text-secondary';
    case 'rain':
    case 'drizzle':
      return 'fas fa-cloud-rain text-primary';
    case 'thunderstorm':
      return 'fas fa-bolt text-warning';
    case 'snow':
      return 'fas fa-snowflake text-info';
    case 'mist':
    case 'fog':
      return 'fas fa-smog text-muted';
    default:
      return 'fas fa-cloud text-secondary';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo htmlspecialchars($city) . ', ' . htmlspecialchars($country); ?> - WeatherVoyager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

  <link rel="stylesheet" href="css/styles.css">
 
  <link rel="stylesheet" href="css/style-fixes.css">
  
  <style>
    .destination-header {
      background: linear-gradient(to right, rgba(20, 184, 166, 0.8), rgba(34, 197, 94, 0.8));
      color: white;
      padding: 4rem 0;
      position: relative;
    }

    .destination-header h1 {
      font-weight: 700;
      font-size: 3rem;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
    }

    .weather-overview {
      background: white;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      margin-top: -3rem;
      position: relative;
    }

    .weather-stat {
      text-align: center;
      padding: 1rem;
      border-radius: 0.5rem;
      background: rgba(20, 184, 166, 0.1);
      margin-bottom: 1rem;
    }

    .attraction-card {
      border: none;
      border-radius: 0.5rem;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .attraction-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .attraction-img {
      height: 180px;
      object-fit: cover;
    }

    .section-title {
      position: relative;
      padding-bottom: 1rem;
      margin-bottom: 2rem;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 4px;
      background: linear-gradient(to right, var(--teal-primary), var(--green-primary));
    }

    .forecast-day {
      background: white;
      padding: 1.5rem;
      border-radius: 0.5rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      text-align: center;
    }

    .activity-item {
      background: white;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
      border-left: 4px solid var(--teal-primary);
      transition: all 0.3s ease;
    }

    .activity-item:hover {
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .photo-gallery img {
      border-radius: 0.5rem;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .photo-gallery img:hover {
      transform: scale(1.03);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .match-details {
      background: linear-gradient(135deg, #f8f9fa, #ffffff);
      padding: 2rem;
      border-radius: 1rem;
      border: 1px solid #e5e7eb;
    }

    .match-score-large {
      font-size: 3rem;
      font-weight: 700;
      color: var(--green-primary);
    }

    .weather-icon-large {
      font-size: 4rem;
    }

    .map-container {
      height: 400px;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark">
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
            <a class="nav-link" href="about.php">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="contact.php">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php if (isset($error)): ?>
    <div class="container my-5">
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
      </div>
      <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i> Back to Search</a>
      </div>
    </div>
  <?php else: ?>
    <header class="destination-header">
      <div class="container">
        <h1><?php echo htmlspecialchars($city); ?>, <?php echo htmlspecialchars($country); ?></h1>
        <p class="lead">Current weather and 5-day forecast</p>
      </div>
    </header>
    <div class="container">
      <div class="weather-overview">
        <div class="row">
          <div class="col-md-4 text-center">
            <div class="weather-icon-large">
              <i class="<?php echo getWeatherIconClass($destinationData['current']['condition']); ?>"></i>
            </div>
            <h2 class="mb-0"><?php echo $destinationData['current']['temp']; ?>°C</h2>
            <p class="text-muted"><?php echo htmlspecialchars($destinationData['current']['condition']); ?></p>
          </div>
          <div class="col-md-8">
            <div class="row">
              <div class="col-6 col-md-4">
                <div class="weather-stat">
                  <i class="fas fa-water text-primary mb-2"></i>
                  <h4><?php echo $destinationData['current']['humidity']; ?>%</h4>
                  <p class="mb-0 small">Humidity</p>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="weather-stat">
                  <i class="fas fa-wind text-info mb-2"></i>
                  <h4><?php echo $destinationData['current']['wind_speed']; ?> km/h</h4>
                  <p class="mb-0 small">Wind Speed</p>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="weather-stat">
                  <i class="fas fa-sun text-warning mb-2"></i>
                  <h4><?php echo $destinationData['current']['uv_index']; ?></h4>
                  <p class="mb-0 small">UV Index</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  
    <div class="container my-5">
      <div class="row">
        <div class="col-lg-12 mb-4">
          <section class="mb-5">
            <h2 class="section-title">5-Day Forecast</h2>
            <div class="row">
              <?php foreach ($destinationData['forecast'] as $day): ?>
                <div class="col-md-4 mb-4">
                  <div class="forecast-day">
                    <h5><?php echo date('D', strtotime($day['date'])); ?></h5>
                    <i class="<?php echo getWeatherIconClass($day['condition']); ?> fa-2x my-3"></i>
                    <h4><?php echo $day['temp_max']; ?>°C</h4>
                    <p class="mb-1"><?php echo $day['temp_min']; ?>°C</p>
                    <p class="mb-0 small"><?php echo htmlspecialchars($day['condition']); ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
          <section>
            <h2 class="section-title">Location</h2>
            <div id="destination-map" class="map-container"></div>
          </section>
        </div>
      </div>
    </div>

  <?php endif; ?>

  <footer class="bg-dark text-white py-4">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="fas fa-cloud-sun-rain me-2"></i> WeatherVoyager</h5>
          <p>Finding your perfect weather destination since 2025.</p>
        </div>
        <div class="col-md-3">
          <h5>Links</h5>
          <ul class="list-unstyled">
            <li><a href="index.php" class="text-white">Home</a></li>
            <li><a href="about.php" class="text-white">About</a></li>
            <li><a href="contact.php" class="text-white">Contact</a></li>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>
  <script>
    $(document).ready(function() {
      <?php if (!isset($error)): ?>
        var map = L.map('destination-map').setView([<?php echo $destinationData['lat']; ?>, <?php echo $destinationData['lng']; ?>], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          maxZoom: 18
        }).addTo(map);
        L.marker([<?php echo $destinationData['lat']; ?>, <?php echo $destinationData['lng']; ?>])
          .addTo(map)
          .bindPopup('<strong><?php echo htmlspecialchars($city); ?>, <?php echo htmlspecialchars($country); ?></strong><br><?php echo $destinationData['current']['temp']; ?>°C | <?php echo htmlspecialchars($destinationData['current']['condition']); ?>')
          .openPopup();
      <?php endif; ?>
    });
  </script>
</body>

</html>