<?php

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
    exit;
}

$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$tempUnit = isset($_POST['temp_unit']) ? $_POST['temp_unit'] : 'celsius';

if (empty($city) || empty($country)) {
    echo json_encode(['error' => 'City and country are required.']);
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
    echo json_encode(['error' => "City not found: $city, $country"]);
    exit;
}

list($city, $country, $lat, $lon) = $cityInfo;
$weatherUrl = OPENWEATHER_API_URL . "/weather?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
$forecastUrl = OPENWEATHER_API_URL . "/forecast?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
$currentResponse = file_get_contents($weatherUrl);
$currentData = json_decode($currentResponse, true);

if (!$currentData) {
    echo json_encode(['error' => "Could not fetch weather data for $city, $country"]);
    exit;
}
$forecastResponse = file_get_contents($forecastUrl);
$forecastData = json_decode($forecastResponse, true);

if (!$forecastData) {
    echo json_encode(['error' => "Could not fetch forecast data for $city, $country"]);
    exit;
}
$currentWeather = [
    'temp' => round($currentData['main']['temp']),
    'condition' => $currentData['weather'][0]['main'],
    'description' => $currentData['weather'][0]['description'],
    'humidity' => $currentData['main']['humidity'],
    'wind_speed' => round($currentData['wind']['speed'] * 3.6, 1),
    'uv_index' => 'unknown', // default since UV requires separate API call remmber to add anoother api
    'precipitation' => isset($currentData['rain']) ? ($currentData['rain']['1h'] ?? 0) : 0
];

if ($tempUnit === 'fahrenheit') {
    $currentWeather['temp'] = round($currentWeather['temp'] * 9 / 5 + 32);
    $currentWeather['temp_unit'] = '°F';
} else {
    $currentWeather['temp_unit'] = '°C';
}

// Process 5-day forecast
$forecast = [];
$processedDates = [];
foreach ($forecastData['list'] as $forecastItem) {
    $date = date('Y-m-d', $forecastItem['dt']);
    if (!in_array($date, $processedDates) && count($processedDates) < 5) {
        $day = [
            'date' => $date,
            'temp_max' => round($forecastItem['main']['temp_max']),
            'temp_min' => round($forecastItem['main']['temp_min']),
            'condition' => $forecastItem['weather'][0]['main'],
            'humidity' => $forecastItem['main']['humidity'],
            'wind_speed' => round($forecastItem['wind']['speed'] * 3.6, 1)
        ];

        if ($tempUnit === 'fahrenheit') {
            $day['temp_min'] = round($day['temp_min'] * 9 / 5 + 32);
            $day['temp_max'] = round($day['temp_max'] * 9 / 5 + 32);
            $day['temp_unit'] = '°F';
        } else {
            $day['temp_unit'] = '°C';
        }

        $forecast[] = $day;
        $processedDates[] = $date;
    }
}

$response = [
    'city' => $city,
    'country' => $country,
    'current' => $currentWeather,
    'forecast' => $forecast,
    'lat' => $lat,
    'lon' => $lon,
    'temp_unit' => $tempUnit === 'fahrenheit' ? '°F' : '°C'
];

echo json_encode($response);
