<?php
require '../admin/config.php';

$contactsQuery = $sql->query('SELECT * FROM контакты');
$contacts = $contactsQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://api-maps.yandex.ru/2.1/?apikey=YOUR_API_KEY&lang=ru_RU"></script>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <title>Контакты - Фиджитал Спорт</title>
</head>
<body>
<header>
    <a href="../index.php" class="logo"><h1>Фиджитал Спорт</h1></a>
</header>
<nav>
    <a href="../index.php">Главная</a>
    <a href="../gallery/">Галерея</a>
    <a href="../news-page/">Новости</a>
    <a href="../contacts/">Контакты</a>
</nav>
    <main>
        <section class="contact-section">
            <h2>Контакты</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <h3>Адрес</h3>
                    <?php foreach ($contacts as $item):?>
                    <p><?= $item['адрес']?></p>
                </div>
                <div class="contact-item">
                    <h3>Телефон</h3>
                    <p><?= $item['телефон']?></p>
                </div>
                <div class="contact-item">
                    <h3>Email</h3>
                    <p><?= $item['email']?></p>
                    <?php endforeach;?>
                </div>
            </div>

            <div class="contact-form">
                <h3>Обратная связь</h3>
                <form action="feedback.php" method="post">
                    <label for="name">Имя:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="comment">Сообщение:</label>
                    <textarea id="comment" name="comment" rows="5" required></textarea>

                    <div class="g-recaptcha" data-sitekey="6LfaBjYrAAAAAMvnQATS33wgsM0Njv5oV86tqcB5"></div>
                    <button type="submit">Отправить</button>
                </form>
            </div>

            <div class="map">
                <h3>Расположение на карте</h3>
                <div id="yandex-map" style="width: 100%; height: 400px;"></div>
            </div>
        </section>
    </main>

    <footer>
    </footer>
    <script src="js/map.js"></script>
</body>
</html>