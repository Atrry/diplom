<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$newsQuery = $sql->query('SELECT * FROM новости ORDER BY id DESC'); // Исправлено на ORDER BY id DESC
$news = $newsQuery->fetch_all(MYSQLI_ASSOC);

$galleryQuery = $sql->query('SELECT * FROM галерея ORDER BY id DESC'); // Исправлено на ORDER BY id DESC
$gallery = $galleryQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ПАНЕЛЬ АДМИНИСТРАТОРА</title>
</head>
<body>
    <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
    <a href="add_news.php">Добавить новость</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Действия</th>
        </tr>

        <?php foreach ($news as $item): ?>
        <tr>
            <td><?= $item['id'] ?></td>
            <td><?= $item['заголовок'] ?></td>
            <td><?= $item['контент'] ?></td>
            <td>
                <a href="edit_news.php?id=<?= $item['id'] ?>">Редактировать</a>
                <a href="delete_news.php?id=<?= $item['id'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Галерея</h2>
    <a href="add_photo.php">Добавить фото</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Фото</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($gallery as $item): ?>
        <tr>
            <td><?= $item['id'] ?></td>
            <td><?= $item['заголовок'] ?></td>
            <td><img src="<?= $item['URL'] ?>" alt="<?= $item['заголовок'] ?>" width="600"></td>
            <td>
                <a href="edit_photo.php?id=<?= $item['id'] ?>">Редактировать</a>
                <a href="delete_photo.php?id=<?= $item['id'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>