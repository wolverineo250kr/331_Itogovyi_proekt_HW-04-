<?php
// db_functions.php

// Функция для подключения к базе данных
function connectDb()
{
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;

    $conn = mysqli_connect($host, $user, $password, $database);

    if (!$conn) {
        die("Ошибка подключения к базе данных: " . mysqli_connect_error());
    }

    return $conn;
}

// Функция для подключения к базе данных PDO
function connectDbPdo()
{
    // Подключение к базе данных
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }

    return $pdo;
}

function getUserIdByEmail($email)
{
    try {
        // Подключаемся к базе данных
        $conn = connectDbPdo();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем SQL-запрос для получения идентификатора пользователя по email
        $sqlGetUserId = "SELECT id FROM users WHERE email = :email";
        $stmtGetUserId = $conn->prepare($sqlGetUserId);
        $stmtGetUserId->bindParam(':email', $email);
        $stmtGetUserId->execute();

        // Получаем результат запроса
        $userData = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return $userData['id'];
        } else {
            return null;
        }
    } catch (PDOException $e) {
        // Если произошла ошибка, возвращаем null
        return null;
    } finally {
        // Закрываем соединение
        $conn = null;
    }
}

function getUserObjectByEmail($email)
{
    try {
        // Подключаемся к базе данных
        $conn = connectDbPdo();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем SQL-запрос для получения идентификатора пользователя по email
        $sqlGetUserId = "SELECT id,avatar,email,nickname,avatar,is_email_hidden FROM users WHERE email = :email";
        $stmtGetUserId = $conn->prepare($sqlGetUserId);
        $stmtGetUserId->bindParam(':email', $email);
        $stmtGetUserId->execute();

        // Получаем результат запроса
        $userData = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return $userData;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        // Если произошла ошибка, возвращаем null
        return null;
    } finally {
        // Закрываем соединение
        $conn = null;
    }
}

function deleteMessage($messageId)
{
    if (!$messageId) {
        return false;
    }
    try {
        // Подключаемся к базе данных
        $conn = connectDbPdo();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Подготовленный запрос для удаления сообщения по его ID
        $stmt = $conn->prepare('DELETE FROM messages WHERE id = :messageId');
        $stmt->bindParam(':messageId', $messageId, PDO::PARAM_INT);
        $stmt->execute();

        return true; // Возвращаем true, если удаление успешно
    } catch (PDOException $e) {
        // Обработка ошибки базы данных
        return false; // Возвращаем false в случае ошибки
    }
}

function editMessage($messageId, $newContent)
{
    try {
        // Подготавливаем SQL-запрос для обновления текста сообщения
        $sql = "UPDATE messages SET content = :content WHERE id = :id";
        $conn = connectDbPdo();
        // Подготавливаем и выполняем запрос
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
        $stmt->bindParam(':content', $newContent, PDO::PARAM_STR);

        // Выполняем запрос
        $stmt->execute();

        // Проверяем успешность выполнения запроса
        if ($stmt->rowCount() > 0) {
            return ['success' => true];
        } else {
            return ['error' => 'No rows affected. Message may not exist.'];
        }
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }


}

function getMessageById($messageId)
{
    $conn = connectDbPdo();
    try {
        // Используйте подготовленные выражения, чтобы избежать SQL-инъекций
        $stmt = $conn->prepare("SELECT * FROM messages WHERE id = :messageId");
        $stmt->bindParam(':messageId', $messageId, PDO::PARAM_INT);
        $stmt->execute();

        // Получаем результат запроса
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        return $message;
    } catch (PDOException $e) {
        // Обработка ошибок базы данных
        return false;
    }
}

function sendMessage($forwarderId, $forwarderChatId, $messageContent)
{
    $conn = connectDbPdo();

    try {
        $receiverId = findUserIdInChat($forwarderChatId, $forwarderId);
        // Используйте подготовленные выражения, чтобы избежать SQL-инъекций
        $stmt = $conn->prepare("INSERT INTO messages (chat_id, user_id, content, created_at) VALUES (:forwarderChatId, :receiverId, :messageContent, NOW())");
        $stmt->bindParam(':forwarderChatId', $forwarderChatId, PDO::PARAM_INT);
        $stmt->bindParam(':receiverId', $receiverId, PDO::PARAM_INT);
        $stmt->bindParam(':messageContent', $messageContent, PDO::PARAM_STR);
        $stmt->execute();

        $insertedMessageId = $conn->lastInsertId();

        // Теперь, когда у вас есть ID нового сообщения, связываем его с чатом
       // linkMessageToChat($insertedMessageId, $forwarderId, $receiverId);

        return true;
    } catch (PDOException $e) {
        // Обработка ошибок базы данных
        return false;
    }
}

function findUserIdInChat($chatId, $userId) {
    $conn = connectDbPdo();

    try {
        $stmt = $conn->prepare("SELECT user_id FROM user_chat WHERE chat_id = :chatId AND user_id != :userId");
        $stmt->bindParam(':chatId', $chatId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['user_id'] : null;
    } catch (PDOException $e) {
        // Обработка ошибок базы данных
        return false;
    }
}

// Проверка токена CSRF
function validateCsrfToken($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
