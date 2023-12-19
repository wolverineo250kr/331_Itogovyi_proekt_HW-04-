<?php
require_once 'config.php';
require_once 'dbFunctions.php';

session_start();

$_POST = file_get_contents('php://input');
$_POST = json_decode($_POST, true);

if (!isset($_POST['chatId']) || !isset($_POST['content'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    die;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';

    if (!validateCsrfToken($clientToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit();
    }


    $chatId = $_POST['chatId'];
    $content = $_POST['content'];

    $conn = connectDbPdo();
    $sqlCheckChat = "SELECT id FROM chats WHERE id = :chatId";
    $stmtCheckChat = $conn->prepare($sqlCheckChat);
    $stmtCheckChat->bindParam(':chatId', $chatId);
    $stmtCheckChat->execute();
    $chatData = $stmtCheckChat->fetch(PDO::FETCH_ASSOC);

    if (!$chatData) {
        echo json_encode(['error' => 'Chat not found']);
        die;
    }

    try {
        $sqlInsertMessage = "INSERT INTO messages (chat_id, user_id, content, created_at) VALUES (:chatId, :userId, :content, NOW())";
        $stmtInsertMessage = $conn->prepare($sqlInsertMessage);

        session_start();
        $userId = getUserIdByEmail($_SESSION['user']);

        $stmtInsertMessage->bindParam(':chatId', $chatId);
        $stmtInsertMessage->bindParam(':userId', $userId);
        $stmtInsertMessage->bindParam(':content', $content);
        $stmtInsertMessage->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {

        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } finally {
        $conn = null;
    }
}
?>
