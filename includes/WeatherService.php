<?php
/**
 * WeatherService Class
 * 
 * Handles API calls to weather services and processes the data
 */
class WeatherService {
    // Cache storage
    private $cache = [];
    private $cacheFile = '';
    private $cacheExpiry = 3600; // Cache validity in seconds (1 hour)

    public function __construct() {
        // Set up cache directory and file
        $cacheDir = dirname(__DIR__) . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->cacheFile = $cacheDir . '/weather_cache.json';
        $this->loadCache();
    }

    /**
     * Load cache data from file
     */
    private function loadCache() {
        if (file_exists($this->cacheFile)) {
            $content = file_get_contents($this->cacheFile);
            $data = json_decode($content, true);
            if (is_array($data)) {
                $this->cache = $data;
            }
        }
    }

    /**
     * Save cache data to file
     */
    private function saveCache() {
        file_put_contents($this->cacheFile, json_encode($this->cache));
    }

    /**
     * Check if a cached item exists and is valid
     * 
     * @param string $key Cache key
     * @return bool True if valid cache exists
     */
    private function hasCachedItem($key) {
        return isset($this->cache[$key]) && 
               isset($this->cache[$key]['time']) && 
               time() - $this->cache[$key]['time'] < $this->cacheExpiry;
    }

    /**
     * Get a cached item
     * 
     * @param string $key Cache key
     * @return mixed The cached data or null
     */
    private function getCachedItem($key) {
        return $this->hasCachedItem($key) ? $this->cache[$key]['data'] : null;
    }

    /**
     * Store an item in cache
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     */
    private function setCachedItem($key, $data) {
        $this->cache[$key] = [
            'time' => time(),
            'data' => $data
        ];
        // Save cache periodically (not on every set to reduce disk writes)
        if (rand(1, 10) === 1) {
            $this->saveCache();
        }
    }

    /**
     * Fetch current weather data from OpenWeather API
     * 
     * @param float $lat Latitude of the location
     * @param float $lon Longitude of the location
     * @param bool $includeUvIndex Whether to include UV index (requires extra API call)
     * @return array|null Weather data or null if failed
     */
    public function getCurrentWeather($lat, $lon, $includeUvIndex = false) {
        // Generate cache key for this request
        $cacheKey = "current_weather_{$lat}_{$lon}";
        
        // Check cache first
        if ($this->hasCachedItem($cacheKey)) {
            return $this->getCachedItem($cacheKey);
        }
        
        $url = OPENWEATHER_API_URL . "/weather?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;
        
        $response = $this->makeApiRequest($url);
        
        if (!$response) {
            return null;
        }
        
        // Extract and format the relevant data
        $weatherData = [
            'temp' => round($response['main']['temp']),
            'condition' => $response['weather'][0]['main'],
            'description' => $response['weather'][0]['description'],
            'humidity' => $response['main']['humidity'],
            'wind_speed' => round($response['wind']['speed'] * 3.6, 1), // Convert m/s to km/h
            'uv_index' => $includeUvIndex ? $this->getUVIndex($lat, $lon) : 2, // Default UV index if not requested
            'icon' => $response['weather'][0]['icon']
        ];
        
        // Save to cache
        $this->setCachedItem($cacheKey, $weatherData);
        
        return $weatherData;
    }
    
    /**
     * Fetch forecast data from Open-Meteo API
     * 
     * @param float $lat Latitude of the location
     * @param float $lon Longitude of the location
     * @return array|null Forecast data or null if failed
     */
    public function getForecast($lat, $lon) {
        // Generate cache key for this request
        $cacheKey = "forecast_{$lat}_{$lon}";
        
        // Check cache first
        if ($this->hasCachedItem($cacheKey)) {
            return $this->getCachedItem($cacheKey);
        }
        
        $url = OPENMETEO_API_URL . "/forecast?latitude={$lat}&longitude={$lon}&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=auto&forecast_days=4";
        
        $response = $this->makeApiRequest($url);
        
        if (!$response || !isset($response['daily'])) {
            return null;
        }
        
        $forecast = [];
        
        // Skip today (index 0) and get the next 3 days
        for ($i = 1; $i <= 3; $i++) {
            if (isset($response['daily']['time'][$i])) {
                $forecast[] = [
                    'date' => $response['daily']['time'][$i],
                    'temp_max' => round($response['daily']['temperature_2m_max'][$i]),
                    'temp_min' => round($response['daily']['temperature_2m_min'][$i]),
                    'precipitation' => $response['daily']['precipitation_sum'][$i],
                    'condition' => $this->getWeatherConditionFromCode($response['daily']['weathercode'][$i])
                ];
            }
        }
        
        // Save to cache
        $this->setCachedItem($cacheKey, $forecast);
        
        return $forecast;
    }
    
    /**
     * Get UV Index from OpenWeather API
     * 
     * @param float $lat Latitude of the location
     * @param float $lon Longitude of the location
     * @return int UV Index value
     */
    private function getUVIndex($lat, $lon) {
        // Generate cache key for this request
        $cacheKey = "uv_index_{$lat}_{$lon}";
        
        // Check cache first
        if ($this->hasCachedItem($cacheKey)) {
            return $this->getCachedItem($cacheKey);
        }
        
        $url = OPENWEATHER_API_URL . "/uvi?lat={$lat}&lon={$lon}&appid=" . OPENWEATHER_API_KEY;
        
        $response = $this->makeApiRequest($url);
        $uvIndex = $response && isset($response['value']) ? round($response['value']) : 0;
        
        // Save to cache
        $this->setCachedItem($cacheKey, $uvIndex);
        
        return $uvIndex;
    }
    
    /**
     * Suggest activities based on weather conditions
     * 
     * @param array $weatherData Weather data
     * @return array List of suggested activities
     */
    public function suggestActivities($weatherData) {
        $activities = [];
        $condition = strtolower($weatherData['condition']);
        $temp = $weatherData['temp'];
        
        // Good weather activities
        if (($condition === 'clear' || $condition === 'sunny' || strpos($condition, 'cloud') !== false) 
            && $weatherData['precipitation'] < 1 && $temp > 15) {
            $activities[] = 'Outdoor sightseeing';
            
            if ($temp > 22) {
                $activities[] = 'Beach visit or water activities';
            }
            
            $activities[] = 'Walking tours and hiking';
            $activities[] = 'Outdoor dining';
        }
        
        // Bad weather activities
        if ($condition === 'rain' || $condition === 'thunderstorm' || $weatherData['precipitation'] > 3) {
            $activities[] = 'Museum visits';
            $activities[] = 'Indoor shopping';
            $activities[] = 'Local food tour';
            $activities[] = 'Spa and wellness';
        }
        
        // Temperature-based activities
        if ($temp < 10) {
            $activities[] = 'Indoor cultural experiences';
            $activities[] = 'Coffee shop hopping';
        } else if ($temp > 28) {
            $activities[] = 'Water parks';
            $activities[] = 'Visit air-conditioned attractions';
        }
        
        // Add some default activities if no specific recommendations
        if (empty($activities)) {
            $activities[] = 'City sightseeing';
            $activities[] = 'Local cuisine exploration';
            $activities[] = 'Cultural experiences';
        }
        
        // Return at most 4 activities
        shuffle($activities);
        return array_slice($activities, 0, 4);
    }
    
    /**
     * Convert Open-Meteo weather code to human-readable condition
     * Based on WMO codes: https://www.nodc.noaa.gov/archive/arc0021/0002199/1.1/data/0-data/HTML/WMO-CODE/WMO4677.HTM
     * 
     * @param int $code Weather code
     * @return string Weather condition
     */
    private function getWeatherConditionFromCode($code) {
        $conditions = [
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45 => 'Fog',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            61 => 'Light rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            71 => 'Light snow',
            73 => 'Moderate snow',
            75 => 'Heavy snow',
            80 => 'Light rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with hail',
            99 => 'Thunderstorm with heavy hail'
        ];
        
        return $conditions[$code] ?? 'Unknown';
    }
    
    /**
     * Make API request and return parsed JSON response
     * 
     * @param string $url API URL
     * @return array|null Response data or null if failed
     */
    private function makeApiRequest($url) {
        try {
            $curl = curl_init();
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10, // Reduced timeout for individual requests
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_CONNECTTIMEOUT => 5, // Added connection timeout
            ]);
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);
            
            if ($err) {
                error_log("cURL Error: " . $err);
                return null;
            }
            
            return json_decode($response, true);
        } catch (Exception $e) {
            error_log("API Request Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate travel advice based on weather conditions
     * 
     * @param array $currentWeather Current weather data
     * @param array $forecast Forecast data
     * @return string Travel advice
     */
    public function generateTravelAdvice($currentWeather, $forecast) {
        $condition = strtolower($currentWeather['condition']);
        $temp = $currentWeather['temp'];
        $humidity = $currentWeather['humidity'];
        $windSpeed = $currentWeather['wind_speed'];
        
        $advice = "";
        
        // Temperature-based advice
        if ($temp > 30) {
            $advice .= "Pack light, breathable clothing and stay hydrated. Consider accommodations with air conditioning. ";
            $advice .= "Plan outdoor activities for early morning or evening to avoid peak heat. ";
        } else if ($temp > 22) {
            $advice .= "Weather is warm and pleasant. Perfect for most outdoor activities throughout the day. ";
        } else if ($temp > 15) {
            $advice .= "Pack layers as temperatures are moderate. Good for sightseeing and moderate outdoor activities. ";
        } else if ($temp > 5) {
            $advice .= "Bring warm clothing. Weather is cool, but still good for brief outdoor activities. ";
        } else {
            $advice .= "Pack winter clothing and prepare for cold temperatures. Check for indoor activities. ";
        }
        
        // Conditions-based advice
        if ($condition === 'rain' || $condition === 'drizzle' || strpos($condition, 'rain') !== false) {
            $advice .= "Don't forget an umbrella or raincoat. Consider waterproof footwear. ";
        } else if ($condition === 'clear' || $condition === 'sunny' || strpos($condition, 'clear') !== false) {
            $advice .= "Bring sunscreen and sunglasses as UV exposure will be high. ";
        } else if (strpos($condition, 'cloud') !== false) {
            $advice .= "Weather is partly cloudy, but should still be pleasant for exploring. ";
        }
        
        // Humidity advice
        if ($humidity > 80) {
            $advice .= "Very humid conditions. Bring moisture-wicking clothing and prepare for potential discomfort. ";
        }
        
        // Wind advice
        if ($windSpeed > 25) {
            $advice .= "Expect windy conditions. Secure loose items and prepare for potential outdoor activity disruptions. ";
        }
        
        // Check forecast for changes
        $weatherChanging = false;
        $rainInForecast = false;
        $coldInForecast = false;
        
        foreach ($forecast as $day) {
            if (strpos(strtolower($day['condition']), 'rain') !== false || 
                strpos(strtolower($day['condition']), 'drizzle') !== false ||
                $day['precipitation'] > 1) {
                $rainInForecast = true;
            }
            
            if ($day['temp_min'] < 10) {
                $coldInForecast = true;
            }
        }
        
        // Add forecast advice
        if ($rainInForecast && strpos($condition, 'rain') === false) {
            $advice .= "Pack an umbrella as rain is expected in the coming days. ";
        }
        
        if ($coldInForecast && $temp > 15) {
            $advice .= "Weather will get cooler during your stay. Bring some warmer clothing. ";
        }
        
        return trim($advice);
    }
    
    /**
     * Destructor to save cache when object is destroyed
     */
    public function __destruct() {
        $this->saveCache();
    }
}