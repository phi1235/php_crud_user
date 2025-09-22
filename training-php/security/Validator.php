<?php

class Validator {
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input, $maxLength = 255) {
        $input = trim($input);
        $input = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return substr($input, 0, $maxLength);
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate integer
     */
    public static function validateInt($input) {
        return filter_var($input, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
    
    /**
     * Sanitize and validate user input
     */
    public static function sanitizeUserInput($input) {
        $sanitized = [];
        
        if (isset($input['name'])) {
            $sanitized['name'] = self::sanitizeString($input['name'], 50);
        }
        
        if (isset($input['fullname'])) {
            $sanitized['fullname'] = self::sanitizeString($input['fullname'], 100);
        }
        
        if (isset($input['email'])) {
            $email = self::sanitizeString($input['email'], 100);
            if (!self::validateEmail($email)) {
                throw new InvalidArgumentException('Invalid email format');
            }
            $sanitized['email'] = $email;
        }
        
        if (isset($input['type'])) {
            $type = self::sanitizeString($input['type'], 20);
            if (!in_array($type, ['user', 'admin'])) {
                throw new InvalidArgumentException('Invalid user type');
            }
            $sanitized['type'] = $type;
        }
        
        if (isset($input['password'])) {
            $password = $input['password'];
            if (strlen($password) < 6) {
                throw new InvalidArgumentException('Password must be at least 6 characters');
            }
            $sanitized['password'] = $password;
        }
        
        if (isset($input['id'])) {
            if (!self::validateInt($input['id'])) {
                throw new InvalidArgumentException('Invalid ID format');
            }
            $sanitized['id'] = (int)$input['id'];
        }
        
        return $sanitized;
    }
}
