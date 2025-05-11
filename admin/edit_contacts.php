<?php
// Защита и проверки как в основном файле
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $sql->real_escape_string($_POST['address']);
    $phone = $sql->real_escape_string($_POST['phone']);
    $email = $sql->real_escape_string($_POST['email']);
    
    // Обновляем контакты
    $sql->query("UPDATE контакты SET 
                адрес = '$address',
                телефон = '$phone',
                email = '$email' 
                LIMIT 1");
    
    header('Location: dashboard.php');
    exit();
}

$contactsQuery = $sql->query('SELECT * FROM контакты LIMIT 1');
$contacts = $contactsQuery->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование контактов</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <header>
        <h1>РЕДАКТИРОВАНИЕ КОНТАКТОВ</h1>
        <a href="dashboard.php" class="logout-button">Назад</a>
    </header>
    
    <main>
        <div class="container">
            <form method="POST">
                <div>
                    <label for="address">Адрес:</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($contacts['адрес']) ?>" required>
                </div>
                
                <div>
                    <label for="phone">Телефон:</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($contacts['телефон']) ?>" required>
                </div>
                
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($contacts['email']) ?>" required>
                </div>
                
                <button type="submit" class="save-button">Сохранить изменения</button>
            </form>
        </div>
    </main>
</body>
</html>