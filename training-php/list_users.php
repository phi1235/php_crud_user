<?php
// list_users.php (đã sửa để giữ nguyên giao diện, nhưng thực hiện POST + CSRF khi Delete)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include an toàn bằng __DIR__
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/security/CSRF.php';

$userModel = new UserModel();

$params = [];
if (!empty($_GET['keyword'])) {
    // giữ nguyên việc nhận keyword từ GET cho chức năng tìm kiếm, nhưng khi dùng DB luôn dùng prepared statement ở model
    $params['keyword'] = $_GET['keyword'];
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <!-- Không dùng localStorage để quyết định auth; dùng session server-side -->

    <?php include 'views/header.php'?>

    <div class="container">
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                Hacker: http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { 
                        // đảm bảo giá trị an toàn
                        $uid = (int)$user['id'];
                    ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($uid, ENT_QUOTES, 'UTF-8') ?></th>
                            <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?php echo htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?php echo htmlspecialchars($user['type'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <!-- Update -->
                                <a href="form_user.php?id=<?php echo urlencode($uid) ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>

                                <!-- View -->
                                <a href="view_user.php?id=<?php echo urlencode($uid) ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>

                                <!-- Delete: vẫn hiển thị icon giống cũ, nhưng thực tế submit form POST ẩn kèm CSRF token -->
                                <a href="#"
                                   onclick="event.preventDefault(); if (confirm('Xoá user này?')) document.getElementById('del-<?php echo $uid ?>').submit();">
                                    <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                </a>

                                <!-- form ẩn gửi POST kèm CSRF token -->
                                <form id="del-<?php echo $uid ?>" method="POST" action="delete_user.php" style="display:none" aria-hidden="true">
                                    <?php echo CSRF::getTokenField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $uid ?>">
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark" role="alert">
                This is a dark alert—check it out!
            </div>
        <?php } ?>
    </div>
</body>
</html>
