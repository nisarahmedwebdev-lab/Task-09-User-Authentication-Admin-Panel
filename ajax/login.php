<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !CSRF::verifyToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Get user from database
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, full_name, email, password, gender, country, profile_image, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'errors' => ['email' => 'Invalid email or password']]);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'errors' => ['password' => 'Invalid email or password']]);
        exit;
    }
    
    // Login successful
    SessionManager::login($user['id'], $user['role']);
    CSRF::regenerateToken();
    
    // Return user data
    unset($user['password']);
    echo json_encode([
        'success' => true,
        'user' => $user,
        'message' => 'Login successful'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>