<?php
// Simple debug page for sessions and Redis
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$handler = ini_get('session.save_handler');
$savePath = ini_get('session.save_path');
$sid = session_id();
$cookieSid = $_COOKIE['PHPSESSID'] ?? '(no cookie)';
$hasRedisExt = extension_loaded('redis') ? 'yes' : 'no';

// Optionally try a simple Redis connection check (safe-guarded)
$redisPing = 'n/a';
try {
    if (class_exists('Redis')) {
        $r = new Redis();
        // Parse host from save_path if possible
        // Expect format tcp://redis:6379?...
        $host = 'redis';
        $port = 6379;
        if (strpos($savePath, 'tcp://') === 0) {
            $url = parse_url($savePath);
            if (!empty($url['host'])) { $host = $url['host']; }
            if (!empty($url['port'])) { $port = (int)$url['port']; }
        }
        if (@$r->connect($host, $port, 1.0)) {
            $redisPing = @$r->ping();
            $r->close();
        } else {
            $redisPing = 'connect failed';
        }
    }
} catch (Throwable $e) {
    $redisPing = 'error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug Session</title>
</head>
<body>
<h3>Session/Redis debug</h3>
<pre><?php echo htmlspecialchars(
"session.save_handler = {$handler}\n".
"session.save_path    = {$savePath}\n".
"session_id()         = {$sid}\n".
"Cookie PHPSESSID     = {$cookieSid}\n".
"redis extension      = {$hasRedisExt}\n".
"redis ping           = {$redisPing}\n"
); ?></pre>

<h4>_SESSION dump</h4>
<pre><?php var_export($_SESSION); ?></pre>

<h4>LocalStorage</h4>
<p>Mở Console để xem tất cả mục localStorage được in ra.</p>

<script>
  (function(){
    try {
      const entries = Object.entries(localStorage);
      console.log('localStorage entries:', entries);
      // Hiển thị một vài khóa thường dùng
      console.log('remembered_username =', localStorage.getItem('remembered_username'));
      console.log('user_login =', localStorage.getItem('user_login'));
    } catch(e) {
      console.warn('Cannot access localStorage:', e);
    }
  })();
  </script>
</body>
</html>


