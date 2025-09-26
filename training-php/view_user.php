<?php
session_start();
require_once 'models/UserModel.php';
require_once 'security/CSRF.php';
$userModel = new UserModel();

$user = NULL; //Add new user
$id = NULL;

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $user = $userModel->findUserById($id);//Update existing user
}


if (!empty($_POST['submit'])) {
    // ✅ CHỐNG CSRF: Validate CSRF token để ngăn chặn Cross-Site Request Forgery
    CSRF::validateRequest();
    
    // ✅ VALIDATION & SANITIZATION: Validate và sanitize input để tránh XSS và injection
    require_once 'security/Validator.php';
    try {
        $sanitizedInput = Validator::sanitizeUserInput($_POST);
        
        if (!empty($id)) {
            $userModel->updateUser($sanitizedInput);
        } else {
            $userModel->insertUser($sanitizedInput);
        }
        header('location: list_users.php');
    } catch (InvalidArgumentException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('location: view_user.php?id=' . $id);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>
<div class="container">

    <?php if ($user || empty($id)) { ?>
        <div class="alert alert-warning" role="alert">
            User profile
        </div>
        <form method="POST">
            <!-- ✅ CHỐNG CSRF: Thêm CSRF token vào form -->
            <?php echo CSRF::getTokenField(); ?>
            <!-- ✅ CHỐNG XSS: Escape HTML entities cho ID -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <!-- ✅ CHỐNG XSS: htmlspecialchars() escape output -->
                <span><?php if (!empty($user[0]['name'])) echo htmlspecialchars($user[0]['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="form-group">
                <label for="password">Fullname</label>
                <!-- ✅ CHỐNG XSS: htmlspecialchars() escape output -->
                <span><?php if (!empty($user[0]['name'])) echo htmlspecialchars($user[0]['fullname'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="form-group">
                <label for="password">Email</label>
                <!-- ✅ CHỐNG XSS: htmlspecialchars() escape output -->
                <span><?php if (!empty($user[0]['name'])) echo htmlspecialchars($user[0]['email'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </form>
    <?php } else { ?>
        <div class="alert alert-success" role="alert">
            User not found!
        </div>
    <?php } ?>
</div>
</body>
</html>