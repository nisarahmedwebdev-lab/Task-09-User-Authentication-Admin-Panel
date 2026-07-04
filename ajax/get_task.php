<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!SessionManager::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

try {
    $task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($task_id <= 0) {
        throw new Exception('Invalid task ID');
    }
    
    $db = Database::getInstance();
    
    // Get task details with user info
    $stmt = $db->prepare("
        SELECT t.*, 
               u.full_name as assigned_to_name,
               u2.full_name as created_by_name
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        LEFT JOIN users u2 ON t.created_by = u2.id
        WHERE t.id = ?
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        throw new Exception('Task not found');
    }
    
    // Check if user has access to this task
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'user';
    
    if ($user_role !== 'admin') {
        // For users: check if task is assigned to them or unassigned
        if ($task['assigned_to'] !== null && $task['assigned_to'] != $user_id) {
            throw new Exception('You do not have access to this task');
        }
    }
    
    echo json_encode([
        'success' => true,
        'task' => $task
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>