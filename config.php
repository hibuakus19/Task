<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'taskmanager');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
    session_start();
}

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, 'api.php')) {
            http_response_code(401);
            die(json_encode(['error' => 'Unauthorized']));
        }
        header('Location: login.php'); exit;
    }
}

function currentUser(): array {
    return [
        'id'    => (int)($_SESSION['user_id']   ?? 0),
        'nama'  => $_SESSION['user_nama']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn  = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts); }
        catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'DB: '.$e->getMessage()]));
        }
    }
    return $pdo;
}
