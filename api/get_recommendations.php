<?php
//the brain - Enhanced Algorithm with Smart Preference Validation
// --- Scientifically-Based Weather Thresholds (Global Standards) ---
define('EXTREME_HOT_TEMP_C', 35);      // >35°C (95°F) - Heatwave
define('EXTREME_COLD_TEMP_C', 0);       // <0°C (32°F) - Freezing
define('HEAVY_RAIN_MM_PER_HOUR', 1.5);  // >=1.5mm/hr - Moderate to Heavy Rain
define('HEAVY_RAIN_MM_PER_3H', 4.5);    // >=4.5mm/3hr
define('STRONG_WIND_KMH', 40);          // >=40 km/h (11 m/s) - Strong Breeze
define('HIGH_HUMIDITY', 85);            // >=85% - Very Humid
define('NORMAL_TEMP_MIN_C', 15);        // 15°C (59°F)
define('NORMAL_TEMP_MAX_C', 30);        // 30°C (86°F)
define('NORMAL_HUMIDITY_MIN', 30);      // 30%
define('NORMAL_HUMIDITY_MAX', 70);      // 70%
define('NORMAL_WIND_KMH', 28);          // <28 km/h (8 m/s) - Gentle to Moderate Breeze

// Enhanced preference weights for new weather profiles
define('PREFERENCE_WEIGHTS', [
    'hot-dry' => 20,        // High weight - hot and arid conditions
    'warm-sunny' => 22,     // High weight - most sought after
    'balanced' => 18,       // High weight - general appeal
    'mild-rainy' => 12,     // Moderate weight - specific preference
    'cool-humid' => 10,     // Moderate weight - comfort factor
    'cold-snowy' => 15,     // Strong preference when selected
]);

define('SEASONAL_FACTORS', [
    'winter' => ['cold-snowy' => 1.3, 'cool-humid' => 1.2, 'balanced' => 1.1],
    'spring' => ['warm-sunny' => 1.2, 'balanced' => 1.1, 'mild-rainy' => 1.1],
    'summer' => ['hot-dry' => 1.2, 'warm-sunny' => 1.3, 'balanced' => 1.1],
    'autumn' => ['cool-humid' => 1.2, 'mild-rainy' => 1.1, 'balanced' => 1.1]
]);

define('CLIMATE_ADJUSTMENTS', [
    'tropical' => ['cool-humid' => 1.2, 'hot-dry' => 1.1],
    'desert' => ['hot-dry' => 1.3, 'warm-sunny' => 1.2],
    'temperate' => ['warm-sunny' => 1.1, 'balanced' => 1.1],
    'polar' => ['cold-snowy' => 1.4, 'cool-humid' => 1.3]
]);

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

$mh = curl_multi_init();
$handles = [];
$citiesToProcessData = [];

foreach ($citiesToProcess as $index => $cityInfo) {
    list($city, $country, $lat, $lon) = $cityInfo;

    $weatherUrl = OPENWEATHER_API_URL . "/weather?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
    $forecastUrl = OPENWEATHER_API_URL . "/forecast?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;

    $weatherCh = curl_init($weatherUrl);
    curl_setopt($weatherCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($weatherCh, CURLOPT_HEADER, 0);
    curl_multi_add_handle($mh, $weatherCh);

    $forecastCh = curl_init($forecastUrl);
    curl_setopt($forecastCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($forecastCh, CURLOPT_HEADER, 0);
    curl_multi_add_handle($mh, $forecastCh);

    $handles[$index] = ['weather' => $weatherCh, 'forecast' => $forecastCh];
    $citiesToProcessData[$index] = $cityInfo;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$cityScores = [];

foreach ($handles as $index => $handlePair) {
    $currentResponse = curl_multi_getcontent($handlePair['weather']);
    $forecastResponse = curl_multi_getcontent($handlePair['forecast']);

    curl_multi_remove_handle($mh, $handlePair['weather']);
    curl_close($handlePair['weather']);
    curl_multi_remove_handle($mh, $handlePair['forecast']);
    curl_close($handlePair['forecast']);

    list($city, $country, $lat, $lon) = $citiesToProcessData[$index];

    $currentData = $currentResponse ? json_decode($currentResponse, true) : null;
    $forecastData = $forecastResponse ? json_decode($forecastResponse, true) : null;

    $currentWeather = null;
    if ($currentData && isset($currentData['main'])) {
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
    }

    $forecast = [];
    if ($forecastData && isset($forecastData['list'])) {
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
    }

    if ($currentWeather === null) {
        continue;
    }

    $matchScore = calculateMatchScore($currentWeather, $forecast, $preferences, $minTemp, $maxTemp, $lat, $lon);

    $cityScores[] = [
        'city' => $city,
        'country' => $country,
        'lat' => $lat,
        'lng' => $lon,
        'current' => $currentWeather,
        'forecast' => $forecast,
        'match_score' => $matchScore,
        'temp_unit' => $tempUnit === 'fahrenheit' ? '°F' : '°C'
    ];
}

curl_multi_close($mh);

usort($cityScores, function ($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});

$results = array_slice($cityScores, 0, 12);

$algorithmMeta = [
    'algorithm_version' => '2.0_enhanced',
    'total_cities_analyzed' => count($cityScores),
    'avg_score' => count($cityScores) > 0 ? round(array_sum(array_column($cityScores, 'match_score')) / count($cityScores), 1) : 0,
    'top_score' => count($cityScores) > 0 ? max(array_column($cityScores, 'match_score')) : 0,
    'preferences_count' => count($preferences),
    'temp_range' => $maxTemp - $minTemp,
    'temp_unit' => $tempUnit
];

echo json_encode([
    'destinations' => $results,
    'meta' => $algorithmMeta
]);
exit;

function getClimateType($lat, $lon, $temp, $humidity)
{
    if (abs($lat) < 23.5) {
        if ($humidity > 70) return 'tropical';
        if ($humidity < 30 && $temp > 25) return 'desert';
    }

    $desertRegions = [
        ['lat_min' => 15, 'lat_max' => 35, 'lon_min' => -10, 'lon_max' => 50], // Sahara
        ['lat_min' => 25, 'lat_max' => 40, 'lon_min' => 40, 'lon_max' => 65],  // Arabian
        ['lat_min' => -35, 'lat_max' => -15, 'lon_min' => 110, 'lon_max' => 155], // Australian
        ['lat_min' => 25, 'lat_max' => 45, 'lon_min' => -125, 'lon_max' => -105]  // SW USA
    ];

    foreach ($desertRegions as $region) {
        if (
            $lat >= $region['lat_min'] && $lat <= $region['lat_max'] &&
            $lon >= $region['lon_min'] && $lon <= $region['lon_max']
        ) {
            return 'desert';
        }
    }

    if (abs($lat) > 66.5) return 'polar';

    return 'temperate';
}

function getCurrentSeason($lat)
{
    $month = date('n'); // 1-12
    $isNorthern = $lat > 0;

    if ($isNorthern) {
        if ($month >= 3 && $month <= 5) return 'spring';
        if ($month >= 6 && $month <= 8) return 'summer';
        if ($month >= 9 && $month <= 11) return 'autumn';
        return 'winter';
    } else {
        // Southern hemisphere - seasons are opposite
        if ($month >= 3 && $month <= 5) return 'autumn';
        if ($month >= 6 && $month <= 8) return 'winter';
        if ($month >= 9 && $month <= 11) return 'spring';
        return 'summer';
    }
}


function calculateComfortIndex($temp, $humidity, $wind, $precip)
{
    $comfortScore = 100;

    if ($temp >= 18 && $temp <= 24) {
        $comfortScore += 10;
    } else {
        $tempDeviation = ($temp < 18) ? (18 - $temp) : ($temp - 24);
        $comfortScore -= min(30, $tempDeviation * 2);
    }
    if ($humidity >= 40 && $humidity <= 60) {
        $comfortScore += 10;
    } else {
        $humidityDeviation = ($humidity < 40) ? (40 - $humidity) : ($humidity - 60);
        $comfortScore -= min(20, $humidityDeviation * 0.5);
    }
    if ($wind >= 5 && $wind <= 15) {
        $comfortScore += 5;
    } else if ($wind < 5) {
        $comfortScore -= 5; // Too still
    } else {
        $comfortScore -= min(15, ($wind - 15) * 0.3);
    }
    $comfortScore -= min(20, $precip * 5);

    return max(0, $comfortScore);
}

/**
 * @param array $currentWeather
 * @param array $forecast
 * @param array $preferences
 * @param int $minTemp
 * @param int $maxTemp
 * @param float $lat
 * @param float $lon
 * @return int
 */
function calculateMatchScore($currentWeather, $forecast, $preferences, $minTemp, $maxTemp, $lat, $lon)
{
    if (!$currentWeather) {
        return 0;
    }

    $temp = $currentWeather['temp'];
    $humidity = $currentWeather['humidity'];
    $wind = $currentWeather['wind_speed'];
    $condition = strtolower($currentWeather['condition']);
    $precip = $currentWeather['precipitation'] ?? 0;

    $climateType = getClimateType($lat, $lon, $temp, $humidity);
    $season = getCurrentSeason($lat);
    $comfortIndex = calculateComfortIndex($temp, $humidity, $wind, $precip);

    $score = 50;

    $tempScore = 0;
    if ($temp >= $minTemp && $temp <= $maxTemp) {
        $tempScore = 30;

        $rangeMidpoint = ($minTemp + $maxTemp) / 2;
        $tempDistance = abs($temp - $rangeMidpoint);
        $rangeWidth = $maxTemp - $minTemp;

        if ($rangeWidth > 0) {
            $positionBonus = 10 * (1 - ($tempDistance / ($rangeWidth / 2)));
            $tempScore += max(0, $positionBonus);
        }
    } else {
        $deviation = ($temp < $minTemp) ? ($minTemp - $temp) : ($temp - $maxTemp);
        $tempScore = max(-30, -3 * pow($deviation, 1.2)); // Exponential penalty
    }
    $score += $tempScore;
    $preferenceScore = 0;
    $totalPossibleScore = 0;
    $weights = PREFERENCE_WEIGHTS;

    foreach ($preferences as $pref) {
        $baseWeight = $weights[$pref] ?? 5;
        $seasonalFactor = SEASONAL_FACTORS[$season][$pref] ?? 1.0;
        $climateFactor = CLIMATE_ADJUSTMENTS[$climateType][$pref] ?? 1.0;
        $adjustedWeight = $baseWeight * $seasonalFactor * $climateFactor;
        $totalPossibleScore += $adjustedWeight;

        $matchScore = 0;

        switch ($pref) {
            case 'hot-dry':
                // Temperature: 28-40°C, Low humidity, No rain
                if ($temp >= 28 && $temp <= 40) {
                    $matchScore = $adjustedWeight;
                    if ($temp >= 30 && $temp <= 38) {
                        $matchScore *= 1.2; // Optimal range
                    }
                    if ($humidity < 50 && $precip < 0.1) {
                        $matchScore *= 1.3; // Perfect conditions
                    } else if ($humidity < 70 && $precip < 1.0) {
                        $matchScore *= 0.8; // Acceptable
                    } else {
                        $matchScore *= 0.4; // Not ideal
                    }
                } else if ($temp >= 25 && $temp <= 45) {
                    $matchScore = $adjustedWeight * 0.6; // Close range
                }
                break;

            case 'warm-sunny':
                // Temperature: 21-28°C, Low humidity, No rain, Sunny
                if ($temp >= 21 && $temp <= 28) {
                    $matchScore = $adjustedWeight;
                    if ($temp >= 23 && $temp <= 26) {
                        $matchScore *= 1.2; // Optimal range
                    }
                    if ((strpos($condition, 'clear') !== false || strpos($condition, 'sun') !== false)
                        && $humidity < 60 && $precip < 0.1
                    ) {
                        $matchScore *= 1.4; // Perfect sunny conditions
                    } else if ($precip < 1.0) {
                        $matchScore *= 0.8; // Acceptable
                    } else {
                        $matchScore *= 0.3; // Not ideal
                    }
                } else if ($temp >= 18 && $temp <= 32) {
                    $matchScore = $adjustedWeight * 0.7; // Close range
                }
                break;

            case 'cold-snowy':
                // Temperature: -10 to 5°C, High humidity, Snow
                if ($temp >= -10 && $temp <= 5) {
                    $matchScore = $adjustedWeight;
                    if ($temp >= -5 && $temp <= 2) {
                        $matchScore *= 1.2; // Optimal snow conditions
                    }
                    if (strpos($condition, 'snow') !== false && $humidity >= 70) {
                        $matchScore *= 1.4; // Perfect snowy conditions
                    } else if ($humidity >= 60) {
                        $matchScore *= 0.8; // Acceptable humidity
                    } else {
                        $matchScore *= 0.4; // Not ideal
                    }
                } else if ($temp >= -15 && $temp <= 10) {
                    $matchScore = $adjustedWeight * 0.6; // Close range
                }
                break;

            case 'mild-rainy':
                // Temperature: 15-25°C, High humidity, Rain
                if ($temp >= 15 && $temp <= 25) {
                    $matchScore = $adjustedWeight;
                    if ($temp >= 18 && $temp <= 22) {
                        $matchScore *= 1.2; // Optimal range
                    }
                    if ((strpos($condition, 'rain') !== false || $precip >= 1.0) && $humidity >= 70) {
                        $matchScore *= 1.3; // Perfect rainy conditions
                    } else if ($humidity >= 60) {
                        $matchScore *= 0.8; // Acceptable humidity
                    } else {
                        $matchScore *= 0.5; // Not ideal
                    }
                } else if ($temp >= 12 && $temp <= 28) {
                    $matchScore = $adjustedWeight * 0.7; // Close range
                }
                break;

            case 'cool-humid':
                // Temperature: 10-20°C, High humidity, Occasional rain
                if ($temp >= 10 && $temp <= 20) {
                    $matchScore = $adjustedWeight;
                    if ($temp >= 13 && $temp <= 17) {
                        $matchScore *= 1.2; // Optimal range
                    }
                    if ($humidity >= 70) {
                        $matchScore *= 1.2; // Good humidity
                        if ($precip >= 0.5 && $precip <= 3.0) {
                            $matchScore *= 1.1; // Light rain bonus
                        }
                    } else if ($humidity >= 60) {
                        $matchScore *= 0.8; // Acceptable humidity
                    } else {
                        $matchScore *= 0.5; // Too dry
                    }
                } else if ($temp >= 7 && $temp <= 23) {
                    $matchScore = $adjustedWeight * 0.7; // Close range
                }
                break;

            case 'balanced':
                // Temperature: 18-28°C, Moderate humidity, Occasional rain
                if ($temp >= 18 && $temp <= 28) {
                    $matchScore = $adjustedWeight;
                    if ($temp >= 20 && $temp <= 26) {
                        $matchScore *= 1.2; // Optimal range
                    }
                    if ($humidity >= 50 && $humidity <= 75) {
                        $matchScore *= 1.2; // Good humidity range
                    } else if ($humidity >= 40 && $humidity <= 85) {
                        $matchScore *= 0.9; // Acceptable humidity
                    } else {
                        $matchScore *= 0.6; // Outside ideal humidity
                    }
                    // Light wind is a bonus
                    if ($wind >= 5 && $wind <= 20) {
                        $matchScore *= 1.1;
                    }
                    // Light rain is acceptable
                    if ($precip <= 2.0) {
                        $matchScore *= 1.05;
                    }
                } else if ($temp >= 15 && $temp <= 32) {
                    $matchScore = $adjustedWeight * 0.8; // Close range
                }
                break;
        }

        $preferenceScore += $matchScore;
    }

    if ($totalPossibleScore > 0) {
        $normalizedPreferenceScore = min(40, ($preferenceScore / $totalPossibleScore) * 40);
        $score += $normalizedPreferenceScore;
    } else {
        // If no preferences, use comfort index as a factor
        $score += ($comfortIndex / 100) * 20;
    }

    // --- Forecast Consistency Score ---
    $forecastConsistencyScore = 0;
    $tempConsistencyPenalty = 0;
    $conditionConsistencyPenalty = 0;
    $lastDayTemp = $temp;
    $lastDayCondition = $condition;
    $consistentDays = 0;

    foreach ($forecast as $day) {
        // Check temperature consistency
        if (abs($day['temp_max'] - $lastDayTemp) > 8) {
            $tempConsistencyPenalty += 3;
        }
        $lastDayTemp = $day['temp_max'];

        // Check condition consistency
        if ($day['condition'] !== $lastDayCondition) {
            $conditionConsistencyPenalty += 2;
        } else {
            $consistentDays++;
        }
        $lastDayCondition = $day['condition'];
    }

    $forecastConsistencyScore = 10 - $tempConsistencyPenalty - $conditionConsistencyPenalty;
    $forecastConsistencyScore += $consistentDays; // Bonus for consecutive similar days
    $score += max(-10, $forecastConsistencyScore);


    // --- Final Penalty Adjustments ---
    if ($wind > STRONG_WIND_KMH) $score -= 15;
    if ($precip > HEAVY_RAIN_MM_PER_HOUR * 2) $score -= 10;
    if ($humidity > HIGH_HUMIDITY) $score -= 5;
    if ($temp > EXTREME_HOT_TEMP_C) $score -= 20;
    if ($temp < EXTREME_COLD_TEMP_C) $score -= 20;


    return max(0, min(100, (int)round($score)));
}
