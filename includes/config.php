<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new \Exception(".env file not found at {$path}");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!array_key_exists($key, $_ENV)) {
            putenv(sprintf('%s=%s', $key, $value));
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load .env file
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// API Keys
define('OPENWEATHER_API_KEY', $_ENV['OPENWEATHER_API_KEY'] ?? '');
define('OPENMETEO_API_KEY', $_ENV['OPENMETEO_API_KEY'] ?? '');

// Validate API keys
if (empty(OPENWEATHER_API_KEY)) {
    error_log('OpenWeather API key is missing. Please check your .env file.');
}

// Base URLs for APIs
define('OPENWEATHER_API_URL', 'https://api.openweathermap.org/data/2.5');
define('OPENMETEO_API_URL', 'https://api.open-meteo.com/v1');

// List of cities for recommendations
// Format: [city, country, latitude, longitude]
define('CITIES', [
    // Europe
    ['London', 'UK', 51.5074, -0.1278],
    ['Paris', 'France', 48.8566, 2.3522],
    ['Rome', 'Italy', 41.9028, 12.4964],
    ['Barcelona', 'Spain', 41.3851, 2.1734],
    ['Berlin', 'Germany', 52.5200, 13.4050],
    ['Amsterdam', 'Netherlands', 52.3676, 4.9041],
    ['Prague', 'Czech Republic', 50.0755, 14.4378],
    ['Vienna', 'Austria', 48.2082, 16.3738],
    ['Budapest', 'Hungary', 47.4979, 19.0402],
    ['Lisbon', 'Portugal', 38.7223, -9.1393],
    ['Madrid', 'Spain', 40.4168, -3.7038],
    ['Copenhagen', 'Denmark', 55.6761, 12.5683],
    ['Stockholm', 'Sweden', 59.3293, 18.0686],
    ['Oslo', 'Norway', 59.9139, 10.7522],
    ['Athens', 'Greece', 37.9838, 23.7275],
    ['Santorini', 'Greece', 36.3932, 25.4615],
    ['Reykjavik', 'Iceland', 64.1466, -21.9426],
    ['Zurich', 'Switzerland', 47.3769, 8.5417],
    ['Dublin', 'Ireland', 53.3498, -6.2603],
    ['Edinburgh', 'UK', 55.9533, -3.1883],
    ['Florence', 'Italy', 43.7696, 11.2558],
    ['Venice', 'Italy', 45.4408, 12.3155],
    ['Milan', 'Italy', 45.4642, 9.1900],
    ['Brussels', 'Belgium', 50.8503, 4.3517],
    ['Krakow', 'Poland', 50.0647, 19.9450],
    ['Helsinki', 'Finland', 60.1699, 24.9384],
    ['Porto', 'Portugal', 41.1579, -8.6291],
    ['Seville', 'Spain', 37.3891, -5.9845],
    ['Valencia', 'Spain', 39.4699, -0.3763],
    ['Nice', 'France', 43.7102, 7.2620],
    
    // North America
    ['New York', 'USA', 40.7128, -74.0060],
    ['San Francisco', 'USA', 37.7749, -122.4194],
    ['Toronto', 'Canada', 43.6532, -79.3832],
    ['Honolulu', 'USA', 21.3069, -157.8583],
    ['Los Angeles', 'USA', 34.0522, -118.2437],
    ['Chicago', 'USA', 41.8781, -87.6298],
    ['Miami', 'USA', 25.7617, -80.1918],
    ['Vancouver', 'Canada', 49.2827, -123.1207],
    ['Las Vegas', 'USA', 36.1699, -115.1398],
    ['Seattle', 'USA', 47.6062, -122.3321],
    ['Montreal', 'Canada', 45.5017, -73.5673],
    ['Denver', 'USA', 39.7392, -104.9903],
    ['Boston', 'USA', 42.3601, -71.0589],
    ['Washington DC', 'USA', 38.9072, -77.0369],
    ['Austin', 'USA', 30.2672, -97.7431],
    ['New Orleans', 'USA', 29.9511, -90.0715],
    ['San Diego', 'USA', 32.7157, -117.1611],
    ['Portland', 'USA', 45.5051, -122.6750],
    ['Quebec City', 'Canada', 46.8139, -71.2080],
    ['Cancun', 'Mexico', 21.1619, -86.8515],
    ['Mexico City', 'Mexico', 19.4326, -99.1332],
    ['Havana', 'Cuba', 23.1136, -82.3666],
    ['San Juan', 'Puerto Rico', 18.4655, -66.1057],
    ['Nassau', 'Bahamas', 25.0343, -77.3963],
    
    // Asia
    ['Tokyo', 'Japan', 35.6762, 139.6503],
    ['Singapore', 'Singapore', 1.3521, 103.8198],
    ['Bangkok', 'Thailand', 13.7563, 100.5018],
    ['Dubai', 'UAE', 25.2048, 55.2708],
    ['Bali', 'Indonesia', -8.4095, 115.1889],
    ['Hong Kong', 'China', 22.3193, 114.1694],
    ['Seoul', 'South Korea', 37.5665, 126.9780],
    ['Beijing', 'China', 39.9042, 116.4074],
    ['Shanghai', 'China', 31.2304, 121.4737],
    ['Kyoto', 'Japan', 35.0116, 135.7681],
    ['Osaka', 'Japan', 34.6937, 135.5023],
    ['Taipei', 'Taiwan', 25.0330, 121.5654],
    ['Kuala Lumpur', 'Malaysia', 3.1390, 101.6869],
    ['Ho Chi Minh City', 'Vietnam', 10.8231, 106.6297],
    ['Hanoi', 'Vietnam', 21.0278, 105.8342],
    ['Mumbai', 'India', 19.0760, 72.8777],
    ['Delhi', 'India', 28.6139, 77.2090],
    ['Istanbul', 'Turkey', 41.0082, 28.9784],
    ['Jerusalem', 'Israel', 31.7683, 35.2137],
    ['Abu Dhabi', 'UAE', 24.4539, 54.3773],
    ['Phuket', 'Thailand', 7.9519, 98.3381],
    ['Maldives', 'Maldives', 3.2028, 73.2207],
    ['Chiang Mai', 'Thailand', 18.7883, 98.9853],
    ['Boracay', 'Philippines', 11.9674, 121.9248],
    ['Kathmandu', 'Nepal', 27.7172, 85.3240],
    
    // Australia & Oceania
    ['Sydney', 'Australia', -33.8688, 151.2093],
    ['Auckland', 'New Zealand', -36.8509, 174.7645],
    ['Melbourne', 'Australia', -37.8136, 144.9631],
    ['Brisbane', 'Australia', -27.4698, 153.0251],
    ['Perth', 'Australia', -31.9505, 115.8605],
    ['Queenstown', 'New Zealand', -45.0312, 168.6626],
    ['Wellington', 'New Zealand', -41.2865, 174.7762],
    ['Gold Coast', 'Australia', -28.0167, 153.4000],
    ['Cairns', 'Australia', -16.9186, 145.7781],
    ['Christchurch', 'New Zealand', -43.5320, 172.6362],
    ['Fiji', 'Fiji', -17.7134, 178.0650],
    ['Tahiti', 'French Polynesia', -17.6509, -149.4260],
    ['Bora Bora', 'French Polynesia', -16.5004, -151.7415],
    
    // Africa
    ['Cape Town', 'South Africa', -33.9249, 18.4241],
    ['Marrakech', 'Morocco', 31.6295, -7.9811],
    ['Cairo', 'Egypt', 30.0444, 31.2357],
    ['Nairobi', 'Kenya', -1.2921, 36.8219],
    ['Zanzibar', 'Tanzania', -6.1659, 39.1888],
    ['Casablanca', 'Morocco', 33.5731, -7.5898],
    ['Johannesburg', 'South Africa', -26.2041, 28.0473],
    ['Luxor', 'Egypt', 25.6872, 32.6396],
    ['Victoria Falls', 'Zimbabwe', -17.9243, 25.8572],
    ['Seychelles', 'Seychelles', -4.6796, 55.4920],
    ['Tunis', 'Tunisia', 36.8065, 10.1815],
    ['Mauritius', 'Mauritius', -20.3484, 57.5522],
    ['Dar es Salaam', 'Tanzania', -6.7924, 39.2083],
    
    // South & Central America
    ['Rio de Janeiro', 'Brazil', -22.9068, -43.1729],
    ['Buenos Aires', 'Argentina', -34.6037, -58.3816],
    ['Lima', 'Peru', -12.0464, -77.0428],
    ['Cusco', 'Peru', -13.5320, -71.9675],
    ['Santiago', 'Chile', -33.4489, -70.6693],
    ['Cartagena', 'Colombia', 10.3910, -75.4794],
    ['Quito', 'Ecuador', -0.1807, -78.4678],
    ['San Jose', 'Costa Rica', 9.9281, -84.0907],
    ['Panama City', 'Panama', 8.9824, -79.5199],
    ['Bogota', 'Colombia', -4.7110, -74.0721],
    ['Montevideo', 'Uruguay', -34.9011, -56.1915],
    ['Sao Paulo', 'Brazil', -23.5505, -46.6333],
    ['La Paz', 'Bolivia', -16.4897, -68.1193],
    ['Galapagos Islands', 'Ecuador', -0.7452, -90.3600],
    ['Machu Picchu', 'Peru', -13.1631, -72.5450],
    ['Belize City', 'Belize', 17.4959, -88.1867],
    ['San Salvador', 'El Salvador', 13.6929, -89.2182],
    ['Antigua', 'Guatemala', 14.5586, -90.7295]
]);

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);