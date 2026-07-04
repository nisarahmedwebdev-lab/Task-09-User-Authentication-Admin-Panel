<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

try {
    // Require admin access
    SessionManager::requireAdmin();
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !CSRF::verifyToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }
    
    $user_id = $_POST['user_id'] ?? 0;
    $user_id = (int)$user_id;
    
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }
    
    $db = Database::getInstance();
    
    // Get user profile image before deletion
    $stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found or cannot be deleted');
    }
    
    // Delete user
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Delete profile image if not default
    if ($user['profile_image'] !== 'default.png') {
        $image_path = UPLOAD_DIR . $user['profile_image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>