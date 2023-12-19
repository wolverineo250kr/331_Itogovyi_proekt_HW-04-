<?php
require_once 'config.php';
require_once 'dbFunctions.php';
session_start();

// Подключение к базе данных
$pdo = connectDbPdo();

$clientToken = isset($_POST['csrf_token']) ?$_POST['csrf_token'] : '';

// Проверка токена
if (!validateCsrfToken($clientToken)) {
    // Некорректный токен, обработайте ошибку
    http_response_code(403); // Запрет доступа
    echo 'А CSRF токен то не настоящий!';
    exit();
}

// Получение данных из формы
$login = $_POST['login'];
$password = $_POST['password'];

// Подготовка и выполнение SQL-запроса для получения записи пользователя из базы данных
$stmt = $pdo->prepare("SELECT * FROM messanger.users WHERE email = ?");
$stmt->execute([$login]);
$user = $stmt->fetch();

// Проверка наличия пользователя и проверка пароля
if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user'] = $user['email'];

    header("Location: /");
    exit();
} else {
    echo "Неверный логин или пароль.";
}
?>