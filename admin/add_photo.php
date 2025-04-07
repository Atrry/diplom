<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['заголовок'];
    $url = $_POST['url'];

    // Добавление фотографии в галерею
    $stmt = $sql->prepare("INSERT INTO галерея (заголовок, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $url);
    $stmt->execute();

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить фото</title>
</head>
<body>
    <h1>Добавить фото</h1>
    <form method="POST">
        <label>Заголовок:</label>
        <input type="text" name="заголовок" required><br>
        
        <label>URL фото:</label>
        <input type="text" name="url" required><br>
        
        <button type="submit">Добавить</button>
    </form>
</body>
</html>