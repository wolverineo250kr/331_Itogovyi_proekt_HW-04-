<?php
require_once 'config.php';
require_once 'dbFunctions.php';

$clientToken = isset($_POST['csrf_token']) ?$_POST['csrf_token'] : '';

// Проверка токена
if (!validateCsrfToken($clientToken)) {
    http_response_code(403); // Запрет доступа
    echo 'А CSRF токен то не настоящий!';
    exit();
}


// Подключение к базе данных
$pdo = connectDbPdo();

// Получение данных из формы
$login = $_POST['login'];
$password = $_POST['password'];
$email = $_POST['email'];

// Проверка на заполнение всех необходимых полей
if (empty($login) || empty($password)) {
    die("Пожалуйста, заполните все необходимые поля.");
}

// Проверка на уникальность логина
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$login]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    die("Логин уже занят, выберите другой логин.");
}

// Хэширование пароля
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Подготовка и выполнение SQL-запроса для добавления пользователя в базу данных
$stmt = $pdo->prepare("INSERT INTO users (nickname, password, email) VALUES (?, ?, ?)");

try {
    $stmt->execute([$login, $hashedPassword, $email]);

    sendMail($email, $login);

    header("Location: /");
    exit();
} catch (PDOException $e) {
    die("Ошибка регистрации: " . $e->getMessage());
}
?>