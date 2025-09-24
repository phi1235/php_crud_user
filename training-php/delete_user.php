<?php
require_once __DIR__ . '/security/CSRF.php';
require_once __DIR__ . '/security/Validator.php';
require_once __DIR__ . '/models/UserModel.php';

session_start();

// Chỉ cho phép phương thức POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit('Method Not Allowed');
}

// Kiểm tra CSRF token
$token = $_POST['csrf_token'] ?? '';
if (!CSRF::verifyToken($token)) {
    http_response_code(403); // Forbidden
    exit('CSRF token validation failed');
}

// Lấy và validate ID
$id = $_POST['id'] ?? null;
if (!Validator::validateInt($id)) {
    http_response_code(400); // Bad Request
    exit('Invalid ID');
}

// Xóa user
$userModel = new UserModel();
$userModel->deleteUserById((int)$id);

// Redirect về danh sách
header('Location: list_users.php', true, 303);
exit;
