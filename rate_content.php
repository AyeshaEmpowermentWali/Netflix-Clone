<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$content_id = (int)($input['content_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$content_id || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO user_ratings (user_id, content_id, rating) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE rating = VALUES(rating)
    ");
    
    $stmt->execute([$user_id, $content_id, $rating]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error saving rating: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
