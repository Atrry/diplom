<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $newsQuery = $sql->query("INSERT INTO новости (заголовок, контент) VALUES ('  $title  ', '  $content  ')");

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
        <input type="text" name="title" required><br>
        <label>Текст:</label>
        <textarea name="content" required></textarea><br>
        <button type="submit">Добавить новость</button>
    </form>
</body>
</html>