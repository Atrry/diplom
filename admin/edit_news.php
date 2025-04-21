<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'];

$stmt = $sql->prepare("SELECT * FROM новости WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $sql->prepare("UPDATE новости SET заголовок = ?, контент = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $id);
    $stmt->execute();

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать новость</title>
    <link rel="stylesheet" href="css/add_news.css">
</head>
<body>
    <h1>Редактировать новость</h1>
    <form method="POST">
        <label>Заголовок:</label>
        <input type="text" name="title" value="<?= $news['заголовок'] ?>" required><br>
        <label>Текст:</label>
        <textarea name="content" required><?= $news['контент'] ?></textarea><br>
        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
