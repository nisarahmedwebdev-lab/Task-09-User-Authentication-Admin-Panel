<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::requireAdmin();

try {
    $db = Database::getInstance();
    
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $query = "
        SELECT t.*, 
               u.full_name as assigned_to_name,
               u2.full_name as created_by_name
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        LEFT JOIN users u2 ON t.created_by = u2.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status) {
        $query .= " AND t.status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY t.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>