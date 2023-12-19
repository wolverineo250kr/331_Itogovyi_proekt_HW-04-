<?php

require 'vendor/autoload.php';

function sendMail($email, $login){
    $yourEmail = 'ВАШ_МАЙЛ@gmail.com';
    $yourAppPassword = 'НЕКИЙ_ПАРОЛЬ';

    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
        ->setUsername($yourEmail)
        ->setPassword($yourAppPassword);

    $mailer = new Swift_Mailer($transport);

    $message = (new Swift_Message('По поводу регистрации'))
        ->setFrom([$yourEmail => 'messanger регистрация успешно'])
        ->setTo([$email => $login])
        ->setBody('Регистрация успешно');

    $result = $mailer->send($message);

    return $result;
}
