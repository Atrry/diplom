<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Новости - Фиджитал Спорт</title>
</head>
<body>
<header>
    <a href="../index.php" class="logo"><h1>Фиджитал Спорт</h1></a>
</header>
<nav>
    <a href="../index.php">Главная</a>
    <a href="../gallery/">Галерея</a>
    <a href="#">Новости</a>
    <a href="../contacts/">Контакты</a>
</nav>

    <main>
        <section class="news-section">
            <h2>НОВОСТИ</h2>
            <div class="news-grid">
                <?php
                    require '../admin/config.php';
                    $query=$sql->query('SELECT * FROM новости');
                    $result=$query->fetch_all(MYSQLI_ASSOC);

                     foreach($result as $item): ?> 
                        <div class="news-item">
                            <div class="news-image" style="background-image: url('<?= $item['фото']?>');"></div>
                            <div class="news-content">
                                <p class="title"><?= $item['заголовок']?></p>
                                <div class="container"> <!-- спросить у миши как это лучше обернуть?? -->
                                    <a href="news-detail.php?id=<?= $item['id']?>" class="read-more">Читать далее</a>
                                    <p id="date"><?= date('d-m-Y', strtotime($item['дата_создания']))?></p>
                                </div>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
    </footer>
</body>
</html>