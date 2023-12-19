<?php
// Обработка запроса на редактирование сообщения
require_once 'config.php';
require_once 'dbFunctions.php';

session_start();
$_POST = file_get_contents('php://input');
$_POST = json_decode($_POST, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = $_POST['messageId'];
    $newContent = $_POST['newContent'];

    $success = editMessage($messageId, $newContent);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete message']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
