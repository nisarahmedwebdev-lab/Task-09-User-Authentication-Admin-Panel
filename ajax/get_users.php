<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

// Require admin access
SessionManager::requireAdmin();

try {
    $db = Database::getInstance();
    
    // Get total users count
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    // Get all users except admins
    $stmt = $db->prepare("
        SELECT id, full_name, email, gender, country, profile_image, created_at 
        FROM users 
        WHERE role != 'admin' 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'total' => $total,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>