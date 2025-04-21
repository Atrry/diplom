<?php 
require_once('../phpmailer/PHPMailerAutoload.php');
$mail = new PHPMailer;
$mail->CharSet = 'utf-8';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Проверка полей
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['comment'])) {
            die('Заполните все поля!');
        }

        // Очистка данных
        $name = htmlspecialchars($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $comment = htmlspecialchars($_POST['comment']);

        // Проверка email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die('Некорректный email!');
        }

    $mail->isSMTP();
    $mail->Host = 'smtp-mail.outlook.com';
    $mail->SMTPAuth = true;
    $mail->Username = '22200827@live.preco.ru';
    $mail->Password = 'kdyKYAXM';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 587;

    $mail->setFrom('22200827@live.preco.ru');
    $mail->addAddress('22200827@live.preco.ru');
    $mail->isHTML(true);

    $mail->Subject = 'Заявка с тестового сайта';
    $mail->Body    = '' .$name . ' оставил заявку!';
    $mail->AltBody = '';

    if(!$mail->send()) {
        echo 'Error';
    } else {
        echo 'success';
    }
}
?>