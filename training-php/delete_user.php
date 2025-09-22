<?php
session_start();
require_once 'models/UserModel.php';
require_once 'security/CSRF.php';
$userModel = new UserModel();

// Validate CSRF token for GET requests (using POST would be better)
$token = $_GET['csrf_token'] ?? '';
if (!CSRF::verifyToken($token)) {
    die('CSRF token validation failed');
}

$user = NULL; //Add new user
$id = NULL;

if (!empty($_GET['id'])) {
    $id = (int)$_GET['id']; // Cast to int for safety
    $userModel->deleteUserById($id);//Delete existing user
}
header('location: list_users.php');
?>