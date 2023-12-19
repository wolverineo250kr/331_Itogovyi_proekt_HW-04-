<?php
require_once 'config.php';
require_once 'dbFunctions.php';

session_start();

if (!isset($_GET['contactId'])) {
    echo json_encode(['error' => 'Missing required parameter: contactId']);
    die;
}

$contactId = $_GET['contactId'];

try {
    $conn = connectDbPdo();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = $_SESSION['user'];

    $sqlGetUserId = "SELECT id FROM users WHERE email = :email";
    $stmtGetUserId = $conn->prepare($sqlGetUserId);
    $stmtGetUserId->bindParam(':email', $email);
    $stmtGetUserId->execute();

    $userData = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $userId = $userData['id'];
    } else {
        echo json_encode(['error' => 'User not found']);
        die;
    }

    $sqlCheckChat = "SELECT chat_id FROM user_chat WHERE user_id = :user_id AND chat_id IN (
                        SELECT chat_id FROM user_chat WHERE user_id = :contact_id
                    )";
    $stmtCheckChat = $conn->prepare($sqlCheckChat);
    $stmtCheckChat->bindParam(':user_id', $userId);
    $stmtCheckChat->bindParam(':contact_id', $contactId);
    $stmtCheckChat->execute();

    $existingChat = $stmtCheckChat->fetch(PDO::FETCH_ASSOC);

    if ($existingChat) {
        $sqlGetNickname = "SELECT nickname FROM users WHERE id = :user_id";
        $stmtGetNickname = $conn->prepare($sqlGetNickname);
        $stmtGetNickname->bindParam(':user_id', $userId);
        $stmtGetNickname->execute();
        $nicknameResult = $stmtGetNickname->fetch(PDO::FETCH_ASSOC);
        $nickname = $nicknameResult['nickname'];

        echo json_encode(['success' => true, 'chatId' => $existingChat['chat_id'], 'nickname' => $nickname]);
        die;
    }

    $conn->beginTransaction();

    $sqlInsertChat = "INSERT INTO chats (name, created_at, updated_at) VALUES (:name, NOW(), NOW())";
    $stmtInsertChat = $conn->prepare($sqlInsertChat);
    $stmtInsertChat->bindParam(':name', $chatName);
    $chatName = "Chat with $contactId";
    $stmtInsertChat->execute();

    $chatId = $conn->lastInsertId();

    $sqlInsertUserChat = "INSERT INTO user_chat (user_id, chat_id, created_at) VALUES (:user_id, :chat_id, NOW())";
    $stmtInsertUserChat = $conn->prepare($sqlInsertUserChat);
    $stmtInsertUserChat->bindParam(':user_id', $contactId);
    $stmtInsertUserChat->bindParam(':chat_id', $chatId);
    $stmtInsertUserChat->execute();

    $sqlInsertUserChatMine = "INSERT INTO user_chat (user_id, chat_id, created_at) VALUES (:user_id, :chat_id, NOW())";
    $stmtInsertUserChatMine = $conn->prepare($sqlInsertUserChatMine);
    $stmtInsertUserChatMine->bindParam(':user_id', $userId);
    $stmtInsertUserChatMine->bindParam(':chat_id', $chatId);
    $stmtInsertUserChatMine->execute();

    $conn->commit();

    echo json_encode(['success' => true, 'chatId' => $chatId, 'nickname' => $nickname]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
