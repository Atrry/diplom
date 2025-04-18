<?php
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

require 'config.php';

if (isset($_SESSION['admin'])) {
    if (!isset($_SESSION['ip']) || $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    $_SESSION['last_activity'] = time();
} else {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity'] = time();
}

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    session_destroy();
    
    setcookie('admin_logged_in', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);

    header('Location: index.php');
    exit();
}

$newsQuery = $sql->query('SELECT * FROM новости ORDER BY id DESC');
$news = $newsQuery->fetch_all(MYSQLI_ASSOC);

$galleryQuery = $sql->query('SELECT * FROM галерея ORDER BY id DESC');
$gallery = $galleryQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ПАНЕЛЬ АДМИНИСТРАТОРА</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="header">
        <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
        <a href="?logout" class="logout-button">Выйти</a>
    </div>
    
    <a href="add_news.php">Добавить новость</a>
    <a href="../news-page/" class="check-button">Перейти на страницу "Новости"</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Фото</th>
            <th>Заголовок</th>
            <th>Действия</th>
            <th>Дата создания</th>
        </tr>

        <?php foreach ($news as $item): ?>
        <tr>
            <td><?= $item['id'] ?></td>
            <td><img src="<?= $item['фото'] ?>" width="600"></td>
            <td><?= $item['заголовок'] ?></td>
            <td><?= $item['контент'] ?></td>
            <td><?= $item['дата_создания'] ?></td>
            <td>
                <a href="edit_news.php?id=<?= $item['id'] ?>">Редактировать</a>
                <a href="delete_news.php?id=<?= $item['id'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Галерея</h2>
    <a href="add_photo.php">Добавить фото</a>
    <a href="../gallery/" class="check-button">Перейти на страницу "Галерея"</a>
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
