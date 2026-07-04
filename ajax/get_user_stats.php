<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::requireLogin();

try {
    $user_id = $_SESSION['user_id'];
    
    $db = Database::getInstance();
    
    // Total tasks available to user (assigned OR unassigned OR created by user)
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tasks 
        WHERE assigned_to = ? 
           OR assigned_to IS NULL 
           OR created_by = ?
    ");
    $stmt->execute([$user_id, $user_id]);
    $totalTasks = $stmt->fetch()['total'];
    
    // Pending tasks
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tasks 
        WHERE (assigned_to = ? OR assigned_to IS NULL OR created_by = ?) 
        AND status = 'pending'
    ");
    $stmt->execute([$user_id, $user_id]);
    $pendingTasks = $stmt->fetch()['total'];
    
    // In Progress tasks
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tasks 
        WHERE (assigned_to = ? OR assigned_to IS NULL OR created_by = ?) 
        AND status = 'in_progress'
    ");
    $stmt->execute([$user_id, $user_id]);
    $inProgressTasks = $stmt->fetch()['total'];
    
    // Completed tasks
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tasks 
        WHERE (assigned_to = ? OR assigned_to IS NULL OR created_by = ?) 
        AND status = 'completed'
    ");
    $stmt->execute([$user_id, $user_id]);
    $completedTasks = $stmt->fetch()['total'];
    
    // Submitted tasks
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM task_submissions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $submittedTasks = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_tasks' => $totalTasks,
            'pending_tasks' => $pendingTasks,
            'in_progress_tasks' => $inProgressTasks,
            'completed_tasks' => $completedTasks,
            'submitted_tasks' => $submittedTasks
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>