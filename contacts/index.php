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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Контакты - Фиджитал Спорт</title>
    <style>
        .copy-contact {
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: inline-block;
        }
        
        .copy-contact:hover {
            background-color: #f0f0f0;
        }
        
        .copy-contact::after {
            content: "Кликните, чтобы скопировать";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
            white-space: nowrap;
            pointer-events: none;
        }
        
        .copy-contact:hover::after {
            opacity: 1;
        }
        
        .copy-contact.copied {
            background-color: #d4edda;
        }
        
        .copy-contact.copied::after {
            content: "Скопировано!";
            background: #28a745;
        }
    </style>
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
                    <?php foreach ($contacts as $item): ?>
                    <p class="copy-contact" data-contact="<?= htmlspecialchars($item['адрес']) ?>"><?= $item['адрес'] ?></p>
                </div>
                <div class="contact-item">
                    <h3>Телефон</h3>
                    <p class="copy-contact" data-contact="<?= htmlspecialchars($item['телефон']) ?>"><?= $item['телефон'] ?></p>
                </div>
                <div class="contact-item">
                    <h3>Email</h3>
                    <p class="copy-contact" data-contact="<?= htmlspecialchars($item['email']) ?>"><?= $item['email'] ?></p>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="contact-form">
                <h3>Обратная связь</h3>
                <form id="feedbackForm" method="post">
                    <label for="name">Имя:</label>
                    <input pattern="[A-Za-zА-Яа-яЁё\s]+" type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="comment">Сообщение:</label>
                    <textarea id="comment" name="comment" rows="5" required></textarea>

                    <div class="g-recaptcha" data-sitekey="6LfaBjYrAAAAAMvnQATS33wgsM0Njv5oV86tqcB5"></div>
                    <button class="submit-btn" type="submit">Отправить</button>
                </form>
                <div id="responseMessage" style="margin-top: 15px;"></div>
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
    <script>
        // Ограничение ввода только букв для поля имени
        document.getElementById('name').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-zА-Яа-яЁё\s]/g, '');
        });

        // Функционал копирования контактов
        document.addEventListener('DOMContentLoaded', function() {
            const contacts = document.querySelectorAll('.copy-contact');
            
            contacts.forEach(contact => {
                contact.addEventListener('click', async function() {
                    const textToCopy = this.getAttribute('data-contact');
                    
                    try {
                        await navigator.clipboard.writeText(textToCopy);
                        
                        // Визуальная обратная связь
                        this.classList.add('copied');
                        setTimeout(() => {
                            this.classList.remove('copied');
                        }, 2000);
                        
                    } catch (err) {
                        console.error('Ошибка копирования: ', err);
                        
                        // Fallback для старых браузеров
                        const textarea = document.createElement('textarea');
                        textarea.value = textToCopy;
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        
                        this.classList.add('copied');
                        setTimeout(() => {
                            this.classList.remove('copied');
                        }, 2000);
                    }
                });
            });
        });

        // Обработка формы обратной связи
        $(document).ready(function() {
            $('#feedbackForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var recaptcha = grecaptcha.getResponse();
                
                if (recaptcha.length === 0) {
                    $('#responseMessage').html('<p style="color: red;">Пожалуйста, заполните капчу</p>');
                    return false;
                }
                
                $.ajax({
                    type: 'POST',
                    url: 'feedback.php',
                    data: formData,
                    success: function(response) {
                        if (response === 'success') {
                            $('#feedbackForm')[0].reset();
                            grecaptcha.reset();
                            $('#responseMessage').html('<p style="color: green;">Сообщение успешно отправлено!</p>');
                        } else {
                            $('#responseMessage').html('<p style="color: red;">' + response + '</p>');
                        }
                    },
                    error: function() {
                        $('#responseMessage').html('<p style="color: red;">Произошла ошибка при отправке формы</p>');
                    }
                });
            });
        });
    </script>
</body>
</html>