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
    <title>Регистрация</title>
    <?php
    require_once '_head.php';
    ?>
</head>
<body>
<main role="main">
    <div class="jumbotron">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <a href="/">Главная</a>
                    <a href="login.php">Вход</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">


                    <h1>Регистрация</h1>

                    <form action="engine/register_process.php" method="post">
                        <input type="hidden" name="csrf_token" class="csfrD" value="">
                        <label for="login">Логин:</label>
                        <input type="text" name="login" id="login" required><br>

                        <label for="password">Пароль:</label>
                        <input type="password" name="password" id="password" required><br>

                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required><br>

                        <input type="submit" class="btn btn-info" value="Зарегистрироваться">
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
<script src="/js/main.js"></script>
</html>
