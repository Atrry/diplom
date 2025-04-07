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
        <h1>Фиджитал Спорт</h1>
        <nav>
            <a href="../">Главная</a>
            <a href="../gallery/">Галерея</a>
            <a href="#">Новости</a>
            <a href="../contacts/">Контакты</a>
        </nav>
    </header>

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
                            <p><?= $item['заголовок']?></p>
                            <a href="news-detail.html" class="read-more">Читать далее</a>
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