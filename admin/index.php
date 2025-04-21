<?php
session_start();
require 'config.php';

// Проверяем, есть ли активная сессия или cookie
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit();
}

// Проверяем cookie с запомненным входом
if (isset($_COOKIE['admin_logged_in'])) {
    $_SESSION['admin'] = true;
    header('Location: dashboard.php');
    exit();
}

// Обработка формы входа
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $result = $sql->query("SELECT * FROM администратор WHERE логин = '$login'");
    $user = $result->fetch_assoc();

    if ($user && $user['пароль'] === $password) {
        $_SESSION['admin'] = true;
        
        // Всегда устанавливаем куки при успешном входе
        setcookie('admin_logged_in', '1', time() + 60*60*24*30, '/'); // 30 дней
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Вход в панель администратора</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="login">Логин:</label>
                <input type="text" id="login" name="login" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-button">Войти</button>
        </form>
    </div>
</body>
</html>