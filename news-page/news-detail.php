<?php
require '../admin/config.php';

$news_id = (int)($_GET['id'] ?? 0);
if ($news_id <= 0) {
    die('Неверный ID новости');
}

$stmt = $sql->prepare('SELECT * FROM новости WHERE id = ?');
$stmt->bind_param('i', $news_id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    die("Новость не найдена!");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/news-detail.css">
    <title>Новости - Фиджитал Спорт</title>
</head>
<body>
<header>
    <a href="../index.php" class="logo"><h1>ССК "Хаски"</h1></a>
</header>
<nav>
    <a href="../index.php">Главная</a>
    <a href="../gallery/">Галерея</a>
    <a href="../news-page/index.php">Новости</a>
    <a href="../contacts/">Контакты</a>
</nav>
    
    <main class="container">
        <section class="news-detail">
            <div class="news-header">
                <h1><?=htmlspecialchars($news['заголовок'] ?? '')?></h1> <!-- Исправлено: правильный синтаксис массива -->
                <div class="news-meta">Опубликовано: <?=htmlspecialchars($news['дата_создания'] ?? '')?></div> <!-- Исправлено -->
            </div>
            
            <?php if (!empty($news['фото'])): ?>
                <img src="<?=htmlspecialchars($news['фото'])?>" class="news-image"> <!-- Исправлено -->
            <?php endif; ?>
            
            <div class="news-content">
                <p><?=$news['контент']?></p>    
            </div>
            
            <a href="index.php" class="back-link">← Вернуться к списку новостей</a>
        </section>
    </main>
    
    <footer>
        <div class="container">
            
        </div>
    </footer>
</body>
</html>