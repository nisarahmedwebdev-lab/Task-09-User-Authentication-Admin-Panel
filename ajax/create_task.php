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
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    $assigned_to = $_POST['assigned_to'] ?? null;
    $created_by = $_SESSION['user_id'];
    
    // If assigned_to is empty or null, set to NULL
    if (empty($assigned_to)) {
        $assigned_to = null;
    }
    
    if (empty($title)) {
        throw new Exception('Task title is required');
    }
    
    $db = Database::getInstance();
    $stmt = $db->prepare("
        INSERT INTO tasks (title, description, status, priority, due_date, assigned_to, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$title, $description, $status, $priority, $due_date, $assigned_to, $created_by]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Task created successfully',
        'task_id' => $db->lastInsertId()
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>