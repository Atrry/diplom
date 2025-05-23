<?php
require_once 'config.php';

// Обработка добавления нового турнира
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tournament'])) {
    $name = $sql->real_escape_string($_POST['name']);
    $type_id = (int)$_POST['type_id'];
    $start_date = $sql->real_escape_string($_POST['start_date']);
    $end_date = $sql->real_escape_string($_POST['end_date']);
    $max_teams = (int)$_POST['max_teams'];
    $status = $sql->real_escape_string($_POST['status']);
    $description = $sql->real_escape_string($_POST['description']);

    $sql->query("INSERT INTO турниры (название_турнира, id_типа, дата_начала, дата_окончания, максимальное_количество_команд, статус, описание) 
                VALUES ('$name', $type_id, '$start_date', '$end_date', $max_teams, '$status', '$description')");
    
    // Перенаправляем чтобы избежать повторной отправки формы
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Обработка изменения статуса турнира
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $tournament_id = (int)$_POST['tournament_id'];
    $new_status = $sql->real_escape_string($_POST['new_status']);
    
    $sql->query("UPDATE турниры SET статус = '$new_status' WHERE id_турнира = $tournament_id");
    
    // Возвращаем успешный ответ
    echo json_encode(['success' => true]);
    exit();
}

// Обработка обновления данных турнира
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_field'])) {
    $tournament_id = (int)$_POST['tournament_id'];
    $field = $sql->real_escape_string($_POST['field']);
    $value = $sql->real_escape_string($_POST['value']);
    
    // Проверяем, является ли поле числовым (id_типа или максимальное_количество_команд)
    if ($field === 'id_типа' || $field === 'максимальное_количество_команд') {
        $value = (int)$value;
    }
    
    $sql->query("UPDATE турниры SET $field = '$value' WHERE id_турнира = $tournament_id");
    
    // Возвращаем успешный ответ
    echo json_encode(['success' => true]);
    exit();
}

// Получаем данные о турнирах
$tournament_data = $sql->query("SELECT 
    т.*, 
    тт.название_типа, 
    тт.описание AS описание_типа
FROM 
    турниры т
JOIN 
    типы_турниров тт ON т.id_типа = тт.id_типа;");

// Получаем все типы турниров для select
$types_data = $sql->query("SELECT * FROM типы_турниров");
$types = [];
while ($type = $types_data->fetch_assoc()) {
    $types[] = $type;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ПАНЕЛЬ АДМИНИСТРАТОРА</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .add-tournament-container {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 100px;
        }
        .submit-btn {
            background-color: #3b3b3b;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        /* Стили для редактируемых ячеек */
        .editable {
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 8px;
        }
        .editable:hover {
            background-color: #f0f0f0;
        }
        .editable-input {
            width: calc(100% - 16px);
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin: -8px;
        }
        .editable-select {
            width: calc(100% - 16px);
            padding: 8px;
            margin: -8px;
        }
        .date-input {
            width: 120px;
        }
        table.request {
            width: 100%;
            border-collapse: collapse;
        }
        table.request td, table.request th {
            padding: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
<header>
    <a href="dashboard.php" class="nav-btn">Назад</a>
    <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
    <a href="?logout" class="logout-button">Выйти</a>
</header>
    
<main>
    <div class="container">
        <!-- Форма добавления нового турнира -->
        <div class="add-tournament-container">

<!-- Список существующих турниров -->
<h2 id="news-container">Список турниров</h2>
        <table border="1" class="request">
            <tr>
                <th>Название турнира</th>
                <th>Тип турнира</th>
                <th>Дата начала - дата окончания</th>
                <th>Максимальное количество команд</th>
                <th>Статус</th>
                <th>Описание</th>
            </tr>
            <?php 
            // Сбросим указатель результата, чтобы можно было снова его использовать
            $tournament_data->data_seek(0);
            while ($item = $tournament_data->fetch_assoc()): ?>
            <tr>
                <td class="editable" data-field="название_турнира" data-tournament-id="<?= $item['id_турнира'] ?>">
                    <?= htmlspecialchars($item['название_турнира'] ?? '') ?>
                </td>
                <td class="editable" data-field="id_типа" data-tournament-id="<?= $item['id_турнира'] ?>">
                    <?= htmlspecialchars($item['название_типа'] ?? '') ?>
                </td>
                <td>
                    <span class="editable date-field" data-field="дата_начала" data-tournament-id="<?= $item['id_турнира'] ?>">
                        <?= htmlspecialchars($item['дата_начала'] ?? '') ?>
                    </span> - 
                    <span class="editable date-field" data-field="дата_окончания" data-tournament-id="<?= $item['id_турнира'] ?>">
                        <?= htmlspecialchars($item['дата_окончания'] ?? '') ?>
                    </span>
                </td>
                <td class="editable" data-field="максимальное_количество_команд" data-tournament-id="<?= $item['id_турнира'] ?>">
                    <?= htmlspecialchars($item['максимальное_количество_команд'] ?? '') ?>
                </td>
                <td>
                    <select class="status-select" data-tournament-id="<?= $item['id_турнира'] ?>">
                        <option value="регистрация" <?= $item['статус'] == 'регистрация' ? 'selected' : '' ?>>Регистрация</option>
                        <option value="регистрация завершена" <?= $item['статус'] == 'регистрация завершена' ? 'selected' : '' ?>>Регистрация завершена</option>
                        <option value="в процессе" <?= $item['статус'] == 'в процессе' ? 'selected' : '' ?>>В процессе</option>
                        <option value="завершен" <?= $item['статус'] == 'завершен' ? 'selected' : '' ?>>Завершен</option>
                    </select>
                </td>
                <td class="editable" data-field="описание" data-tournament-id="<?= $item['id_турнира'] ?>">
                    <?= htmlspecialchars($item['описание'] ?? '') ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

            <h2>Добавить новый турнир</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Название турнира:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="type_id">Тип турнира:</label>
                    <select id="type_id" name="type_id" required>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id_типа'] ?>"><?= htmlspecialchars($type['название_типа']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Дата начала:</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">Дата окончания:</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                
                <div class="form-group">
                    <label for="max_teams">Максимальное количество команд:</label>
                    <input type="number" id="max_teams" name="max_teams" min="2" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Статус:</label>
                    <select id="status" name="status" required>
                        <option value="регистрация">Регистрация</option>
                        <option value="регистрация завершена">Регистрация завершена</option>
                        <option value="в процессе">В процессе</option>
                        <option value="завершен">Завершен</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <button type="submit" name="add_tournament" class="submit-btn">Добавить турнир</button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка изменения статуса турнира
    const statusSelects = document.querySelectorAll('.status-select');
    
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const tournamentId = this.dataset.tournamentId;
            const newStatus = this.value;
            
            // Отправка данных на сервер
            fetch('create_tournament.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `change_status=1&tournament_id=${tournamentId}&new_status=${encodeURIComponent(newStatus)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Статус успешно обновлен!');
                } else {
                    alert('Ошибка при обновлении статуса');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении статуса');
            });
        });
    });
    
    // Обработка редактирования ячеек
    const editableCells = document.querySelectorAll('.editable');
    
    editableCells.forEach(cell => {
        cell.addEventListener('click', function(e) {
            // Если уже редактируется, не обрабатываем повторный клик
            if (this.querySelector('input, select')) return;
            
            const tournamentId = this.dataset.tournamentId;
            const field = this.dataset.field;
            const originalValue = this.textContent.trim();
            
            // Сохраняем ссылку на элемент
            const element = this;
            
            // В зависимости от типа поля создаем разные элементы ввода
            if (field === 'id_типа') {
                // Создаем select для выбора типа турнира
                const select = document.createElement('select');
                select.className = 'editable-select';
                
                // Добавляем варианты из списка типов турниров
                <?php foreach ($types as $type): ?>
                    select.innerHTML += `<option value="<?= $type['id_типа'] ?>"><?= htmlspecialchars($type['название_типа']) ?></option>`;
                <?php endforeach; ?>
                
                // Устанавливаем текущее значение
                const currentType = "<?= $item['название_типа'] ?? '' ?>";
                Array.from(select.options).forEach(option => {
                    if (option.text === currentType) {
                        option.selected = true;
                    }
                });
                
                // Заменяем содержимое ячейки на select
                element.innerHTML = '';
                element.appendChild(select);
                select.focus();
                
                // Обработка потери фокуса
                select.addEventListener('blur', function() {
                    saveChanges();
                });
                
                // Обработка нажатия Enter
                select.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        saveChanges();
                    }
                });
                
                function saveChanges() {
                    const newValue = select.value;
                    const newText = select.options[select.selectedIndex].text;
                    element.textContent = newText;
                    updateField(tournamentId, field, newValue, element);
                }
                
            } else if (field === 'дата_начала' || field === 'дата_окончания') {
                // Создаем input для даты
                const input = document.createElement('input');
                input.type = 'date';
                input.className = 'editable-input date-input';
                input.value = originalValue;
                
                // Заменяем содержимое ячейки на input
                element.innerHTML = '';
                element.appendChild(input);
                input.focus();
                
                // Обработка потери фокуса
                input.addEventListener('blur', function() {
                    saveChanges();
                });
                
                // Обработка нажатия Enter
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        saveChanges();
                    }
                });
                
                function saveChanges() {
                    const newValue = input.value;
                    element.textContent = newValue;
                    updateField(tournamentId, field, newValue, element);
                }
                
            } else {
                // Создаем обычный input для текста или чисел
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'editable-input';
                input.value = originalValue;
                
                // Для числовых полей устанавливаем соответствующий тип
                if (field === 'максимальное_количество_команд') {
                    input.type = 'number';
                    input.min = '2';
                }
                
                // Заменяем содержимое ячейки на input
                element.innerHTML = '';
                element.appendChild(input);
                input.focus();
                
                // Обработка потери фокуса
                input.addEventListener('blur', function() {
                    saveChanges();
                });
                
                // Обработка нажатия Enter
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        saveChanges();
                    }
                });
                
                function saveChanges() {
                    const newValue = input.value;
                    element.textContent = newValue;
                    updateField(tournamentId, field, newValue, element);
                }
            }
        });
    });
    
    function updateField(tournamentId, field, value, element) {
        // Отправка данных на сервер
        fetch('create_tournament.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `update_field=1&tournament_id=${tournamentId}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Ошибка при обновлении данных');
                // Можно добавить восстановление предыдущего значения
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при обновлении данных');
        });
    }
});
</script>
</body>
</html>