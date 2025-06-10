<?php
require_once 'config.php';

// Обработка загрузки фото
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $title = $sql->real_escape_string($_POST['title']);
    $content = $sql->real_escape_string($_POST['content']);
    
    // Обработка файла
    $fileName = $news['фото']; // Сохраняем текущее фото по умолчанию
    
    if (isset($_POST['delete_photo'])) {
        // Удаляем фото (оставляем поле пустым)
        $fileName = '';
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        // Загружаем новое фото
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/news/';
        
        // Сохраняем оригинальное имя файла
        $fileName = basename($_FILES['photo']['name']);
        $filePath = $uploadDir . $fileName;
        
        // Проверяем и создаем директорию, если ее нет
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        move_uploaded_file($_FILES['photo']['tmp_name'], $filePath);
        
        // Формируем полный URL для сохранения в БД
        $fileName = '/uploads/news/' . $fileName;
    }
    
    // Обновляем запись
    $sql->query("UPDATE новости SET 
        заголовок = '$title',
        контент = '$content',
        фото = '$fileName'
        WHERE id = $id");
    
    header("Location: dashboard.php");
    exit;
}

// Получение данных новости
$id = (int)$_GET['id'];
$news = $sql->query("SELECT * FROM новости WHERE id = $id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактирование новости</title>
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
            max-width: 200px; display: <?= $news['фото']?> ? 'block' : 'none'; 
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
    <h1>Редактирование новости</h1>
    <?= isset($_GET['success']) ? '<p style="color:green">Сохранено!</p>' : '' ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $news['id'] ?>">
        
        <label>Заголовок:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($news['заголовок']) ?>" required>
        
        <label>Текст:</label>
        <textarea name="content" required><?= htmlspecialchars($news['контент']) ?></textarea>
        
        <label>Фото:</label>
        <input type="file" name="photo" id="photoInput">
        
        <div id="photoPreview">
            <?php if ($news['фото']): ?>
                <img src="<?= htmlspecialchars($news['фото']) ?>" class="preview">
                <button type="submit" name="delete_photo" value="1" class="delete-btn">Удалить фото</button>
                <input type="hidden" name="current_photo" value="<?= htmlspecialchars($news['фото']) ?>">
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