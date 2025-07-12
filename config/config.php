<?php
/**
 * UCP Configuration File
 * Main configuration settings for the User Control Panel
 */

// Prevent direct access
if (!defined('UCP_ACCESS')) {
    die('Access denied');
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gameserver');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', '');

// reCAPTCHA Configuration
define('RECAPTCHA_SITE_KEY', '6LfYour_reCAPTCHA_Site_Key_Here');
define('RECAPTCHA_SECRET_KEY', '6LfYour_reCAPTCHA_Secret_Key_Here');
define('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify');
define('RECAPTCHA_MIN_SCORE', 0.5);

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@yourserver.com');
define('SMTP_FROM_NAME', 'Game Server');

// Security Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Site Configuration
define('SITE_NAME', 'Game Server');
define('SITE_URL', 'https://yourserver.com');
define('SITE_DESCRIPTION', 'Official Game Server User Control Panel');
define('SITE_KEYWORDS', 'game, server, mmo, rpg, online');
define('SITE_LANGUAGE', 'de');
define('SITE_TIMEZONE', 'Europe/Berlin');

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif');
define('UPLOAD_PATH', 'uploads/');

// Game Configuration
define('MAX_CHARACTERS_PER_ACCOUNT', 5);
define('CHARACTER_NAME_MIN_LENGTH', 3);
define('CHARACTER_NAME_MAX_LENGTH', 20);
define('CHARACTER_CLASSES', 'Warrior,Mage,Archer,Priest,Rogue,Paladin');
define('STARTING_LEVEL', 1);
define('MAX_LEVEL', 100);

// Language Configuration
define('AVAILABLE_LANGUAGES', 'de,en,br,cn,vm,ru');
define('DEFAULT_LANGUAGE', 'de');

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PREFIX', 'ucp_');

// Logging Configuration
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE', 'logs/ucp.log');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_ROTATE', true);

// API Configuration
define('API_RATE_LIMIT', 60); // requests per minute
define('API_RATE_LIMIT_WINDOW', 60); // seconds
define('API_TIMEOUT', 30); // seconds

// Development Configuration
define('DEBUG_MODE', false);
define('SHOW_ERRORS', false);
define('ERROR_REPORTING', E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Server Configuration
define('SERVER_HOST', 'localhost');
define('SERVER_PORT', 5555);
define('SERVER_STATUS_CHECK_INTERVAL', 60); // seconds

// Social Media Configuration
define('SOCIAL_FACEBOOK', 'https://facebook.com/yourserver');
define('SOCIAL_TWITTER', 'https://twitter.com/yourserver');
define('SOCIAL_DISCORD', 'https://discord.gg/yourserver');
define('SOCIAL_YOUTUBE', 'https://youtube.com/yourserver');

// Maintenance Mode
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'System is under maintenance. Please try again later.');
define('MAINTENANCE_ALLOWED_IPS', '127.0.0.1,::1');

// Feature Flags
define('FEATURE_REGISTRATION', true);
define('FEATURE_CHARACTER_CREATION', true);
define('FEATURE_PASSWORD_RESET', true);
define('FEATURE_EMAIL_VERIFICATION', true);
define('FEATURE_TWO_FACTOR_AUTH', false);
define('FEATURE_SOCIAL_LOGIN', false);
define('FEATURE_NEWSLETTER', true);

// Game Server Integration
define('GAME_SERVER_API_URL', 'http://localhost:8080/api');
define('GAME_SERVER_API_KEY', 'your-api-key-here');
define('GAME_SERVER_TIMEOUT', 10); // seconds

// Backup Configuration
define('BACKUP_ENABLED', true);
define('BACKUP_SCHEDULE', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION', 30); // days
define('BACKUP_PATH', 'backups/');

// Performance Configuration
define('GZIP_COMPRESSION', true);
define('MINIFY_HTML', true);
define('MINIFY_CSS', true);
define('MINIFY_JS', true);
define('BROWSER_CACHE_TTL', 86400); // 24 hours

// Additional Configuration Array
$config = [
    'database' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => DB_CHARSET,
        'prefix' => DB_PREFIX,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ]
    ],
    
    'security' => [
        'password_hash_algo' => PASSWORD_ARGON2ID,
        'password_hash_options' => [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ],
        'csrf_token_name' => 'csrf_token',
        'csrf_token_length' => 32,
        'session_name' => 'UCP_SESSION',
        'session_cookie_lifetime' => 0,
        'session_cookie_path' => '/',
        'session_cookie_domain' => '',
        'session_cookie_secure' => false,
        'session_cookie_httponly' => true,
        'session_cookie_samesite' => 'Lax'
    ],
    
    'email' => [
        'smtp_host' => SMTP_HOST,
        'smtp_port' => SMTP_PORT,
        'smtp_username' => SMTP_USERNAME,
        'smtp_password' => SMTP_PASSWORD,
        'smtp_encryption' => 'tls',
        'from_email' => SMTP_FROM_EMAIL,
        'from_name' => SMTP_FROM_NAME,
        'reply_to' => SMTP_FROM_EMAIL,
        'templates_path' => 'templates/email/',
        'verification_subject' => 'Verify your email address',
        'password_reset_subject' => 'Password reset request',
        'welcome_subject' => 'Welcome to ' . SITE_NAME
    ],
    
    'recaptcha' => [
        'site_key' => RECAPTCHA_SITE_KEY,
        'secret_key' => RECAPTCHA_SECRET_KEY,
        'verify_url' => RECAPTCHA_VERIFY_URL,
        'min_score' => RECAPTCHA_MIN_SCORE,
        'timeout' => 10
    ],
    
    'validation' => [
        'username' => [
            'min_length' => 3,
            'max_length' => 20,
            'pattern' => '/^[a-zA-Z0-9_]+$/',
            'reserved_names' => ['admin', 'root', 'system', 'null', 'undefined']
        ],
        'password' => [
            'min_length' => PASSWORD_MIN_LENGTH,
            'require_special' => PASSWORD_REQUIRE_SPECIAL,
            'require_numbers' => PASSWORD_REQUIRE_NUMBERS,
            'require_uppercase' => PASSWORD_REQUIRE_UPPERCASE,
            'require_lowercase' => PASSWORD_REQUIRE_LOWERCASE,
            'special_chars' => '!@#$%^&*(),.?":{}|<>'
        ],
        'email' => [
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'disposable_domains' => ['tempmail.com', '10minutemail.com', 'guerrillamail.com']
        ]
    ],
    
    'character' => [
        'max_per_account' => MAX_CHARACTERS_PER_ACCOUNT,
        'name_min_length' => CHARACTER_NAME_MIN_LENGTH,
        'name_max_length' => CHARACTER_NAME_MAX_LENGTH,
        'name_pattern' => '/^[a-zA-Z0-9_]+$/',
        'classes' => explode(',', CHARACTER_CLASSES),
        'starting_level' => STARTING_LEVEL,
        'max_level' => MAX_LEVEL,
        'starting_location' => 'starter_town',
        'starting_money' => 1000
    ],
    
    'features' => [
        'registration' => FEATURE_REGISTRATION,
        'character_creation' => FEATURE_CHARACTER_CREATION,
        'password_reset' => FEATURE_PASSWORD_RESET,
        'email_verification' => FEATURE_EMAIL_VERIFICATION,
        'two_factor_auth' => FEATURE_TWO_FACTOR_AUTH,
        'social_login' => FEATURE_SOCIAL_LOGIN,
        'newsletter' => FEATURE_NEWSLETTER
    ],
    
    'social' => [
        'facebook' => SOCIAL_FACEBOOK,
        'twitter' => SOCIAL_TWITTER,
        'discord' => SOCIAL_DISCORD,
        'youtube' => SOCIAL_YOUTUBE
    ],
    
    'upload' => [
        'max_size' => MAX_UPLOAD_SIZE,
        'allowed_types' => explode(',', ALLOWED_FILE_TYPES),
        'path' => UPLOAD_PATH,
        'create_thumbnails' => true,
        'thumbnail_size' => 150,
        'image_quality' => 85
    ],
    
    'cache' => [
        'enabled' => CACHE_ENABLED,
        'lifetime' => CACHE_LIFETIME,
        'prefix' => CACHE_PREFIX,
        'driver' => 'file', // file, memcached, redis
        'path' => 'cache/'
    ],
    
    'logging' => [
        'enabled' => LOG_ENABLED,
        'level' => LOG_LEVEL,
        'file' => LOG_FILE,
        'max_size' => LOG_MAX_SIZE,
        'rotate' => LOG_ROTATE,
        'format' => '[%datetime%] %level_name%: %message% %context%'
    ],
    
    'api' => [
        'rate_limit' => API_RATE_LIMIT,
        'rate_limit_window' => API_RATE_LIMIT_WINDOW,
        'timeout' => API_TIMEOUT,
        'version' => '1.0',
        'cors_enabled' => true,
        'cors_origins' => ['*'],
        'cors_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'cors_headers' => ['Content-Type', 'Authorization', 'X-Requested-With']
    ],
    
    'performance' => [
        'gzip_compression' => GZIP_COMPRESSION,
        'minify_html' => MINIFY_HTML,
        'minify_css' => MINIFY_CSS,
        'minify_js' => MINIFY_JS,
        'browser_cache_ttl' => BROWSER_CACHE_TTL,
        'enable_opcache' => true,
        'enable_query_cache' => true
    ]
];

// Environment-specific configuration
if (DEBUG_MODE) {
    $config['logging']['level'] = 'DEBUG';
    $config['performance']['minify_html'] = false;
    $config['performance']['minify_css'] = false;
    $config['performance']['minify_js'] = false;
    $config['cache']['enabled'] = false;
}

// Set error reporting
if (SHOW_ERRORS) {
    ini_set('display_errors', 1);
    error_reporting(ERROR_REPORTING);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Set timezone
date_default_timezone_set(SITE_TIMEZONE);

// Set memory limit
ini_set('memory_limit', '256M');

// Set maximum execution time
set_time_limit(30);

// Function to get configuration value
function getConfig($key, $default = null) {
    global $config;
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}

// Function to check if feature is enabled
function isFeatureEnabled($feature) {
    return getConfig("features.{$feature}", false);
}

// Function to get localized configuration
function getLocalizedConfig($key, $lang = null) {
    if ($lang === null) {
        $lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
    }
    
    $localizedKey = "{$key}.{$lang}";
    $defaultValue = getConfig($key);
    
    return getConfig($localizedKey, $defaultValue);
}

// Auto-loader for classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../php/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include additional configuration files
$additionalConfigs = [
    'database_tables.php',
    'email_templates.php',
    'character_data.php'
];

foreach ($additionalConfigs as $configFile) {
    $configPath = __DIR__ . '/' . $configFile;
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}
?> 