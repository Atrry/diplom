<?php
// Упрощенные настройки сессии
session_start([
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

require 'config.php';

// Проверяем безопасность сессии (если пользователь авторизован)
if (isset($_SESSION['admin'])) {
    // Проверяем таймаут неактивности (30 минут)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

// Если не админ — редирект (но только если это не страница входа)
if (!isset($_SESSION['admin']) && basename($_SERVER['PHP_SELF']) !== 'index.php') {
    header('Location: index.php');
    exit();
}

// Выход из системы
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

// Остальной код остается без изменений
$newsQuery = $sql->query('SELECT * FROM новости ORDER BY id DESC');
$news = $newsQuery->fetch_all(MYSQLI_ASSOC);

$galleryQuery = $sql->query('SELECT * FROM галерея ORDER BY id DESC');
$gallery = $galleryQuery->fetch_all(MYSQLI_ASSOC);

$sliderQuery = $sql->query('SELECT * FROM слайдер ORDER BY id DESC');
$slider = $sliderQuery->fetch_all(MYSQLI_ASSOC);

$contactsQuery = $sql->query('SELECT * FROM контакты');
$contacts = $contactsQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ПАНЕЛЬ АДМИНИСТРАТОРА</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <header>
        <div class="btn">
            <a href="#news-container" class="nav-btn">Новости</a>
            <a href="#gallery-container" class="nav-btn">Галерея</a>
            <a href="#slider-container" class="nav-btn">Слайдер</a>
            <a href="#contacts-container" class="nav-btn">Контакты</a>
            <a href="#admin" class="nav-btn">Ссылки</a>
            <a href="tournament.php">Заявки</a>
            <a href="create_tournament.php">Турниры</a>
            <a href="grid_edit.php">Турнирная сетка</a>
        </div>
        <h1></h1>
        <a href="?logout" class="logout-button">Выйти</a>
    </header>
    
    <main>
        <div class="container">
            <h2 id="news-container">Новости</h2>
            <a href="add_news.php" id="news-container">Добавить новость</a>
            <a href="../news-page/" class="check-button">Перейти на страницу "Новости"</a>
            <table border="1" class="news-table">
                <tr>
                    <th>ID</th>
                    <th>Фото</th>
                    <th>Заголовок</th>
                    <th>Контент</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
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
        </div>

        <div class="container">
            <h2 id="gallery-container">Галерея</h2>
            <a href="add_photo.php">Добавить фото</a>
            <a href="../gallery/" class="check-button">Перейти на страницу "Галерея"</a>
            <table border="1" class="gallery-table">
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
        </div>
        <div class="container">
            <h2 id="slider-container">Слайдер</h2>
            <a href="add_slider.php">Добавить фото</a> <!-- ПОПРАВИТЬ ЗДЕСЬ: ссылку на добавление слайдера -->
            <a href="../" class="check-button">Перейти на страницу "Главная"</a>
            <table border="1" class="slider-table">
                <tr>
                    <th>ID</th>
                    <th>Фото</th>
                    <th>Действия</th>
                </tr>
                <?php foreach ($slider as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><img src="<?= $item['фото'] ?>" width="600"></td>
                    <td>
                        <a href="edit_slider.php?id=1">Редактировать</a> <!-- ПОПРАВИТЬ ЗДЕСЬ: ссылку редактирования -->
                        <a href="delete_slider.php?id=1" onclick="return confirm('Вы уверены?')">Удалить</a> <!-- ПОПРАВИТЬ ЗДЕСЬ: ссылку удаления -->
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
                <!-- ИЗМЕНЕНО: Добавлен контейнер для редактирования контактов -->
        <div class="container">
            <h2 id="contacts-container">Контакты</h2>
            <a href="../contacts" class="check-button">Перейти на страницу "Контакты"</a>
            <table border="1" class="contacts-table">
                <tr>
                    <th>Адрес</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Действия</th>
                </tr>
                <?php foreach ($contacts as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['адрес'] ?? 'Не указан') ?></td>
                    <td><?= htmlspecialchars($item['телефон'] ?? 'Не указан') ?></td>
                    <td><?= htmlspecialchars($item['email'] ?? 'Не указан') ?></td>
                    <td><a href="edit_contacts.php">Редактировать контакты</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="container">
            <h2 id="admin">Административные ресурсы</h2>
            <div>
                <a href="https://pma.phygitalnews.ru/" target="_blank">phpMyAdmin</a> <!-- ПОПРАВИТЬ ЗДЕСЬ: ссылку на pma -->
                <a href="https://npm.phygitalnews.ru/" target="_blank">NPM</a> <!-- ПОПРАВИТЬ ЗДЕСЬ: ссылку на npm -->
            </div>
        </div>
    </main>
</body>
</html>
