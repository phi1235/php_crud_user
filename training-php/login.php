<?php
// Start the session
session_start();

require_once 'models/UserModel.php';
require_once 'security/CSRF.php';
require_once 'security/Validator.php';
$userModel = new UserModel();

if (!empty($_POST['submit'])) {
    // Validate CSRF token
    CSRF::validateRequest();
    
    // Regenerate session ID early to avoid header issues
    session_regenerate_id(true);
    
    // Sanitize input
    $username = Validator::sanitizeString($_POST['username'] ?? '', 50);
    $password = $_POST['password'] ?? '';
    
    $users = [
        'username' => $username,
        'password' => $password
    ];
    $user = NULL;
    if ($user = $userModel->auth($users['username'], $users['password'])) {
        // Giữ phiên server-side (Redis)
        $_SESSION['id'] = $user[0]['id'];
        $_SESSION['message'] = 'Login successful';
        // Ghi session trước khi chuyển trang để đảm bảo lưu vào Redis
        session_write_close();
        header('location: list_users.php');
        exit;
    } else {
        $_SESSION['message'] = 'Login failed';
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
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info" >
                <div class="panel-heading">
                    <div class="panel-title">Login</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body" >
                    <form method="post" class="form-horizontal" role="form" onsubmit="return saveRememberedUsername();">
                        <?php echo CSRF::getTokenField(); ?>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="username or email">
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                        </div>

                        <div class="margin-bottom-25">
                            <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                            <label for="remember"> Remember Me</label>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <!-- Button -->
                            <div class="col-sm-12 controls">
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                                <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12 control">
                                    Don't have an account!
                                    <a href="form_user.php">
                                        Sign Up Here
                                    </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
<script>
    function saveRememberedUsername() {
        var usernameInput = document.getElementById('login-username');
        var rememberCheckbox = document.getElementById('remember');
        try {
            if (rememberCheckbox && rememberCheckbox.checked && usernameInput) {
                localStorage.setItem('remembered_username', usernameInput.value || '');
            } else {
                localStorage.removeItem('remembered_username');
            }
        } catch (e) {}
        return true; // tiếp tục submit
    }

    (function() {
        var usernameInput = document.getElementById('login-username');
        var rememberCheckbox = document.getElementById('remember');
        try {
            var savedUsername = localStorage.getItem('remembered_username');
            if (savedUsername && usernameInput) {
                usernameInput.value = savedUsername;
                if (rememberCheckbox) rememberCheckbox.checked = true;
            }
        } catch (e) {}
    })();
    // Clear any insecure cookie.txt logging endpoint usage
</script>
</html>