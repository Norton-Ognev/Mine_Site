-- Создание базы данных
CREATE DATABASE IF NOT EXISTS minecraft_server;
USE minecraft_server;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Уникальный ID пользователя',
    username VARCHAR(16) NOT NULL UNIQUE COMMENT 'Minecraft ник (3-16 символов)',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email пользователя',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Хэш пароля (bcrypt)',
    rank VARCHAR(50) DEFAULT 'Новичок' COMMENT 'Ранг игрока',
    coins INT DEFAULT 1000 COMMENT 'Монеты',
    crystals INT DEFAULT 0 COMMENT 'Кристаллы',
    keys_count INT DEFAULT 0 COMMENT 'Легендарные ключи',
    tokens INT DEFAULT 0 COMMENT 'Токены событий',
    boss_kills INT DEFAULT 0 COMMENT 'Убийств боссов',
    unique_crafts INT DEFAULT 0 COMMENT 'Уникальных крафтов',
    legendary_kills INT DEFAULT 0 COMMENT 'Побеждено мифических боссов',
    achievements INT DEFAULT 0 COMMENT 'Достижений получено',
    resources_collected INT DEFAULT 0 COMMENT 'Собрано ресурсов',
    premium_until DATE NULL COMMENT 'Дата окончания премиума',
    season_pass TINYINT DEFAULT 0 COMMENT 'Активен ли сезонный пасс',
    vip_chat TINYINT DEFAULT 0 COMMENT 'Доступ в VIP-чат',
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата регистрации',
    last_login DATETIME NULL COMMENT 'Последний вход',
    is_active TINYINT DEFAULT 1 COMMENT 'Активен ли аккаунт',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица пользователей';

-- Таблица сессий (для автоподдержания входа)
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID пользователя',
    session_token VARCHAR(255) NOT NULL UNIQUE COMMENT 'Уникальный токен сессии',
    ip_address VARCHAR(45) NULL COMMENT 'IP адрес',
    user_agent TEXT NULL COMMENT 'User Agent браузера',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Время создания',
    expires_at DATETIME NOT NULL COMMENT 'Время истечения сессии',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица сессий пользователей';

-- Таблица для отслеживания смены ника
CREATE TABLE IF NOT EXISTS nickname_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID пользователя',
    old_username VARCHAR(16) NOT NULL COMMENT 'Старый ник',
    new_username VARCHAR(16) NOT NULL COMMENT 'Новый ник',
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата смены',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История смены ников';