<?php
require_once 'config.php';
require_once 'database.php';

try {
    $db = Database::getInstance();
    
    // First, let's check if admin exists and delete it
    $stmt = $db->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    
    // Create a new admin with a known password
    $password = 'Admin@123'; // This is the password you'll use to login
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Insert admin
    $stmt = $db->prepare("
        INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'Admin User',
        'admin@example.com',
        $hashed_password,
        'male',
        'Pakistan',
        'admin',
        1
    ]);
    
    echo "========================================\n";
    echo "✅ Admin user created successfully!\n";
    echo "========================================\n";
    echo "📧 Email: admin@example.com\n";
    echo "🔑 Password: Admin@123\n";
    echo "========================================\n";
    echo "Hash: " . $hashed_password . "\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>