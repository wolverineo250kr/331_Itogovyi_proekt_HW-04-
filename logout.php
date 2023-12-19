<?php
require_once 'engine/config.php';
require_once 'engine/dbFunctions.php';

session_start();

unset($_SESSION['user']);

header("Location: /");
exit();
?>