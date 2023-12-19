<?php
session_start();

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

$csrfToken = ['csrf_token' => generateCsrfToken()];

header('Content-Type: application/json');

echo json_encode($csrfToken);
?>