<?php
/**
 * Environment Variable Loader
 * 
 * This file provides functions to load environment variables from a .env file
 * and make them accessible to the application.
 */

// Define the path to the .env file
$envFilePath = __DIR__ . '/../.env';

// Load environment variables from .env file
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) {
        // Create a default .env file if it doesn't exist
        $defaultEnvContent = "# WeatherVoyager Environment Variables
RECAPTCHA_SITE_KEY=6LcXXXXXXXXXXXXXXXXXXXXXXXX
RECAPTCHA_SECRET_KEY=6LcXXXXXXXXXXXXXXXXXXXXXXXX

# MongoDB Connection
MONGODB_URI=mongodb://localhost:27017

# Email Settings
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@weathervoyager.com
MAIL_PASSWORD=your_password_here
MAIL_FROM_ADDRESS=noreply@weathervoyager.com
MAIL_FROM_NAME=WeatherVoyager
MAIL_ENCRYPTION=tls

# Weather API Keys
OPENWEATHER_API_KEY=your_openweather_api_key
WEATHERAPI_KEY=your_weatherapi_key
";
        file_put_contents($filePath, $defaultEnvContent);
        error_log("Created default .env file at {$filePath}");
    }
    
    // Read the .env file line by line
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVars = [];
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse variable assignments
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Store in our environment array
            $envVars[$name] = $value;
        }
    }
    
    return $envVars;
}

// Load environment variables
$envVars = loadEnvFile($envFilePath);

/**
 * Get an environment variable value
 *
 * @param string $key The environment variable name
 * @param mixed $default Default value if the environment variable is not set
 * @return mixed The environment variable value or default if not found
 */
function getEnvVar($key, $default = null) {
    global $envVars;
    
    // First check our loaded .env file
    if (isset($envVars[$key])) {
        return $envVars[$key];
    }
    
    // Then check actual environment variables
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    // Finally check $_ENV and $_SERVER
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    
    // Return default value if not found
    return $default;
}

// Setup path constants
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('CONFIG_PATH', BASE_PATH . '/config');
define('LOGS_PATH', BASE_PATH . '/logs');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Create necessary directories with proper permissions
$directories = [
    LOGS_PATH,
    LOGS_PATH . '/mail',
    STORAGE_PATH,
    STORAGE_PATH . '/rate_limits',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/tmp'
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        // Create directory with limited permissions
        @mkdir($directory, 0750, true);
        
        // Create .htaccess to deny web access if it doesn't exist
        $htaccess = $directory . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Require all denied\nOptions -Indexes");
        }
    }
}

// Set environment based on env var or default to development
define('APP_ENV', getEnvVar('APP_ENV', 'development'));
define('APP_DEBUG', filter_var(getEnvVar('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN));

// Configure error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// Set memory limit if needed
ini_set('memory_limit', '256M');
?>