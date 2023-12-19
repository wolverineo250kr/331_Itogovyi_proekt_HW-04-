<?php
require_once 'config.php';
require_once 'dbFunctions.php';

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Не авторизован']);
    die;
}

$userEmail = $_SESSION['user'];

try {
    $conn = connectDbPdo();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlGetUserId = "SELECT id FROM users WHERE email = :email";
    $stmtGetUserId = $conn->prepare($sqlGetUserId);
    $stmtGetUserId->bindParam(':email', $userEmail);
    $stmtGetUserId->execute();

    $userIdResult = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

    if (!$userIdResult) {
        echo json_encode(['error' => 'Нет пользователя']);
        die;
    }

    $userId = $userIdResult['id'];

    $sqlGetCommonChats = "SELECT c.id, c.name, u.avatar, u.nickname AS other_user_nickname
                          FROM chats c
                          JOIN user_chat uc ON c.id = uc.chat_id
                          JOIN user_chat other_uc ON c.id = other_uc.chat_id AND other_uc.user_id != :user_id
                          JOIN users u ON other_uc.user_id = u.id
                          WHERE uc.user_id = :user_id";
    $stmtGetCommonChats = $conn->prepare($sqlGetCommonChats);
    $stmtGetCommonChats->bindParam(':user_id', $userId);
    $stmtGetCommonChats->execute();

    $commonChats = $stmtGetCommonChats->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'commonChats' => $commonChats]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
