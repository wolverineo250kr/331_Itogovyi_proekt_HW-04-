<?php
require_once 'engine/config.php';
require_once 'engine/dbFunctions.php';
session_start();

?>
<?php if (isset($_SESSION['user'])): ?>
    <?php
    die('Уже авторизован ' . $_SESSION['user']);
    ?>
<?php endif; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <?php
    require_once '_head.php';
    ?>
</head>
<body>
<main role="main">
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <a href="/">Главная</a>
                    <?php if (!isset($_SESSION['user'])): ?>
                        <a href="register.php">Регистрация</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="logout.php">Выйти</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">


                    <h1>Вход</h1>

                    <form action="/engine/login_process.php" method="post">
                        <input type="hidden" name="csrf_token" class="csfrD" value="">
                        <label for="login">Логин:</label>
                        <input type="text" name="login" id="login" required><br>

                        <label for="password">Пароль:</label>
                        <input type="password" name="password" id="password" required><br>

                        <input type="submit" class="btn btn-info" value="Войти">
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
<script src="/js/main.js"></script>
</html>
