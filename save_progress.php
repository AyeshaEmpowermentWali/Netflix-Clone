<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$content_id = (int)($input['content_id'] ?? 0);
$progress_time = (int)($input['progress_time'] ?? 0);
$total_time = (int)($input['total_time'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$content_id || $progress_time < 0 || $total_time <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO watch_progress (user_id, content_id, progress_time, total_time) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        progress_time = VALUES(progress_time), 
        total_time = VALUES(total_time),
        last_watched = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([$user_id, $content_id, $progress_time, $total_time]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error saving progress: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
