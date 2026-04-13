<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    // Проверяем remember_token
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $stmt = $pdo->prepare("SELECT id FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, minecraft_nick, email, has_pass, subscription_days, 
           warn_count, is_banned, subscription_end_date, created_at
    FROM users 
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
    exit;
}

// Рассчитываем оставшиеся дни подписки
$remaining_days = 0;
$subscription_valid_until = null;

if ($user['subscription_days'] > 0) {
    // Если есть дата окончания
    if ($user['subscription_end_date']) {
        $end_date = new DateTime($user['subscription_end_date']);
        $now = new DateTime();
        $remaining_days = $now->diff($end_date)->days;
        if ($now > $end_date) $remaining_days = 0;
        $subscription_valid_until = $end_date->format('d.m.Y');
    } else {
        // Если используем subscription_days как количество дней от регистрации
        $created = new DateTime($user['created_at']);
        $now = new DateTime();
        $days_passed = $now->diff($created)->days;
        $remaining_days = max(0, $user['subscription_days'] - $days_passed);
        if ($remaining_days > 0) {
            $subscription_valid_until = $now->modify("+$remaining_days days")->format('d.m.Y');
        }
    }
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'minecraft_nick' => $user['minecraft_nick'],
        'email' => $user['email'],
        'has_pass' => (bool)$user['has_pass'],
        'subscription_days' => (int)$user['subscription_days'],
        'remaining_days' => $remaining_days,
        'subscription_valid_until' => $subscription_valid_until,
        'warn_count' => (int)$user['warn_count'],
        'is_banned' => (bool)$user['is_banned'],
        'created_at' => date('d.m.Y', strtotime($user['created_at']))
    ]
]);
?>