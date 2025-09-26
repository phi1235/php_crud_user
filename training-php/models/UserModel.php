<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        // ✅ CHỐNG SQL INJECTION: Sử dụng prepared statement với placeholder (?)
        $sql = 'SELECT * FROM users WHERE id = ?';
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        // ✅ CHỐNG SQL INJECTION: Bind parameter với kiểu dữ liệu cụ thể (integer)
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        return $rows;
    }

    public function findUser($keyword) {
        $sql = 'SELECT * FROM users WHERE name LIKE ? OR email LIKE ?';
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        $searchTerm = '%' . $keyword . '%';
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userNameOrEmail, $password) {
        // ✅ CHỐNG SQL INJECTION: Prepared statement cho authentication
        $sql = 'SELECT * FROM users WHERE name = ? OR email = ?';
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        // ✅ CHỐNG SQL INJECTION: Bind parameters an toàn
        $stmt->bind_param('ss', $userNameOrEmail, $userNameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // ✅ BẢO MẬT MẬT KHẨU: Sử dụng password_verify thay vì so sánh trực tiếp
                if (password_verify($password, $row['password'])) {
                    $rows[] = $row;
                }
            }
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id) {
        $sql = 'DELETE FROM users WHERE id = ?';
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input) {
        $sql = 'UPDATE users SET name = ?, password = ? WHERE id = ?';
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        // ✅ BẢO MẬT MẬT KHẨU: Hash password trước khi lưu vào database
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        // ✅ CHỐNG SQL INJECTION: Bind parameters với kiểu dữ liệu cụ thể
        $stmt->bind_param('ssi', $input['name'], $passwordHash, $input['id']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Insert user using prepared statement with all required columns
     * @param array $input
     * @return bool
     * @throws \Exception
     */
    public function insertUser(array $input) {
        $conn = self::$_connection;

        $name = trim($input['name'] ?? '');
        $fullname = trim($input['fullname'] ?? '');
        $email = trim($input['email'] ?? '');
        $type = trim($input['type'] ?? 'user');
        $password = (string)($input['password'] ?? '');
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($fullname === '' || $email === '' || $type === '') {
            throw new \Exception('fullname/email/type are required');
        }

        // ✅ CHỐNG SQL INJECTION: Prepared statement cho INSERT operation
        $sql = "INSERT INTO users (`name`,`fullname`,`email`,`type`,`password`) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new \Exception('Prepare failed');
        }
        // ✅ CHỐNG SQL INJECTION: Bind tất cả parameters với kiểu string
        $stmt->bind_param("sssss", $name, $fullname, $email, $type, $passwordHash);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = []) {
        //Keyword
        if (!empty($params['keyword'])) {
            $sql = 'SELECT * FROM users WHERE name LIKE ?';
            $stmt = self::$_connection->prepare($sql);
            if ($stmt === false) {
                return [];
            }
            $searchTerm = '%' . $params['keyword'] . '%';
            $stmt->bind_param('s', $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            $users = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
            }
            $stmt->close();
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->select($sql);
        }

        return $users;
    }
}