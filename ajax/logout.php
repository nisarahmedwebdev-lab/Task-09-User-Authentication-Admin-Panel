<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

header('Content-Type: application/json');

SessionManager::logout();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>