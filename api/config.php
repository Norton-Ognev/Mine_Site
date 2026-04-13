<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://' . $_SERVER['HTTP_HOST']);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ТВОИ ДАННЫЕ ДЛЯ ПОДКЛЮЧЕНИЯ
$host = 'localhost';
$dbname = 'der9009s_users';
$username = 'der9009s_users';
$password = 'Norton2007!';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die(json_encode(['error' => 'Ошибка подключения к БД: ' . $e->getMessage()]));
}

session_start();
?>