<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['заголовок'];
    $content = $_POST['контент'];

    $newsQuery = $sql->query("INSERT INTO новости (заголовок, контент, дата_создания) VALUES ('  $title  ', '  $content  ', CURRENT_DATE())");

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
        <input type="text" name="заголовок" required><br>
        <label>Текст:</label>
        <textarea name="контент" required></textarea><br>
        <button type="submit">Добавить новость</button>
    </form>
</body>
</html>