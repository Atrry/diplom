<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ПАНЕЛЬ АДМИНИСТРАТОРА</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<header>
    <a href="dashboard.php" class="nav-btn">Назад</a>
    <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
    <a href="?logout" class="logout-button">Выйти</a>
</header>
<main>
<style>
        /* Стили для редактируемых полей */
        .editable-team, .editable-score {
            cursor: pointer;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .editable-team:hover, .editable-score:hover {
            background: #f0f0f0;
        }
    </style>
    <div class="container-grid">
        <div id="bracket"></div>
        <button id="saveButton" style="margin: 20px auto; display: block;">Сохранить данные</button>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js"></script>
<script src="js/script.js"></script>
</main>
</body>
</html>