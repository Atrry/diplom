<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ПАНЕЛЬ АДМИНИСТРАТОРА</title>
    <link rel="stylesheet" href="css/reset.css">
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/grid.css">
</head>
<body>
<header>
    <a href="dashboard.php" class="nav-btn">Назад</a>
    <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
    <a href="?logout" class="logout-button">Выйти</a>
</header>
<main>
<div class="container-grid">
    <select id="tournamentSelector" class="tournament-selector">
        <option value="">Выберите турнир</option>
    </select>
    <div class="bracket-container">
        <div id="bracket"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js"></script>
<script src="js/script.js"></script>
</main>
</body>
</html>