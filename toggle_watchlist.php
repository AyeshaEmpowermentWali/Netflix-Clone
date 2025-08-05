<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$content_id = (int)$input['content_id'];
$user_id = $_SESSION['user_id'];

if (!$content_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid content ID']);
    exit;
}

try {
    // Check if already in watchlist
    $stmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    $stmt->execute([$user_id, $content_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Remove from watchlist
        $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
        $stmt->execute([$user_id, $content_id]);
        echo json_encode(['success' => true, 'in_watchlist' => false]);
    } else {
        // Add to watchlist
        $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $content_id]);
        echo json_encode(['success' => true, 'in_watchlist' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
