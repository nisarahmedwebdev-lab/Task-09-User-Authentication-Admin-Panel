<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::requireAdmin();

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id, full_name, email FROM users WHERE role != 'admin' ORDER BY full_name");
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>