<?php
// Include configuration and services
require_once '../includes/config.php';
require_once '../includes/WeatherService.php';

// Set headers to allow JSON response
header('Content-Type: application/json');

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
    exit;
}

// Get city and country from request
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';

if (empty($city) || empty($country)) {
    echo json_encode(['error' => 'City and country are required.']);
    exit;
}

// Find the city in our predefined list
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
    echo json_encode(['error' => "City not found: $city, $country"]);
    exit;
}

// Get coordinates
list($city, $country, $lat, $lon) = $cityInfo;

// Initialize weather service
$weatherService = new WeatherService();

// Get current weather
$currentWeather = $weatherService->getCurrentWeather($lat, $lon, true);

if (!$currentWeather) {
    echo json_encode(['error' => "Could not fetch weather data for $city, $country"]);
    exit;
}

// Get forecast
$forecast = $weatherService->getForecast($lat, $lon);

if (!$forecast) {
    echo json_encode(['error' => "Could not fetch forecast data for $city, $country"]);
    exit;
}

// Add average precipitation to current weather for activity suggestions
$precipitation = 0;
foreach ($forecast as $day) {
    $precipitation += $day['precipitation'];
}
$currentWeather['precipitation'] = $precipitation / count($forecast);

// Suggest activities
$activities = $weatherService->suggestActivities($currentWeather);

// Generate travel advice
$travelAdvice = $weatherService->generateTravelAdvice($currentWeather, $forecast);

// Create response
$response = [
    'city' => $city,
    'country' => $country,
    'lat' => $lat,
    'lng' => $lon,
    'current' => $currentWeather,
    'forecast' => $forecast,
    'activities' => $activities,
    'travel_advice' => $travelAdvice,
    'match_score' => 100 // Default high score for a manually selected favorite
];

// Return the response
echo json_encode($response);
exit;