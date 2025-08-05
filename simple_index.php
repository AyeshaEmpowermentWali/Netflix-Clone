<?php
// Simplified version of index.php for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<!DOCTYPE html>";
echo "<html><head><title>Netflix Clone - Simple Test</title></head><body>";
echo "<h1>Netflix Clone - Test Page</h1>";

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'db6gous4y7xmrw';
    $username = 'ugrj543f7lree';
    $password = 'cgmq43woifkoari';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>Database connected successfully!</p>";
    
    // Try to get some content
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM content");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "<p>Content count: " . ($result['count'] ?? 0) . "</p>";
    
    echo "<p><a href='index.php'>Try Full Homepage</a></p>";
    echo "<p><a href='test_connection.php'>Database Test</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration.</p>";
}

echo "</body></html>";
?>
