<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

SessionManager::requireLogin();

try {
    if (!isset($_POST['csrf_token']) || !CSRF::verifyToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }
    
    $task_id = (int)$_POST['task_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    // Validate
    if (empty($title)) {
        throw new Exception('Submission title is required');
    }
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Please upload a ZIP file');
    }
    
    $file = $_FILES['file'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if ($file_ext !== 'zip') {
        throw new Exception('Only ZIP files are allowed');
    }
    
    // Validate file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('File size must be less than 10MB');
    }
    
    $db = Database::getInstance();
    
    // Check if task exists and user is assigned to it
    $stmt = $db->prepare("SELECT id FROM tasks WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL)");
    $stmt->execute([$task_id, $user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Task not found or not assigned to you');
    }
    
    // Check if already submitted
    $stmt = $db->prepare("SELECT id FROM task_submissions WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('You have already submitted this task');
    }
    
    // Create submissions directory if not exists
    $upload_dir = __DIR__ . '/../uploads/submissions/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $new_filename = time() . '_' . $user_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $file_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Failed to upload file');
    }
    
    // Save submission
    $stmt = $db->prepare("
        INSERT INTO task_submissions (task_id, user_id, title, description, file_path, file_name) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $task_id,
        $user_id,
        $title,
        $description,
        'uploads/submissions/' . $new_filename,
        $file['name']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Task submitted successfully!'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>