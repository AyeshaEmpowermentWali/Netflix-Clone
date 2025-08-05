<?php
// Simple database test with corrected credentials
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Database configuration - CORRECTED
$host = 'localhost';
$dbname = 'db6gous4y7xmrw';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko'; // CORRECTED PASSWORD

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    echo "<p>Host: $host</p>";
    echo "<p>Database: $dbname</p>";
    echo "<p>Username: $username</p>";
    
    // Test if tables exist
    $tables = ['users', 'content', 'watchlist', 'watch_progress', 'user_ratings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $count_stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $count_stmt->fetchColumn();
                echo "<p style='color: green;'>✅ Table '$table' exists with $count records</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Table '$table' does not exist</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error checking table '$table': " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<br><a href='index.php'>Go to Homepage</a>";
?>
