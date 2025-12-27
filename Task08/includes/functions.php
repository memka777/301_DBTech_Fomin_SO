<?php
// Функции для работы с приложением

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function formatTime($time) {
    return date('H:i', strtotime($time));
}

function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function get($key, $default = '') {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function post($key, $default = '') {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function validateRequired($field, $value) {
    if (empty(trim($value))) {
        return "Поле '$field' обязательно для заполнения";
    }
    return null;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Некорректный email адрес";
    }
    return null;
}

function validatePhone($phone) {
    if (!preg_match('/^\+7\d{10}$/', $phone)) {
        return "Телефон должен быть в формате +7XXXXXXXXXX";
    }
    return null;
}