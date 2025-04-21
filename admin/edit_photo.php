<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'];

// Get photo data
$stmt = $sql->prepare("SELECT * FROM галерея WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$photo = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['заголовок'];
    $url = $_POST['url'];

    // Update photo
    $stmt = $sql->prepare("UPDATE галерея SET заголовок = ?, url = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $url, $id);
    $stmt->execute();

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать фото</title>
    <link rel="stylesheet" href="css/add_photo.css">
</head>
<body>
    <h1>Редактировать фото</h1>
    <form method="POST">
        <label>Заголовок:</label>
        <input type="text" name="заголовок" value="<?= $photo['заголовок'] ?>" required><br>
        
        <label>URL фото:</label>
        <input type="text" name="url" value="<?= $photo['URL'] ?>" required><br>
        
        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
