<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::requireAdmin();

try {
    $db = Database::getInstance();
    
    // Get total tasks
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks");
    $totalTasks = $stmt->fetch()['total'];
    
    // Get pending tasks
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks WHERE status = 'pending'");
    $pendingTasks = $stmt->fetch()['total'];
    
    // Get in progress tasks
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks WHERE status = 'in_progress'");
    $inProgressTasks = $stmt->fetch()['total'];
    
    // Get completed tasks
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks WHERE status = 'completed'");
    $completedTasks = $stmt->fetch()['total'];
    
    // Get total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
    $totalUsers = $stmt->fetch()['total'];
    
    // Get pending submissions
    $stmt = $db->query("SELECT COUNT(*) as total FROM task_submissions WHERE status = 'pending'");
    $pendingSubmissions = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_tasks' => $totalTasks,
            'pending_tasks' => $pendingTasks,
            'in_progress_tasks' => $inProgressTasks,
            'completed_tasks' => $completedTasks,
            'total_users' => $totalUsers,
            'pending_submissions' => $pendingSubmissions
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>