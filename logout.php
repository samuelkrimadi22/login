<?php
/**
 * logout.php — V8.9.22
 * Logout bersih: hapus session PHP + semua cookie auth.
 */
require_once __DIR__ . '/config/config.php';

// 1. Hapus semua session data
$_SESSION = [];

// 2. Hapus session cookie dari browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy session di server
session_destroy();

// 4. Hapus semua auth cookie (JWT, username, plan, admin)
clearAuthToken();

// 5. Redirect ke halaman utama
header('Location: index.php?logout=1');
exit;