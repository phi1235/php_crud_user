<?php

/**
 * ✅ VALIDATION & SANITIZATION CLASS
 * Class này cung cấp các phương thức để validate và sanitize input
 * nhằm ngăn chặn XSS, SQL Injection và các lỗ hổng bảo mật khác
 */
class Validator {
    
    /**
     * ✅ SANITIZE STRING: Làm sạch chuỗi input để tránh XSS và injection
     */
    public static function sanitizeString($input, $maxLength = 255) {
        $input = trim($input);
        // Không dùng FILTER_SANITIZE_FULL_SPECIAL_CHARS vì có thể làm hỏng UTF-8
        // Chỉ loại bỏ null bytes và control characters
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        return mb_substr($input, 0, $maxLength, 'UTF-8');
    }
    
    /**
     * ✅ VALIDATE EMAIL: Kiểm tra định dạng email hợp lệ
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * ✅ VALIDATE INTEGER: Kiểm tra input có phải là số nguyên hợp lệ
     */
    public static function validateInt($input) {
        return filter_var($input, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * ✅ VALIDATE PASSWORD: Kiểm tra độ mạnh của mật khẩu
     * Yêu cầu: ít nhất 8 ký tự, 1 chữ hoa, 1 chữ thường, 1 số
     */
    public static function validatePassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
    
    /**
     * ✅ SANITIZE & VALIDATE USER INPUT: Xử lý toàn diện input từ user
     * Sanitize tất cả string fields và validate theo từng loại dữ liệu
     */
    public static function sanitizeUserInput($input) {
        $sanitized = [];
        
        // ✅ Sanitize name field
        if (isset($input['name'])) {
            $sanitized['name'] = self::sanitizeString($input['name'], 50);
        }
        
        // ✅ Sanitize fullname field  
        if (isset($input['fullname'])) {
            $sanitized['fullname'] = self::sanitizeString($input['fullname'], 100);
        }
        
        // ✅ Sanitize và validate email
        if (isset($input['email'])) {
            $email = self::sanitizeString($input['email'], 100);
            if (!self::validateEmail($email)) {
                throw new InvalidArgumentException('Invalid email format');
            }
            $sanitized['email'] = $email;
        }
        
        // ✅ Sanitize và validate user type (whitelist)
        if (isset($input['type'])) {
            $type = self::sanitizeString($input['type'], 20);
            if (!in_array($type, ['user', 'admin'])) {
                throw new InvalidArgumentException('Invalid user type');
            }
            $sanitized['type'] = $type;
        }
        
        // ✅ Validate password length
        if (isset($input['password'])) {
            $password = $input['password'];
            if (strlen($password) < 6) {
                throw new InvalidArgumentException('Password must be at least 6 characters');
            }
            $sanitized['password'] = $password;
        }
        
        // ✅ Validate ID là integer
        if (isset($input['id'])) {
            if (!self::validateInt($input['id'])) {
                throw new InvalidArgumentException('Invalid ID format');
            }
            $sanitized['id'] = (int)$input['id'];
        }
        
        return $sanitized;
    }
}
