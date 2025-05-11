<?php 
require_once('../phpmailer/src/PHPMailer.php');
require_once('../phpmailer/src/SMTP.php');
require_once('../phpmailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->CharSet = 'utf-8';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['comment'])) {
        die('Заполните все поля!');
    }

    $error = true;
    $secret = '6LfaBjYrAAAAAEfhXv5_CUa_3QUhaSZUPOYTG_Dx';
    if (!empty($_POST['g-recaptcha-response'])) {
        $curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
        $out = curl_exec($curl);
        curl_close($curl);
        $out = json_decode($out);
        if ($out->success == true) {
            $error = false;
        }
    }
    if ($error) {
        die('Ошибка заполнения капчи.');
    }

    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $comment = htmlspecialchars($_POST['comment']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Некорректный email!');
    }
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-mail.outlook.com';
        $mail->SMTPAuth = true;
        $mail->Username = '22200827@live.preco.ru';
        $mail->Password = 'kdyKYAXM';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('22200827@live.preco.ru', $name);
        $mail->addAddress('22200827@live.preco.ru');
        
        $mail->isHTML(true);
        $mail->Subject = 'Заявка с тестового сайта: ' . $name;
        $mail->Body = "
            <h3>Новая заявка</h3>
            <p><strong>Имя:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Сообщение:</strong><br>" . nl2br(htmlspecialchars($comment)) . "</p>
        ";
        $mail->AltBody = "Имя: $name\nEmail: $email\nСообщение:\n$comment";

        if($mail->send()) {
            echo 'success';
        }
    } catch (Exception $e) {
        echo "Ошибка отправки: {$mail->ErrorInfo}";
    }
}
?>