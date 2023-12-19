<?php
// Обработка запроса на удаление сообщения
require_once 'config.php';
require_once 'dbFunctions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET["messageId"])) {
        echo json_encode(['error' => 'Failed to delete message']);
    }
    $messageId = $_GET["messageId"];

    $success = deleteMessage($messageId); // Предполагаем, что у вас есть функция deleteMessage в engine/dbFunctions.php

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete message']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
