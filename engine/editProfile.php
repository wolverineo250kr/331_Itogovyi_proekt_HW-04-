<?php
require_once 'config.php';
require_once 'dbFunctions.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';

    if (!validateCsrfToken($clientToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'А CSRF токен то не настоящий!']);
        exit();
    }

    $userEmail = $_SESSION['user'];
    $userData = getUserObjectByEmail($userEmail);
    try {
        $conn = connectDbPdo();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $newNickname = $_POST['nickname'];
            if (!empty($newNickname)) {

                $isNicknameAvailable = isNicknameAvailable($conn, $newNickname, $userEmail, $userData['id']);

                if (!$isNicknameAvailable) {
                    echo json_encode(['error' => 'nickname уже занят. Выберите другой. ок ? ']);
                    exit();
                } else {
                    updateNickname($conn, $newNickname, $userEmail);
                }
            }

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $avatarPath = handleAvatarUpload($_FILES['avatar']);
                updateAvatar($conn, $avatarPath, $userEmail);
            }

            $isEmailHidden = isset($_POST['is_email_hidden']) ? 1 : 0;
            updateEmailVisibility($conn, $isEmailHidden, $userEmail);

            echo json_encode(['success' => true]);
            exit();
        }

        $userData = getUserData($conn, $userEmail);

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
        exit();
    } finally {
        $conn = null;
    }
}

/**
 * Проверяет, доступен ли указанный nickname для использования.
 *
 * @param PDO $conn         Объект PDO для соединения с базой данных.
 * @param string $nickname  Проверяемый nickname.
 * @param string $userEmail Email текущего пользователя.
 * @param int|null $userId  ID текущего пользователя.
 * @return bool             Возвращает true, если nickname доступен, и false в противном случае.
 */
function isNicknameAvailable($conn, $nickname, $userEmail, $userId = null) {
    // Используем параметры для игнорирования текущего пользователя
    $sql = "SELECT COUNT(*) AS count FROM users WHERE nickname = :nickname AND email != :user_email";

    // Если указан ID текущего пользователя, добавляем его к условию
    if ($userId !== null) {
        $sql .= " AND id != :user_id";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nickname', $nickname);
    $stmt->bindParam(':user_email', $userEmail);

    // Если указан ID текущего пользователя, привязываем его к параметру
    if ($userId !== null) {
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['count'] == 0;
}

function updateNickname($conn, $newNickname, $userEmail)
{
    $sql = "UPDATE users SET nickname = :newNickname WHERE email = :userEmail";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':newNickname', $newNickname);
    $stmt->bindParam(':userEmail', $userEmail);
    $stmt->execute();
}

function handleAvatarUpload($avatarFile)
{
    $uploadDir = 'avatars/';
    $uploadedFilePath = $uploadDir . basename($avatarFile['name']);

    move_uploaded_file($avatarFile['tmp_name'], $uploadedFilePath);

    return $uploadedFilePath;
}

function updateAvatar($conn, $avatarPath, $userEmail)
{
    $sql = "UPDATE users SET avatar = :avatarPath WHERE email = :userEmail";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':avatarPath', $avatarPath);
    $stmt->bindParam(':userEmail', $userEmail);
    $stmt->execute();
}

function updateEmailVisibility($conn, $isEmailHidden, $userEmail)
{
    $sql = "UPDATE users SET is_email_hidden = :isEmailHidden WHERE email = :userEmail";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':isEmailHidden', $isEmailHidden, PDO::PARAM_INT);
    $stmt->bindParam(':userEmail', $userEmail);
    $stmt->execute();
}

function getUserData($conn, $userEmail)
{
    $sql = "SELECT * FROM users WHERE email = :userEmail";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userEmail', $userEmail);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
