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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление новости</title>
    <link rel="stylesheet" href="css/add_news.css">
</head>
<body>
    <div class="container">
        <h1>Добавить новость</h1>
        <form method="POST">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="заголовок" required>
            </div>
            
            <div class="form-group">
                <label for="content">Текст:</label>
                <textarea id="content" name="контент" required></textarea>
            </div>
            
            <button type="submit" class="btn">Добавить новость</button>
        </form>
    </div>
</body>
</html>