<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

// Require admin access
SessionManager::requireAdmin();

try {
    if (!isset($_POST['csrf_token']) || !CSRF::verifyToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }
    
    $submission_id = (int)$_POST['submission_id'];
    $status = $_POST['status'] ?? '';
    $admin_comment = trim($_POST['admin_comment'] ?? '');
    
    if ($submission_id <= 0) {
        throw new Exception('Invalid submission ID');
    }
    
    if (!in_array($status, ['approved', 'rejected'])) {
        throw new Exception('Invalid status');
    }
    
    $db = Database::getInstance();
    
    // Update submission status
    $stmt = $db->prepare("
        UPDATE task_submissions 
        SET status = ?, admin_comment = ?, admin_viewed = 1, reviewed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $admin_comment, $submission_id]);
    
    // Get submission details to update task status if approved
    if ($status === 'approved') {
        $stmt = $db->prepare("SELECT task_id, user_id FROM task_submissions WHERE id = ?");
        $stmt->execute([$submission_id]);
        $submission = $stmt->fetch();
        
        if ($submission) {
            // Update task status to completed
            $stmt = $db->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?");
            $stmt->execute([$submission['task_id']]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Submission ' . $status . ' successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>