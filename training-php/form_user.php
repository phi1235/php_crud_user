<?php
// form_user.php — giữ nguyên luồng cũ: POST về chính trang này, chỉ thêm CSRF

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/security/CSRF.php';

$userModel = new UserModel();

$user = null;   // Add new user
$_id  = null;

// Nếu có id trên URL thì load user để sửa
if (!empty($_GET['id'])) {
    $_id = (int)$_GET['id'];
    $user = $userModel->findUserById($_id); // Update existing user
}

// Submit form (POST về chính trang này)
if (!empty($_POST['submit'])) {
    // ✅ Kiểm tra CSRF token (bổ sung)
    $token = $_POST['csrf_token'] ?? '';
    if (!CSRF::verifyToken($token)) {
        http_response_code(403);
        exit('CSRF token validation failed');
    }

    // Giữ nguyên logic cũ: nếu có $_id thì update, ngược lại insert
    if (!empty($_id)) {
        $userModel->updateUser($_POST);
    } else {
        $userModel->insertUser($_POST);
    }

    // Điều hướng về danh sách
    header('location: list_users.php');
    exit;
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

    <?php if ($user || !isset($_id)) { ?>
        <div class="alert alert-warning" role="alert">
            User form
        </div>

        <!-- Không đặt action => POST về chính form_user.php (giữ nguyên như cũ) -->
        <form method="POST">
            <!-- ✅ CSRF hidden field (bổ sung) -->
            <?= CSRF::getTokenField(); ?>

            <input type="hidden" name="id" value="<?php echo htmlspecialchars($_id ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="name">Name</label>
                <input class="form-control" name="name" placeholder="Name" required
                       value='<?php if (!empty($user[0]["name"])) echo htmlspecialchars($user[0]["name"], ENT_QUOTES, "UTF-8"); ?>'>
            </div>

            <div class="form-group">
                <label for="fullname">Full name</label>
                <input class="form-control" name="fullname" placeholder="Full name" required
                       value='<?php if (!empty($user[0]["fullname"])) echo htmlspecialchars($user[0]["fullname"], ENT_QUOTES, "UTF-8"); ?>'>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" name="email" type="email" placeholder="Email" required
                       value='<?php if (!empty($user[0]["email"])) echo htmlspecialchars($user[0]["email"], ENT_QUOTES, "UTF-8"); ?>'>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <input class="form-control" name="type" placeholder="Type (e.g. user/admin)"
                       value='<?php echo !empty($user[0]["type"]) ? htmlspecialchars($user[0]["type"], ENT_QUOTES, "UTF-8") : "user"; ?>'>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <!-- Giữ nguyên: luôn required như code cũ -->
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
        </form>

    <?php } else { ?>
        <div class="alert alert-success" role="alert">
            User not found!
        </div>
    <?php } ?>

</div>
</body>
</html>
