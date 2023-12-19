<?php
require_once 'config.php';
require_once 'dbFunctions.php';
session_start();

try {
    $conn = connectDbPdo();
    // Устанавливаем режим обработки ошибок PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверяем, существует ли пользователь в сессии
    if (!isset($_SESSION['user'])) {
        echo json_encode(['error' => 'User not authenticated']);
        die;
    }

    // Получение введенного nickname из запроса
    $nickname = $_GET['nickname'];

    if (!isset($_GET['nickname']) || empty($_GET['nickname'])) {
        echo 'Пусто';
        die;
    }

    // Получаем ID текущего пользователя из сессии
    $currentUserId = $_SESSION['user'];

    // Поиск контактов по nickname в базе данных, исключая текущего пользователя
    $sql = "SELECT id,nickname,email  FROM users WHERE email <> ? AND nickname LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$currentUserId, "%$nickname%"]); // Используем подготовленный запрос с параметрами

    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем результат в формате JSON
    echo json_encode($contacts);
} catch (PDOException $e) {
    // Обработка ошибок подключения к базе данных
    echo "Connection failed: " . $e->getMessage();
}

// Закрываем соединение
$conn = null;
?>
