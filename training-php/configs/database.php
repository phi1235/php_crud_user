<?php
// Ở đầu file configs/database.php
if (getenv('DOCKER')) {
    require __DIR__ . '/database.docker.php';
    return;
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', 3306);
define('DB_NAME', 'app_web1');
