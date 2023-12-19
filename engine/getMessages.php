<?php

require_once 'config.php';
require_once 'dbFunctions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['error' => 'User not authenticated']);
        die;
    }

    $chatId = $_GET['chatId'];

    $userEmail = $_SESSION['user'];
    $userId = getUserIdByEmail($userEmail);

    if (!$userId) {
        echo json_encode(['error' => 'User not found']);
        die;
    }

    try {
        $conn = connectDbPdo();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sqlGetMessages = "SELECT m.id, m.content, m.created_at, u.nickname, u.email , u.is_email_hidden 
                           FROM messages m
                           JOIN users u ON m.user_id = u.id
                           WHERE m.chat_id = :chat_id
                           ORDER BY m.created_at ASC";
        $stmtGetMessages = $conn->prepare($sqlGetMessages);
        $stmtGetMessages->bindParam(':chat_id', $chatId);
        $stmtGetMessages->execute();

        $messages = $stmtGetMessages->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as $index => $value) {
            if ($messages[$index]["is_email_hidden"] === '1') {
                $messages[$index]["email"] = 'email скрыт';
            }
        }

        echo json_encode(['success' => true, 'messages' => $messages]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } finally {
        $conn = null;
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
