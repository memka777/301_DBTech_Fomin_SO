<?php
// Конфигурация базы данных
define('DB_PATH', __DIR__ . '/../data/carwash.db');
define('DB_DSN', 'sqlite:' . DB_PATH);

// Настройки приложения
define('SITE_NAME', 'Автомойка "Чистота"');
define('ITEMS_PER_PAGE', 10);

// Включение отображения ошибок (только для разработки)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);