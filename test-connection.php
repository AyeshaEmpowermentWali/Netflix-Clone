<?php
// Simple test file to check if PHP and database are working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Test Page</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
try {
    $host = 'localhost';
    $dbname = 'db6gous4y7xmrw';
    $username = 'ugrj543f7lree';
    $password = 'cgmq43woifkoari';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = ['users', 'content', 'watchlist', 'watch_progress', 'user_ratings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Table '$table' exists with $count records</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table '$table' error: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>
