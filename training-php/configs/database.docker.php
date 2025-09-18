<?php
// Use Docker service names and env vars
define('DB_HOST', getenv('DB_HOST') ?: 'db'); // 'db' is the service name in docker-compose
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'root');
define('DB_PORT', getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'app_web1');
