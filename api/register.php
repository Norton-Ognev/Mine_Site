<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$minecraft_nick = trim($data['minecraft_nick'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Валидация
if (empty($minecraft_nick) || empty($email) || empty($password)) {
    die(json_encode(['success' => false, 'error' => 'Все поля обязательны']));
}

if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $minecraft_nick)) {
    die(json_encode(['success' => false, 'error' => 'Некорректный ник Minecraft']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'error' => 'Некорректный email']));
}

if (strlen($password) < 6) {
    die(json_encode(['success' => false, 'error' => 'Пароль должен быть минимум 6 символов']));
}

// Проверка на существование
$stmt = $pdo->prepare("SELECT id FROM users WHERE minecraft_nick = ? OR email = ?");
$stmt->execute([$minecraft_nick, $email]);
if ($stmt->fetch()) {
    die(json_encode(['success' => false, 'error' => 'Пользователь с таким ником или email уже существует']));
}

// Хэшируем пароль
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Создаём пользователя
$stmt = $pdo->prepare("INSERT INTO users (minecraft_nick, email, password_hash) VALUES (?, ?, ?)");
$stmt->execute([$minecraft_nick, $email, $password_hash]);

// Сразу логиним пользователя после регистрации
$userId = $pdo->lastInsertId();

// Генерируем токены
$remember_token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
$stmt->execute([$remember_token, $userId]);

// Ставим cookie на 30 дней
setcookie('remember_token', $remember_token, time() + 86400 * 30, '/', '', false, true);

// Создаём сессию
$_SESSION['user_id'] = $userId;
$_SESSION['minecraft_nick'] = $minecraft_nick;

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $userId,
        'minecraft_nick' => $minecraft_nick,
        'email' => $email,
        'has_pass' => false,
        'subscription_days' => 0
    ]
]);
?>