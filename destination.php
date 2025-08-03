<?php
require_once 'includes/config.php';

function getWeatherIconClass($condition)
{
  $condition = strtolower($condition);
  if (strpos($condition, 'thunderstorm') !== false) return 'fas fa-bolt text-warning';
  if (strpos($condition, 'drizzle') !== false) return 'fas fa-cloud-rain text-primary';
  if (strpos($condition, 'rain') !== false) return 'fas fa-cloud-showers-heavy text-primary';
  if (strpos($condition, 'snow') !== false) return 'fas fa-snowflake text-info';
  if (strpos($condition, 'mist') !== false || strpos($condition, 'fog') !== false || strpos($condition, 'haze') !== false) return 'fas fa-smog text-muted';
  if (strpos($condition, 'clear') !== false || strpos($condition, 'sunny') !== false) return 'fas fa-sun text-warning';
  if (strpos($condition, 'clouds') !== false) return 'fas fa-cloud text-secondary';
  return 'fas fa-cloud-sun text-secondary';
}

function getSuggestedActivities($condition)
{
  $condition = strtolower($condition);
  $activities = [];
  //i know what you are going to say i should use an api for this but am too lazy to look for one
  if (strpos($condition, 'sun') !== false || strpos($condition, 'clear') !== false) {
    $activities = [
      ['name' => 'Sightseeing Tour', 'icon' => 'fa-binoculars'],
      ['name' => 'Beach Day / Outdoor Picnic', 'icon' => 'fa-umbrella-beach'],
      ['name' => 'Hiking or Nature Walk', 'icon' => 'fa-hiking']
    ];
  } elseif (strpos($condition, 'cloud') !== false) {
    $activities = [
      ['name' => 'City Exploration & Photography', 'icon' => 'fa-camera-retro'],
      ['name' => 'Visit a Local Market', 'icon' => 'fa-store'],
      ['name' => 'Cafe Hopping', 'icon' => 'fa-coffee']
    ];
  } elseif (strpos($condition, 'rain') !== false || strpos($condition, 'drizzle') !== false) {
    $activities = [
      ['name' => 'Museum or Art Gallery Visit', 'icon' => 'fa-landmark'],
      ['name' => 'Indoor Shopping', 'icon' => 'fa-shopping-bag'],
      ['name' => 'Try a Local Cooking Class', 'icon' => 'fa-utensils']
    ];
  } else {
    $activities = [
      ['name' => 'Explore Local Cuisine', 'icon' => 'fa-utensils'],
      ['name' => 'Visit Historical Sites', 'icon' => 'fa-landmark-dome'],
      ['name' => 'Check for Local Events', 'icon' => 'fa-calendar-check']
    ];
  }
  return $activities;
}

$city = isset($_GET['city']) ? $_GET['city'] : '';
$country = isset($_GET['country']) ? $_GET['country'] : '';

if (empty($city) || empty($country)) {
  header('Location: home');
  exit;
}

$cityInfo = null;
foreach (CITIES as $info) {
  if ($info[0] === $city && $info[1] === $country) {
    $cityInfo = $info;
    break;
  }
}

if (!$cityInfo) {
  $error = "City not found: $city, $country";
} else {
  list($city, $country, $lat, $lon) = $cityInfo;

  // --- Parallel API Requests using cURL ---
  $mh = curl_multi_init();
  $handles = [];

  if (!defined('PEXELS_API_KEY') || empty(PEXELS_API_KEY)) {
    error_log("Pexels API key is not set");
  }

  $urls = [
    'weather' => OPENWEATHER_API_URL . "/weather?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY,
    'forecast' => OPENWEATHER_API_URL . "/forecast?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY,
    'photos' => 'https://api.pexels.com/v1/search?query=' . urlencode($city . ' ' . $country . ' landmark travel') . '&per_page=6&orientation=landscape'
  ];

  $pexels_headers = ['Authorization: ' . PEXELS_API_KEY];

  error_log("Pexels request URL: " . $urls['photos']);

  foreach ($urls as $key => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if ($key === 'photos') {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $pexels_headers);
    }
    curl_multi_add_handle($mh, $ch);
    $handles[$key] = $ch;
  }

  $running = null;
  do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
  } while ($running > 0);

  $currentResponse = curl_multi_getcontent($handles['weather']);
  $forecastResponse = curl_multi_getcontent($handles['forecast']);
  $photosResponse = curl_multi_getcontent($handles['photos']);

  foreach ($handles as $ch) {
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
  }
  curl_multi_close($mh);

  $currentData = json_decode($currentResponse, true);
  $forecastData = json_decode($forecastResponse, true);
  $photosData = json_decode($photosResponse, true);

  if (!$currentData || !$forecastData) {
    $error = 'Failed to retrieve weather information.';
  } else {
    $destinationData = [
      'lat' => $lat,
      'lng' => $lon,
      'current' => [
        'temp' => round($currentData['main']['temp']),
        'condition' => $currentData['weather'][0]['main'],
        'description' => $currentData['weather'][0]['description'],
        'humidity' => $currentData['main']['humidity'],
        'wind_speed' => round($currentData['wind']['speed'] * 3.6, 1),
        'uv_index' => 2 // Placeholder
      ],
      'forecast' => [],
      'photos' => $photosData['photos'] ?? []
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
    $newsArticles = [];
    $newsFeedUrl = "https://news.google.com/rss/search?q=" . urlencode($country . " weather");
    $newsRss = @simplexml_load_file($newsFeedUrl);
    if ($newsRss && isset($newsRss->channel->item)) {
      $count = 0;
      foreach ($newsRss->channel->item as $item) {
        if ($count >= 4) break;
        $newsArticles[] = [
          'title' => (string)$item->title,
          'link' => (string)$item->link,
          'pubDate' => (string)$item->pubDate,
          'source' => (string)$item->source
        ];
        $count++;
      }
    }
    $suggestedActivities = getSuggestedActivities($destinationData['current']['condition']);
  }
}
?>

<?php
$headerImageUrl = !empty($destinationData['photos']) ? $destinationData['photos'][0]['src']['large2x'] : 'https://source.unsplash.com/1600x900/?travel';
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
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/style-fixes.css">
  <link rel="stylesheet" href="css/gallery.css">

  <style>
    .destination-header {
      background: linear-gradient(135deg, rgba(13, 148, 136, 0.8), rgba(20, 184, 166, 0.8)), url('<?php echo $headerImageUrl; ?>') no-repeat center center;
      background-size: cover;
      color: white;
      padding: 4rem 0;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
    }

    .weather-overview {
      background: white;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      margin-top: -3rem;
      position: relative;
      z-index: 10;
    }

    .section-title {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: var(--teal-dark);
    }

    .forecast-day {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 0.5rem;
      text-align: center;
      transition: all 0.3s ease;
    }

    .forecast-day:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .activity-item,
    .news-item {
      background: #f8f9fa;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
      border-left: 4px solid var(--teal-primary);
      transition: all 0.3s ease;
    }

    .activity-item:hover,
    .news-item:hover {
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .news-item a {
      text-decoration: none;
      color: #333;
      font-weight: 600;
    }

    .news-item .source {
      font-size: 0.8rem;
      color: #6c757d;
    }

    .map-container {
      height: 400px;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .photo-gallery .gallery-item {
      cursor: pointer;
      overflow: hidden;
      border-radius: 0.5rem;
    }

    .photo-gallery img {
      transition: transform 0.3s ease;
    }

    .photo-gallery .gallery-item:hover img {
      transform: scale(1.05);
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
    <div class="container">
      <a class="navbar-brand" href="home"><i class="fas fa-cloud-sun-rain me-2"></i>WeatherVoyager</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="home">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="news">Weather News</a></li>
          <li class="nav-item"><a class="nav-link" href="about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <?php if (isset($error)): ?>
    <div class="container my-5">
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?></div>
      <div class="text-center mt-4"><a href="home" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i> Back to Search</a></div>
    </div>
  <?php else: ?>
    <header class="destination-header">
      <div class="container text-center">
        <h1><?php echo htmlspecialchars($city); ?>, <?php echo htmlspecialchars($country); ?></h1>
        <p class="lead mb-0"><?php echo htmlspecialchars($destinationData['current']['description']); ?></p>
      </div>
    </header>

    <div class="container my-5">
      <div class="row g-5">
        <div class="col-lg-8">

          <div class="weather-overview mb-5">
            <div class="row align-items-center">
              <div class="col-md-4 text-center">
                <div class="weather-icon-large" style="font-size: 4rem;">
                  <i class="<?php echo getWeatherIconClass($destinationData['current']['condition']); ?>"></i>
                </div>
                <h2 class="mb-0" style="color: #0d9488;"><?php echo $destinationData['current']['temp']; ?>°C</h2>
                <p class="text-muted"><?php echo htmlspecialchars($destinationData['current']['condition']); ?></p>
              </div>
              <div class="col-md-8">
                <div class="row">
                  <div class="col-6 col-md-4 text-center p-2"><i class="fas fa-water text-primary mb-2"></i>
                    <h4><?php echo $destinationData['current']['humidity']; ?>%</h4>
                    <p class="mb-0 small">Humidity</p>
                  </div>
                  <div class="col-6 col-md-4 text-center p-2"><i class="fas fa-wind text-info mb-2"></i>
                    <h4><?php echo $destinationData['current']['wind_speed']; ?> km/h</h4>
                    <p class="mb-0 small">Wind</p>
                  </div>
                  <div class="col-6 col-md-4 text-center p-2"><i class="fas fa-sun text-warning mb-2"></i>
                    <h4><?php echo $destinationData['current']['uv_index']; ?></h4>
                    <p class="mb-0 small">UV Index</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 5-Day Forecast for the view more page -->
          <section class="mb-5">
            <h2 class="section-title" style="color: #0d9488;">5-Day Forecast</h2>
            <div class="row g-3">
              <?php foreach ($destinationData['forecast'] as $day): ?>
                <div class="col">
                  <div class="forecast-day h-100">
                    <h5><?php echo date('D', strtotime($day['date'])); ?></h5>
                    <i class="<?php echo getWeatherIconClass($day['condition']); ?> fa-2x my-2"></i>
                    <p class="mb-1 fw-bold"><?php echo $day['temp_max']; ?>°</p>
                    <p class="mb-0 text-muted"><?php echo $day['temp_min']; ?>°</p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </section>

          <?php
          if (empty($destinationData['photos'])) {
            echo '<!-- Debug: No photos available. PhotosData: ' . htmlspecialchars(json_encode($photosData)) . ' -->';
          }
          if (!empty($destinationData['photos'])): ?>
            <section class="mb-5">
              <h2 class="section-title" style="color: #0d9488;">Photo Gallery</h2>
              <div class="photo-gallery">
                <?php foreach ($destinationData['photos'] as $photo): ?>
                  <div class="photo-card"
                    data-full-image="<?php echo htmlspecialchars($photo['src']['large2x']); ?>"
                    data-photographer="<?php echo htmlspecialchars($photo['photographer']); ?>"
                    data-photographer-url="<?php echo htmlspecialchars($photo['photographer_url']); ?>">
                    <img src="<?php echo htmlspecialchars($photo['src']['large']); ?>"
                      alt="<?php echo htmlspecialchars($photo['alt'] ?? "$city, $country"); ?>"
                      loading="lazy">
                    <div class="photo-info">
                      <p class="mb-1">📸 by <a href="<?php echo htmlspecialchars($photo['photographer_url']); ?>"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="photographer"><?php echo htmlspecialchars($photo['photographer']); ?></a></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="pexels-attribution">
                Photos provided by <a href="https://www.pexels.com" target="_blank" rel="noopener noreferrer">Pexels</a>
              </div>
            </section>
          <?php endif; ?>

          <!-- Map from leaflet  -->
          <section>
            <h2 class="section-title" style="color: #0d9488;">Location on Map</h2>
            <div id="destination-map" class="map-container"></div>
          </section>
        </div>
        <div class="col-lg-4">
          <!-- Suggested activities again am not using an api but if you find one for this let me know  -->
          <section class="mb-5">
            <h2 class="section-title" style="color: #0d9488;">Suggested Activities</h2>
            <?php foreach ($suggestedActivities as $activity): ?>
              <div class="activity-item">
                <i class="fas <?php echo $activity['icon']; ?> me-2 text-primary"></i>
                <span><?php echo htmlspecialchars($activity['name']); ?></span>
              </div>
            <?php endforeach; ?>
          </section>

          <!-- weather news from the google something i dont know if its an api or what  -->
          <section>
            <h2 class="section-title" style="color: #0d9488;">Latest Weather News</h2>
            <?php if (empty($newsArticles)): ?>
              <p>No recent weather news found for <?php echo htmlspecialchars($country); ?>.</p>
            <?php else: ?>
              <?php foreach ($newsArticles as $article): ?>
                <div class="news-item">
                  <a href="<?php echo htmlspecialchars($article['link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($article['title']); ?></a>
                  <div class="source">
                    <span><?php echo htmlspecialchars($article['source']); ?> &bull; <?php echo date('M j, Y', strtotime($article['pubDate'])); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Photo Modal -->
  <div class="modal fade photo-modal" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="photoModalLabel">
            📸 Photo by <a id="photographerLink" href="#" target="_blank" rel="noopener noreferrer"><span id="photographerName"></span></a>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border text-light" role="status" id="modalSpinner">
              <span class="visually-hidden">Loading...</span>
            </div>
            <img id="modalImage" src="" class="modal-photo" alt="Enlarged destination photo">
          </div>
        </div>
        <div class="modal-footer">
          <p class="mb-0">Powered by <a href="https://www.pexels.com" target="_blank" rel="noopener noreferrer" class="text-white">Pexels</a></p>
        </div>
      </div>
    </div>
  </div>

  <footer class="py-4 mt-auto" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white;">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="fas fa-cloud-sun-rain me-2" style="color: #14b8a6;"></i> WeatherVoyager</h5>
          <p style="color: #5eead4;">Finding your perfect weather destination since 2025.</p>
        </div>
        <div class="col-md-3">
          <h5 style="color: #14b8a6;">Links</h5>
          <ul class="list-unstyled">
            <li><a href="home" style="color: #5eead4; text-decoration: none;">Home</a></li>
            <li><a href="news" style="color: #5eead4; text-decoration: none;">Weather News</a></li>
            <li><a href="about" style="color: #5eead4; text-decoration: none;">About</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h5 style="color: #14b8a6;">Data Sources</h5>
          <ul class="list-unstyled">
            <li style="color: #5eead4;">OpenWeather API</li>
            <li style="color: #5eead4;">Pexels API</li>
            <li style="color: #5eead4;">Google News RSS</li>
          </ul>
        </div>
      </div>
      <div class="text-center mt-3">
        <p class="mb-0" style="color: #5eead4;">&copy; 2025 WeatherVoyager. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="js/gallery.js"></script>
  <script>
    $(document).ready(function() {
      <?php if (!isset($error)): ?>
        var lat = <?php echo $destinationData['lat']; ?>;
        var lng = <?php echo $destinationData['lng']; ?>;
        var map = L.map('destination-map').setView([lat, lng], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          maxZoom: 18
        }).addTo(map);

        L.marker([lat, lng])
          .addTo(map)
          .bindPopup('<strong><?php echo htmlspecialchars($city); ?>, <?php echo htmlspecialchars($country); ?></strong>')
          .openPopup();

        $('#photoModal').on('show.bs.modal', function(event) {
          var button = $(event.relatedTarget);
          var imgSrc = button.data('img-src');
          var modalImage = $('#modalImage');
          modalImage.attr('src', imgSrc);
        });
      <?php endif; ?>
    });
  </script>
</body>

</html>