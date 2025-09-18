<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $sql = 'SELECT * FROM users WHERE id = '.$id;
        $user = $this->select($sql);

        return $user;
    }

    public function findUser($keyword) {
        $sql = 'SELECT * FROM users WHERE user_name LIKE %'.$keyword.'%'. ' OR user_email LIKE %'.$keyword.'%';
        $user = $this->select($sql);

        return $user;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userNameOrEmail, $password) {
        $md5Password = md5($password);
        $sql = 'SELECT * FROM users WHERE (name = ? OR email = ?) AND password = ?';
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param('sss', $userNameOrEmail, $userNameOrEmail, $md5Password);
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
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id) {
        $sql = 'DELETE FROM users WHERE id = '.$id;
        return $this->delete($sql);

    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input) {
        $sql = 'UPDATE users SET 
                 name = "' . mysqli_real_escape_string(self::$_connection, $input['name']) .'", 
                 password="'. md5($input['password']) .'"
                WHERE id = ' . $input['id'];

        $user = $this->update($sql);

        return $user;
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
        $passwordHash = md5($password);

        if ($fullname === '' || $email === '' || $type === '') {
            throw new \Exception('fullname/email/type are required');
        }

        $sql = "INSERT INTO users (`name`,`fullname`,`email`,`type`,`password`) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new \Exception('Prepare failed');
        }
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
            $sql = 'SELECT * FROM users WHERE name LIKE "%' . $params['keyword'] .'%"';

            //Keep this line to use Sql Injection
            //Don't change
            //Example keyword: abcef%";TRUNCATE banks;##
            $users = self::$_connection->multi_query($sql);

            //Get data
            $users = $this->query($sql);
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->select($sql);
        }

        return $users;
    }
}