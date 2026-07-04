<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

SessionManager::requireAdmin();

try {
    if (!isset($_POST['csrf_token']) || !CSRF::verifyToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }
    
    $task_id = (int)$_POST['task_id'];
    
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Task deleted successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>