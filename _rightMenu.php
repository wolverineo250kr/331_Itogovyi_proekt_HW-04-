<a href="/">Главная</a>
<?php if (!isset($_SESSION['user'])): ?>
    <a href="login.php">Войти</a>
    <a href="register.php">Регистрация</a>
<?php endif; ?>
<?php if (isset($_SESSION['user'])): ?>
    <a href="edit_profile_page.php">Профиль</a>
    <a href="logout.php" class="exit">Выйти</a>
<?php endif; ?>