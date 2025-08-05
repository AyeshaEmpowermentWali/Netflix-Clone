<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration - CORRECTED CREDENTIALS
$host = 'localhost';
$dbname = 'db6gous4y7xmrw';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko'; // Fixed password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    // Log error instead of displaying it
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Helper functions - RENAMED to avoid conflicts
function sanitize_input($data) {
    if ($data === null) return '';
    return htmlspecialchars(strip_tags(trim($data)));
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_logged_user() { // RENAMED from get_current_user
    global $pdo;
    if (!is_user_logged_in()) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

function redirect_to($url) { // RENAMED to avoid conflicts
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href = '$url';</script>";
        exit();
    }
}

// Start session with error handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
