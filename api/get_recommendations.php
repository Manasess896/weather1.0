<?php
// Set a higher execution time limit
set_time_limit(300); // 5 minutes

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

// Get user preferences
$preferences = $_POST['preferences'] ?? [];
$minTemp = isset($_POST['min_temp']) ? (int)$_POST['min_temp'] : 15;
$maxTemp = isset($_POST['max_temp']) ? (int)$_POST['max_temp'] : 30;
$tempUnit = isset($_POST['temp_unit']) ? $_POST['temp_unit'] : 'celsius';
$selectedContinents = $_POST['continents'] ?? ['europe', 'north_america', 'asia', 'australia_oceania', 'africa', 'south_central_america'];

// If no continents are selected, default to all continents
if (empty($selectedContinents)) {
    $selectedContinents = ['europe', 'north_america', 'asia', 'australia_oceania', 'africa', 'south_central_america'];
}

// Convert temperature values if they're in Fahrenheit
if ($tempUnit === 'fahrenheit') {
    // Convert from F to C for API usage
    $minTemp = round(($minTemp - 32) * 5/9);
    $maxTemp = round(($maxTemp - 32) * 5/9);
}

// Initialize weather service
$weatherService = new WeatherService();

// Array to store city weather data and match scores
$cityScores = [];

// Get the cities array
$allCities = CITIES;

// Define continent indices ranges
$continentRanges = [
    'europe' => [0, 29],
    'north_america' => [30, 53],
    'asia' => [54, 78],
    'australia_oceania' => [79, 91],
    'africa' => [92, 104],
    'south_central_america' => [105, 122]
];

// Filter cities based on selected continents
$filteredCities = [];
foreach ($selectedContinents as $continent) {
    if (isset($continentRanges[$continent])) {
        [$start, $end] = $continentRanges[$continent];
        $filteredCities = array_merge($filteredCities, array_slice($allCities, $start, $end - $start + 1));
    }
}

// Maximum number of cities to process (for performance)
$maxCitiesToProcess = 40;

// If we have cached results, use fewer cities for processing
$cacheDir = dirname(__DIR__) . '/cache';
$cacheExists = file_exists($cacheDir . '/weather_cache.json');
$citiesToProcess = $cacheExists ? array_slice($filteredCities, 0, $maxCitiesToProcess) : 
                                 array_slice($filteredCities, 0, 20); // Use fewer cities on first run

// Get weather data for cities
foreach ($citiesToProcess as $cityInfo) {
    list($city, $country, $lat, $lon) = $cityInfo;
    
    // Get current weather (don't include UV index to reduce API calls)
    $currentWeather = $weatherService->getCurrentWeather($lat, $lon, false);
    
    if (!$currentWeather) {
        continue; // Skip if we couldn't fetch weather data
    }
    
    // Convert temperature if user selected Fahrenheit
    if ($tempUnit === 'fahrenheit') {
        $currentWeather['temp'] = round($currentWeather['temp'] * 9/5 + 32);
        $currentWeather['temp_unit'] = '°F';
    } else {
        $currentWeather['temp_unit'] = '°C';
    }
    
    // Get forecast
    $forecast = $weatherService->getForecast($lat, $lon);
    
    if (!$forecast) {
        continue; // Skip if we couldn't fetch forecast data
    }
    
    // Convert forecast temperatures if needed
    if ($tempUnit === 'fahrenheit') {
        foreach ($forecast as &$day) {
            $day['temp_min'] = round($day['temp_min'] * 9/5 + 32);
            $day['temp_max'] = round($day['temp_max'] * 9/5 + 32);
            $day['temp_unit'] = '°F';
        }
    } else {
        foreach ($forecast as &$day) {
            $day['temp_unit'] = '°C';
        }
    }
    
    // Calculate match score based on user preferences
    $matchScore = calculateMatchScore($currentWeather, $forecast, $preferences, $minTemp, $maxTemp);
    
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
    
    // Add to city scores
    $cityScores[] = [
        'city' => $city,
        'country' => $country,
        'lat' => $lat,
        'lng' => $lon,
        'current' => $currentWeather,
        'forecast' => $forecast,
        'match_score' => $matchScore,
        'activities' => $activities,
        'travel_advice' => $travelAdvice,
        'temp_unit' => $tempUnit
    ];
}

// Sort by match score (highest first)
usort($cityScores, function($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});

// Return top results (increased from 3 to 12)
echo json_encode(array_slice($cityScores, 0, 12));
exit;

/**
 * Calculate match score based on user preferences and weather data
 * 
 * @param array $currentWeather Current weather data
 * @param array $forecast Weather forecast data
 * @param array $preferences User preferences
 * @param int $minTemp Minimum preferred temperature
 * @param int $maxTemp Maximum preferred temperature
 * @return int Match score (0-100)
 */
function calculateMatchScore($currentWeather, $forecast, $preferences, $minTemp, $maxTemp) {
    $score = 0;
    $maxPossibleScore = 0;
    
    // Temperature score (weight: 30)
    $maxPossibleScore += 30;
    $temp = $currentWeather['temp'];
    
    if ($temp >= $minTemp && $temp <= $maxTemp) {
        // Full points if within range
        $score += 30;
    } else {
        // Partial points based on how close to range
        $distanceFromRange = min(abs($temp - $minTemp), abs($temp - $maxTemp));
        $score += max(0, 30 - ($distanceFromRange * 3));
    }
    
    // Check each preference
    foreach ($preferences as $pref) {
        $maxPossibleScore += 10;
        
        switch ($pref) {
            case 'sunny':
                if (
                    stripos($currentWeather['condition'], 'clear') !== false || 
                    stripos($currentWeather['condition'], 'sun') !== false
                ) {
                    $score += 10;
                } else if (stripos($currentWeather['condition'], 'partly') !== false) {
                    $score += 5; // Partial match
                }
                break;
                
            case 'cool_breeze':
                // Wind between 5-15 km/h is considered a cool breeze
                if ($currentWeather['wind_speed'] >= 5 && $currentWeather['wind_speed'] <= 15) {
                    $score += 10;
                } else if ($currentWeather['wind_speed'] > 0 && $currentWeather['wind_speed'] < 20) {
                    $score += 5; // Partial match
                }
                break;
                
            case 'low_humidity':
                if ($currentWeather['humidity'] < 50) {
                    $score += 10;
                } else if ($currentWeather['humidity'] < 70) {
                    $score += 5; // Partial match
                }
                break;
                
            case 'no_rain':
                // Check current and forecast for no rain
                $hasRain = stripos($currentWeather['condition'], 'rain') !== false || 
                          stripos($currentWeather['condition'], 'drizzle') !== false;
                
                $forecastRain = false;
                foreach ($forecast as $day) {
                    if (stripos($day['condition'], 'rain') !== false || $day['precipitation'] > 1) {
                        $forecastRain = true;
                        break;
                    }
                }
                
                if (!$hasRain && !$forecastRain) {
                    $score += 10;
                } else if (!$hasRain) {
                    $score += 5; // Partial match (no current rain)
                }
                break;
                
            case 'moderate_temp':
                if ($temp >= 18 && $temp <= 25) {
                    $score += 10; // Perfect moderate temperature
                } else if ($temp >= 15 && $temp <= 28) {
                    $score += 5; // Still acceptable
                }
                break;
                
            case 'clear_sky':
                if (
                    stripos($currentWeather['condition'], 'clear') !== false || 
                    (stripos($currentWeather['condition'], 'cloud') !== false && 
                     stripos($currentWeather['condition'], 'partly') !== false)
                ) {
                    $score += 10;
                } else if (stripos($currentWeather['condition'], 'cloud') === false) {
                    $score += 5; // Partial match
                }
                break;
        }
    }
    
    // If no preferences selected, use general weather quality score
    if (empty($preferences)) {
        $maxPossibleScore = 100;
        
        // Base score for pleasant temperature (15-28°C)
        if ($temp >= 15 && $temp <= 28) {
            $score += 40;
        } else if ($temp > 28 || $temp < 15) {
            $score += max(0, 40 - (abs($temp - 22) * 2)); // Decrease score as temp deviates from ideal
        }
        
        // Score for good conditions
        if (stripos($currentWeather['condition'], 'clear') !== false || 
            stripos($currentWeather['condition'], 'sun') !== false) {
            $score += 30;
        } else if (stripos($currentWeather['condition'], 'cloud') !== false && 
                  stripos($currentWeather['condition'], 'partly') !== false) {
            $score += 20;
        } else if (stripos($currentWeather['condition'], 'cloud') !== false) {
            $score += 10;
        }
        
        // Score for no precipitation
        $hasPrecipitation = 
            stripos($currentWeather['condition'], 'rain') !== false || 
            stripos($currentWeather['condition'], 'snow') !== false ||
            stripos($currentWeather['condition'], 'drizzle') !== false;
            
        if (!$hasPrecipitation) {
            $score += 30;
        }
    }
    
    // Calculate percentage score
    return min(100, round(($score / $maxPossibleScore) * 100));
}