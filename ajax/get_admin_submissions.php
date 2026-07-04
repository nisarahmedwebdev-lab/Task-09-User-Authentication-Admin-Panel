<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

// Require admin access
SessionManager::requireAdmin();

try {
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $db = Database::getInstance();
    
    $query = "
        SELECT s.*, 
               t.title as task_title,
               u.full_name as user_name,
               u.email as user_email
        FROM task_submissions s
        JOIN tasks t ON s.task_id = t.id
        JOIN users u ON s.user_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status !== 'all') {
        $query .= " AND s.status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $query .= " AND (s.title LIKE ? OR s.description LIKE ? OR u.full_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY s.submitted_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'submissions' => $submissions
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>