<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['заголовок'];
    $url = $_POST['url'];

    // Добавление фотографии в галерею
    $stmt = $sql->prepare("INSERT INTO галерея (заголовок, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $url);
    $stmt->execute();

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить фото</title>
    <link rel="stylesheet" href="css/add_photo.css">
</head>
<body>
    <div class="container">
        <h1>Добавить фото</h1>
        <form method="POST" id="photoForm">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="заголовок" required>
            </div>
            
            <div class="form-group">
                <label for="url">URL фото:</label>
                <input type="text" id="url" name="url" required 
                       placeholder="https://example.com/image.jpg">
            </div>
            
            <div class="image-preview" id="imagePreview">
                <img src="" alt="Предпросмотр">
            </div>
            
            <button type="submit">Добавить</button>
        </form>
    </div>

    <script>
        // Скрипт для предпросмотра изображения
        document.getElementById('url').addEventListener('input', function() {
            const url = this.value.trim();
            const preview = document.getElementById('imagePreview');
            const img = preview.querySelector('img');
            
            if (url) {
                img.src = url;
                preview.style.display = 'block';
                
                // Проверка загрузки изображения
                img.onload = function() {
                    preview.style.display = 'block';
                }
                
                img.onerror = function() {
                    preview.style.display = 'none';
                }
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>