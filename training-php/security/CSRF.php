<?php

/**
 * ✅ CHỐNG CSRF (Cross-Site Request Forgery)
 * Class này cung cấp các phương thức để tạo và xác thực CSRF token
 * nhằm ngăn chặn các tấn công CSRF
 */
class CSRF {
    
    /**
     * ✅ TẠO CSRF TOKEN: Tạo token ngẫu nhiên 64 ký tự để bảo vệ form
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            // Tạo token ngẫu nhiên 32 bytes = 64 ký tự hex
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * ✅ XÁC THỰC CSRF TOKEN: So sánh token từ form với token trong session
     */
    public static function verifyToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Sử dụng hash_equals để tránh timing attack
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * ✅ TẠO HIDDEN FIELD: Tạo hidden input chứa CSRF token cho form HTML
     */
    public static function getTokenField() {
        $token = self::generateToken();
        // Escape HTML để tránh XSS khi output token
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * ✅ VALIDATE REQUEST: Kiểm tra POST request và xác thực CSRF token
     * Dừng thực thi nếu token không hợp lệ
     */
    public static function validateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!self::verifyToken($token)) {
                die('CSRF token validation failed');
            }
        }
    }
}
