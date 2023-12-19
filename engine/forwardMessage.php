<?php
// Обработка запроса на пересылку сообщения
require_once 'config.php';
require_once 'dbFunctions.php';
session_start();

$_POST = file_get_contents('php://input');
$_POST = json_decode($_POST, true);

$messageId = $_POST["messageId"]; // ID пересылаемого сообщения
$userEmail = $_SESSION['user'];
$forwarderId = getUserIdByEmail($userEmail);
$forwarderChatId = $_POST["forwarderChatId"];

// Получаем сообщение по ID
$message = getMessageById($messageId);

if ($message) {
    $forwardedMessage = 'Перенаправленное сообщение <pre>' . $message['content'] . '</pre>';

    sendMessage($forwarderId, $forwarderChatId, $forwardedMessage);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Message not found']);
}
?>