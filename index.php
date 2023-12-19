<?php
require_once 'engine/config.php';
require_once 'engine/dbFunctions.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['user'];
$userData = getUserObjectByEmail($userEmail);
?>
<!DOCTYPE html>
<html>
<head>
    <title>messanger</title>
    <?php
    require_once '_head.php';
    ?>
</head>
<body>
<main role="main">
    <div class="jumbotron">
        <div class="container">

            <div id="whoisChat"></div>
            <?php if (isset($_SESSION['user'])): ?>
                <span class="textright" style="display: inline-block">Вы: <?= $userData['nickname'] ?>
                    (<?= $_SESSION['user'] ?>) </span>
            <?php endif; ?>
            <hr/>
            <div class="row">
                <div class="col-md-3">
                    <input type="text" id="nickname" class="nickname" name="nickname" required="required">
                    <button class="nickname" onclick="searchContacts()">поиск контактов</button>

                    <div id="result"></div>
                    <div class="row">
                        <hr/>
                        <div class="col-md-12">
                            <ul id="listUsers">

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="chatMessages" class="chat-messages"></div>
                    <input type="text" id="messageInput" placeholder="Наберите Ваше сообщение здесь">
                    <button class="send-message" onclick="sendMessage()"><img src="/images/button.svg" alt="Send"></button>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4">
                            <?php
                            require_once '_rightMenu.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
<script src="/components/fancybox/source/jquery.fancybox.js"></script>
<script src="/js/main.js"></script>
</html>
