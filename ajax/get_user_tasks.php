<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::requireLogin();

try {
    $user_id = $_SESSION['user_id'];
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $db = Database::getInstance();
    
    // Show tasks that are:
    // 1. Assigned to the user, OR
    // 2. Not assigned to anyone (NULL), OR
    // 3. Created by the user
    $query = "
        SELECT t.*, 
               u.full_name as assigned_to_name,
               (SELECT COUNT(*) FROM task_submissions WHERE task_id = t.id AND user_id = ?) as has_submitted,
               CASE 
                   WHEN t.assigned_to = ? THEN 'assigned'
                   WHEN t.assigned_to IS NULL THEN 'available'
                   ELSE 'other'
               END as assignment_status
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        WHERE t.assigned_to = ? 
           OR t.assigned_to IS NULL
           OR t.created_by = ?
    ";
    
    $params = [$user_id, $user_id, $user_id, $user_id];
    
    if ($status !== 'all') {
        $query .= " AND t.status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY 
                CASE 
                    WHEN t.assigned_to = ? THEN 1
                    WHEN t.assigned_to IS NULL THEN 2
                    ELSE 3
                END,
                t.created_at DESC";
    
    $params[] = $user_id;
    
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