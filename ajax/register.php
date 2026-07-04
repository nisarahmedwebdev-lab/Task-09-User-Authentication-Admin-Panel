<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../csrf.php';

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !CSRF::verifyToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }
    
    // Get POST data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $country = trim($_POST['country'] ?? '');
    $privacy_agreed = isset($_POST['privacy_agreed']) ? 1 : 0;
    
    $errors = [];
    
    // Validate full name
    if (empty($full_name) || strlen($full_name) < 3) {
        $errors['full_name'] = 'Full name must be at least 3 characters';
    }
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } else {
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password must contain at least 1 uppercase letter';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least 1 number';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least 1 special character';
        }
    }
    
    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Validate gender
    if (!in_array($gender, ['male', 'female', 'other'])) {
        $errors['gender'] = 'Please select a gender';
    }
    
    // Validate country
    if (empty($country)) {
        $errors['country'] = 'Please select a country';
    }
    
    // Validate privacy agreement
    if (!$privacy_agreed) {
        $errors['privacy'] = 'You must agree to the Privacy Policy';
    }
    
    // Validate profile image
    $profile_image = 'default.png';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $file_type = mime_content_type($file['tmp_name']);
        $file_size = $file['size'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_type, ALLOWED_MIME_TYPES)) {
            $errors['profile_image'] = 'Only JPG and PNG images are allowed';
        } elseif ($file_size > MAX_FILE_SIZE) {
            $errors['profile_image'] = 'Image size must be less than 2MB';
        } elseif (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
            $errors['profile_image'] = 'Only JPG and PNG images are allowed';
        }
    } elseif ($_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['profile_image'] = 'Error uploading file';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Check if email already exists
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'errors' => ['email' => 'Email already exists']]);
        exit;
    }
    
    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        // Create uploads directory if it doesn't exist
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        
        $file = $_FILES['profile_image'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $file['name']);
        $upload_path = UPLOAD_DIR . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $profile_image = $new_filename;
        } else {
            echo json_encode(['success' => false, 'errors' => ['profile_image' => 'Failed to upload image']]);
            exit;
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (full_name, email, password, gender, country, profile_image, privacy_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$full_name, $email, $hashed_password, $gender, $country, $profile_image, $privacy_agreed]);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login.'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>