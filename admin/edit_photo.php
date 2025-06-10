<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Обработка загрузки фото
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $title = $sql->real_escape_string($_POST['заголовок']);
    
    // Обработка файла
    $fileName = $photo['URL']; // Сохраняем текущее фото по умолчанию
    
    if (isset($_POST['delete_photo'])) {
        // Удаляем фото (оставляем поле пустым)
        $fileName = '';
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        // Загружаем новое фото
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/gallery/';
        
        // Сохраняем оригинальное имя файла
        $fileName = basename($_FILES['photo']['name']);
        $filePath = $uploadDir . $fileName;
        
        // Проверяем и создаем директорию, если ее нет
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        move_uploaded_file($_FILES['photo']['tmp_name'], $filePath);
        
        // Формируем полный URL для сохранения в БД
        $fileName = '/uploads/gallery/' . $fileName;
    }
    
    // Обновляем запись
    $stmt = $sql->prepare("UPDATE галерея SET заголовок = ?, URL = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $fileName, $id);
    $stmt->execute();
    
    header('Location: dashboard.php');
    exit();
}

// Получение данных фото
$id = (int)$_GET['id'];
$stmt = $sql->prepare("SELECT * FROM галерея WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$photo = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать фото</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { 
            font-family: Arial; max-width: 800px; margin: 20px auto; 
        }
        input, textarea { 
            width: 100%; margin: 5px 0; 
        }
        textarea { 
            height: 200px; 
        }
        .preview { 
            max-width: 200px; display: <?= $photo['URL']?> ? 'block' : 'none'; 
        }
        .delete-btn { 
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Редактировать фото</h1>
    <?= isset($_GET['success']) ? '<p style="color:green">Сохранено!</p>' : '' ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $photo['id'] ?>">
        
        <label>Заголовок:</label>
        <input type="text" name="заголовок" value="<?= htmlspecialchars($photo['заголовок']) ?>" required><br>
        
        <label>Фото:</label>
        <input type="file" name="photo" id="photoInput">
        
        <div id="photoPreview">
            <?php if ($photo['URL']): ?>
                <img src="<?= htmlspecialchars($photo['URL']) ?>" class="preview">
                <button type="submit" name="delete_photo" value="1" class="delete-btn">Удалить фото</button>
                <input type="hidden" name="current_photo" value="<?= htmlspecialchars($photo['URL']) ?>">
            <?php endif; ?>
        </div>
        
        <button type="submit">Сохранить</button>
    </form>

    <script>
        document.getElementById('photoInput').onchange = function(e) {
            if (!e.target.files[0]) return;
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').innerHTML = 
                    '<img src="' + e.target.result + '" class="preview">' +
                    '<button type="submit" name="delete_photo" value="1" class="delete-btn">Удалить фото</button>';
            };
            reader.readAsDataURL(e.target.files[0]);
        };
    </script>
</body>
</html>