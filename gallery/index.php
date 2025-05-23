<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Галерея - Фиджитал Спорт</title>
</head>
<body>
<header>
    <a href="../index.php" class="logo"><h1>Фиджитал Спорт</h1></a>
</header>
<nav>
    <a href="../index.php">Главная</a>
    <a href="#">Галерея</a>
    <a href="../news-page/">Новости</a>
    <a href="../contacts/">Контакты</a>
</nav>
    <main>
        <section class="gallery-section">
            <h2>ГАЛЕРЕЯ</h2>
            <div class="gallery-grid">

                <?php
                    require '../admin/config.php';

                    $query=$sql->query('SELECT * FROM галерея');
                    $result=$query->fetch_all(MYSQLI_ASSOC);
                
                    foreach($result as $item): ?> 
                <div class="gallery-item">
                    <img src="<?= $item['URL']?>" class="gallery-image">
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
    </footer>

    <div id="lightbox" class="lightbox">
        <span class="close">&times;</span>
        <span class="prev">&#10094;</span>
        <span class="next">&#10095;</span>
        <img class="lightbox-content" id="lightbox-img">
    </div>

    <script src="js/script.js"></script>
</body>
</html>