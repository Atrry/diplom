<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
<<<<<<< HEAD
    header('Location: index.php');
=======
    header('Location: login.php');
>>>>>>> ba75e01af8f40709fc7b64da51b8218d92825623
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
    $title = $_POST['заголовок'];
    $content = $_POST['контент'];

    $newsQuery = $sql->query("INSERT INTO новости (заголовок, контент, дата_создания) VALUES ('  $title  ', '  $content  ', CURRENT_DATE())");
=======
    $title = $_POST['title'];
    $content = $_POST['content'];

    $newsQuery = $sql->query("INSERT INTO новости (заголовок, контент) VALUES ('  $title  ', '  $content  ')");
>>>>>>> ba75e01af8f40709fc7b64da51b8218d92825623

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление</title>
</head>
<body>
    <h1>Добавить новость</h1>
    <form method="POST">
        <label>Заголовок:</label>
<<<<<<< HEAD
        <input type="text" name="заголовок" required><br>
        <label>Текст:</label>
        <textarea name="контент" required></textarea><br>
=======
        <input type="text" name="title" required><br>
        <label>Текст:</label>
        <textarea name="content" required></textarea><br>
>>>>>>> ba75e01af8f40709fc7b64da51b8218d92825623
        <button type="submit">Добавить новость</button>
    </form>
</body>
</html>