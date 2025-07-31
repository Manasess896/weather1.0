<?php

set_time_limit(300);

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: ../405.php');
    exit;
}

$preferences = $_POST['preferences'] ?? [];
$minTemp = isset($_POST['min_temp']) ? (int)$_POST['min_temp'] : 15;
$maxTemp = isset($_POST['max_temp']) ? (int)$_POST['max_temp'] : 30;
$tempUnit = isset($_POST['temp_unit']) ? $_POST['temp_unit'] : 'celsius';
$selectedContinents = $_POST['continents'] ?? ['europe', 'north_america', 'asia', 'australia_oceania', 'africa', 'south_central_america'];

if (empty($selectedContinents)) {
    $selectedContinents = ['europe', 'north_america', 'asia', 'australia_oceania', 'africa', 'south_central_america'];
}

if ($tempUnit === 'fahrenheit') {
    $minTemp = round(($minTemp - 32) * 5 / 9);
    $maxTemp = round(($maxTemp - 32) * 5 / 9);
}

$cityScores = [];
$allCities = CITIES;

$continentRanges = [
    'europe' => [0, 29],
    'north_america' => [30, 53],
    'asia' => [54, 78],
    'australia_oceania' => [79, 91],
    'africa' => [92, 104],
    'south_central_america' => [105, 122]
];

$filteredCities = [];
foreach ($selectedContinents as $continent) {
    if (isset($continentRanges[$continent])) {
        [$start, $end] = $continentRanges[$continent];
        $filteredCities = array_merge($filteredCities, array_slice($allCities, $start, $end - $start + 1));
    }
}

$maxCitiesToProcess = 30;
$citiesToProcess = array_slice($filteredCities, 0, $maxCitiesToProcess);

foreach ($citiesToProcess as $cityInfo) {
    list($city, $country, $lat, $lon) = $cityInfo;

    $weatherUrl = OPENWEATHER_API_URL . "/weather?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
    $currentResponse = file_get_contents($weatherUrl);
    $currentData = json_decode($currentResponse, true);

    if (!$currentData) {
        continue;
    }

    $currentWeather = [
        'temp' => round($currentData['main']['temp']),
        'condition' => $currentData['weather'][0]['main'],
        'description' => $currentData['weather'][0]['description'],
        'humidity' => $currentData['main']['humidity'],
        'wind_speed' => round($currentData['wind']['speed'] * 3.6, 1),
        'precipitation' => isset($currentData['rain']) ? ($currentData['rain']['1h'] ?? 0) : 0
    ];

    if ($tempUnit === 'fahrenheit') {
        $currentWeather['temp'] = round($currentWeather['temp'] * 9 / 5 + 32);
        $currentWeather['temp_unit'] = '°F';
    } else {
        $currentWeather['temp_unit'] = '°C';
    }
    $forecastUrl = OPENWEATHER_API_URL . "/forecast?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
    $forecastResponse = file_get_contents($forecastUrl);
    $forecastData = json_decode($forecastResponse, true);

    if (!$forecastData) {
        continue;
    }
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
                'wind_speed' => round($forecastItem['wind']['speed'] * 3.6, 1),
                'precipitation' => isset($forecastItem['rain']) ? ($forecastItem['rain']['3h'] ?? 0) : 0
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

    $matchScore = calculateMatchScore($currentWeather, $forecast, $preferences, $minTemp, $maxTemp);

    $cityScores[] = [
        'city' => $city,
        'country' => $country,
        'lat' => $lat,
        'lng' => $lon,
        'current' => $currentWeather,
        'forecast' => $forecast,
        'match_score' => $matchScore
    ];
}

usort($cityScores, function ($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});
echo json_encode(array_slice($cityScores, 0, 12));
exit;

/**
 * @param array 
 * @param array 
 * @param array 
 * @param int 
 * @param int 
 * @return int 
 */
function calculateMatchScore($currentWeather, $forecast, $preferences, $minTemp, $maxTemp)
{
    $score = 0;
    $maxPossibleScore = 0;
    $maxPossibleScore += 30;
    $temp = $currentWeather['temp'];

    if ($temp >= $minTemp && $temp <= $maxTemp) {
        $score += 30;
    } else {
        $distanceFromRange = min(abs($temp - $minTemp), abs($temp - $maxTemp));
        $score += max(0, 30 - ($distanceFromRange * 3));
    }
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
                    $score += 5;
                }
                break;

            case 'cool_breeze':
                if ($currentWeather['wind_speed'] >= 5 && $currentWeather['wind_speed'] <= 15) {
                    $score += 10;
                } else if ($currentWeather['wind_speed'] > 0 && $currentWeather['wind_speed'] < 20) {
                    $score += 5;
                }
                break;

            case 'low_humidity':
                if ($currentWeather['humidity'] < 50) {
                    $score += 10;
                } else if ($currentWeather['humidity'] < 70) {
                    $score += 5; 
                }
                break;

            case 'no_rain':
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
                    $score += 5;
                }
                break;

            case 'moderate_temp':
                if ($temp >= 18 && $temp <= 25) {
                    $score += 10;
                } else if ($temp >= 15 && $temp <= 28) {
                    $score += 5;
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
                    $score += 5;
                }
                break;
        }
    }
    if (empty($preferences)) {
        $maxPossibleScore = 100;
        if ($temp >= 15 && $temp <= 28) {
            $score += 40;
        } else if ($temp > 28 || $temp < 15) {
            $score += max(0, 40 - (abs($temp - 22) * 2));
        }
        if (
            stripos($currentWeather['condition'], 'clear') !== false ||
            stripos($currentWeather['condition'], 'sun') !== false
        ) {
            $score += 30;
        } else if (
            stripos($currentWeather['condition'], 'cloud') !== false &&
            stripos($currentWeather['condition'], 'partly') !== false
        ) {
            $score += 20;
        } else if (stripos($currentWeather['condition'], 'cloud') !== false) {
            $score += 10;
        }
        $hasPrecipitation =
            stripos($currentWeather['condition'], 'rain') !== false ||
            stripos($currentWeather['condition'], 'snow') !== false ||
            stripos($currentWeather['condition'], 'drizzle') !== false;

        if (!$hasPrecipitation) {
            $score += 30;
        }
    }
    return min(100, round(($score / $maxPossibleScore) * 100));
}
