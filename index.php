<?php
require 'admin/config.php';

$stmt = $sql->prepare('SELECT * FROM новости ORDER BY дата_создания DESC LIMIT 4');
$stmt->execute();
$result = $stmt->get_result();
$newsItems = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Фиджитал Спорт</title>
</head>
<body>
<header>
    <h1>Фиджитал Спорт</h1>
</header>
<nav>
    <a href="#">Главная</a>
    <a href="gallery/">Галерея</a>
    <a href="news-page/">Новости</a>
    <a href="contacts/">Контакты</a>
</nav>
<main>
    <div class="slider">
        <div class="slides">
            <div class="slide" style="background-image: url(photo/1.png);"></div>
            <div class="slide" style="background-image: url(photo/2.jpg);"></div>
            <div class="slide" style="background-image: url(photo/3.jpeg);"></div>
        </div>
        <div class="controls">
            <button class="prev">&#10094;</button>
            <button class="next">&#10095;</button>
        </div>
        <div class="dots">
            <span class="dot active" data-slide="0"></span>
            <span class="dot" data-slide="1"></span>
            <span class="dot" data-slide="2"></span>
        </div>
    </div>
    <div class="news-container">
        <h2 class="title">АКТУАЛЬНЫЕ НОВОСТИ</h2>
        <div class="news-grid">
            <?php foreach ($newsItems as $news): ?>
                <div class="news-item">
                    <a href="news-page/news-detail.php?id=<?= $news['id'] ?>">
                        <div class="news-image" style="background-image: url('<?= $news['фото'] ?>');"></div>
                        <div class="news-content">
                            <h3><?= $news['заголовок'] ?></h3>
                            <p><?= mb_substr($news['контент'], 0, 100) ?>...</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<footer>
</footer>
<script src="js/slider.js"></script>
</body>
</html>