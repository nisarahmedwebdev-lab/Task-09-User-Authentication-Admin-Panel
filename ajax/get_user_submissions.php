<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::requireLogin();

try {
    $user_id = $_SESSION['user_id'];
    
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT s.*, t.title as task_title 
        FROM task_submissions s
        JOIN tasks t ON s.task_id = t.id
        WHERE s.user_id = ?
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$user_id]);
    $submissions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'submissions' => $submissions
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>