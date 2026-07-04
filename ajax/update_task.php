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
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    $assigned_to = $_POST['assigned_to'] ?? null;
    
    if (empty($title)) {
        throw new Exception('Task title is required');
    }
    
    $db = Database::getInstance();
    $stmt = $db->prepare("
        UPDATE tasks 
        SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, assigned_to = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$title, $description, $status, $priority, $due_date, $assigned_to, $task_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Task updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>