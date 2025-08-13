<?php
/**
 * Database Configuration
 * 0S-CARE - Cancer Patient Care Management System
 */

// Environment detection
$is_production = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost');

// Database Configuration
if ($is_production) {
    // Production Database Settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'outsrglr_mom');
    define('DB_USER', 'outsrglr_mom');
    define('DB_PASS', 'born#1852Niptuck');
} else {
    // Development Database Settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'os_care_dev');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Application Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_FILE_SIZE', 10485760); // 10MB
define('LOG_LEVEL', 'DEBUG');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Security Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.use_only_cookies', 1);

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please contact administrator.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

/**
 * CSRF Token Functions
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME] = $token;
    return $token;
}

function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION[CSRF_TOKEN_NAME]) && 
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Logging Function
 */
function logError($message, $level = 'ERROR') {
    $logFile = __DIR__ . '/../logs/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout check
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION['last_activity'] = time();
?>