<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $sql->real_escape_string($_POST['заголовок']);
    $fileName = '';

    // Обработка загрузки фото
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/gallery/';
        $fileName = basename($_FILES['photo']['name']);
        $filePath = $uploadDir . $fileName;
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
            $fileName = '/uploads/gallery/' . $fileName;
        }
    }

    $stmt = $sql->prepare("INSERT INTO галерея (заголовок, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $fileName);
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
    <style>
        .preview {
            max-width: 200px;
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Добавить фото</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="заголовок" required>
            </div>
            
            <div class="form-group">
                <label for="photo">Фото:</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>
                <img id="photoPreview" class="preview" src="" alt="Предпросмотр">
            </div>
            
            <button type="submit">Добавить</button>
        </form>
    </div>

    <script>
        document.getElementById('photo').addEventListener('change', function(e) {
            const preview = document.getElementById('photoPreview');
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(e.target.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>