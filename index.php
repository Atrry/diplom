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
    <a href="#" class="logo"><h1>ССК "Хаски"</h1></a>
</header>
<nav>
    <a href="#">Главная</a>
    <a href="gallery/">Галерея</a>
    <a href="news-page/">Новости</a>
    <a href="contacts/">Контакты</a>
</nav>
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
<main>
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
    
    <div class="reg-for-tournamennt">
        <h1 class="title">ПОДРОБНОСТИ ТУРНИРОВ</h1>
    </div>
    <div class="tournaments-description">
        <?php
            $tournament_data = $sql ->query("SELECT t.*, tt.название_типа FROM турниры t JOIN типы_турниров tt ON t.id_типа = tt.id_типа;");
            $tournament_data_result = $tournament_data -> fetch_all(MYSQLI_ASSOC);

            foreach ($tournament_data_result as $item):
        ?>
            <details>
            <summary><?=$item['название_турнира']?></summary>
            <p><?=$item['описание']?></p>
            <p>Вид спорта: <?=$item['название_типа']?></p>
            </details>
            <?php endforeach; ?>
    </div>
    <div class="reg-for-tournamennt">
        <h1 class="title">Турниры</h1>
    </div>
    <?php
            foreach ($tournament_data_result as $item):
        ?>
    <div class="reg-for-tournamennt">
        <div class="tournament-block">
        <div class="tournament-header">
            <h3 class="tournament-title"><?= $item['название_турнира']?></h3>
        </div>
        <div class="tournament-body">
            <div class="tournament-dates">
                <span><?= $item['дата_начала']?></span>
                <span>-</span>
                <span><?= $item['дата_окончания']?></span>
            </div>
            <span class="tournament-status status-registration"><?= $item['статус']?></span>
        </div>
        <div class="tournament-footer">
            <a href="tournament/tournament-detail.php?id=<?= $item['id_турнира']?>" class="view-link">Подробнее →</a>
        </div>
    </div>
    </div>
    <?php endforeach; ?>
</main>
<footer>
</footer>
<script src="js/slider.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js"></script>
<script src="js/tournament.js"></script>
</body>
</html>