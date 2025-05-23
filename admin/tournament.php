<?php

ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

require 'config.php';

// Проверки безопасности сессии (остаются без изменений)
if (!isset($_SESSION['ip'])) {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity'] = time();
}

if (isset($_SESSION['admin'])) {
    if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    if (time() - $_SESSION['last_activity'] > 1800) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    $_SESSION['last_activity'] = time();
}

if (!isset($_SESSION['admin']) && basename($_SERVER['PHP_SELF']) !== 'index.php') {
    header('Location: index.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    setcookie('admin_logged_in', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
    header('Location: index.php');
    exit();
}

// Обработка одобрения заявки (ИЗМЕНЕНО)
if (isset($_GET['approve'])) {
    $id_участника = (int)$_GET['approve'];
    
    // Получаем данные заявки
    $stmt = $sql->prepare("SELECT * FROM заявка_на_турнир WHERE id_участника = ?");
    $stmt->bind_param("i", $id_участника);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();
    
    if ($application) {
        // Начинаем транзакцию для атомарности операций
        $sql->begin_transaction();
        
        try {
            // Получаем ВСЕХ участников этой команды для этого турнира
            $team_members_stmt = $sql->prepare("SELECT * FROM заявка_на_турнир 
                                              WHERE id_команды = ? AND id_турнира = ?");
            $team_members_stmt->bind_param("ii", $application['id_команды'], $application['id_турнира']);
            $team_members_stmt->execute();
            $team_members_result = $team_members_stmt->get_result();
            $team_members = $team_members_result->fetch_all(MYSQLI_ASSOC);
            
            // Переносим всех участников команды в таблицу участников
            foreach ($team_members as $member) {
                $insert_stmt = $sql->prepare("INSERT INTO участники_команд 
                                            (id_команды, id_турнира, фио, id_роли, контактный_телефон, контактный_email) 
                                            VALUES (?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("iisiss", 
                    $member['id_команды'],
                    $member['id_турнира'],
                    $member['фио'],
                    $member['id_роли'],
                    $member['контактный_телефон'],
                    $member['контактный_email']
                );
                $insert_stmt->execute();
            }
            
            // Удаляем ВСЕХ участников этой команды из таблицы заявок
            $delete_stmt = $sql->prepare("DELETE FROM заявка_на_турнир 
                                        WHERE id_команды = ? AND id_турнира = ?");
            $delete_stmt->bind_param("ii", $application['id_команды'], $application['id_турнира']);
            $delete_stmt->execute();
            
            // Фиксируем транзакцию
            $sql->commit();
            
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            // Откатываем транзакцию в случае ошибки
            $sql->rollback();
            die("Ошибка при обработке заявки: " . $e->getMessage());
        }
    }
}

// Обработка отклонения заявки (остается без изменений)
if (isset($_GET['reject'])) {
    $id_участника = (int)$_GET['reject'];
    
    $stmt = $sql->prepare("SELECT * FROM заявка_на_турнир WHERE id_участника = ?");
    $stmt->bind_param("i", $id_участника);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();
    
    if ($application) {
        // Удаляем заявку
        $delete_stmt = $sql->prepare("DELETE FROM заявка_на_турнир WHERE id_участника = ?");
        $delete_stmt->bind_param("i", $id_участника);
        $delete_stmt->execute();
        
        // Удаляем команду, если это последний участник
        $check_team_stmt = $sql->prepare("SELECT COUNT(*) as count FROM заявка_на_турнир WHERE id_команды = ?");
        $check_team_stmt->bind_param("i", $application['id_команды']);
        $check_team_stmt->execute();
        $team_result = $check_team_stmt->get_result();
        $team_data = $team_result->fetch_assoc();
        
        if ($team_data['count'] == 0) {
            $delete_team_stmt = $sql->prepare("DELETE FROM команды WHERE id_команды = ?");
            $delete_team_stmt->bind_param("i", $application['id_команды']);
            $delete_team_stmt->execute();
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Получаем список заявок с названиями турниров (остается без изменений)
$newsQuery = $sql->query('SELECT з.*, т.название_турнира 
                         FROM заявка_на_турнир з 
                         LEFT JOIN турниры т ON з.id_турнира = т.id_турнира');
$news = $newsQuery->fetch_all(MYSQLI_ASSOC);

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
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .approve-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .reject-btn {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <a href="dashboard.php" class="nav-btn">Назад</a>
    <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
    <a href="?logout" class="logout-button">Выйти</a>
</header>

    <div class="container">
        <h2 id="news-container">Заявки на турнир</h2>
        <table border="1" class="request">
            <tr>
                <th>Название турнира</th>
                <th>ФИО</th>
                <th>Роль</th>
                <th>Контактный телефон</th>
                <th>Контактный email</th>
                <th>Дата подачи заявки</th>
                <th>Действия</th>
            </tr>

            <?php foreach ($news as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['название_турнира'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['фио'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['id_роли'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['контактный_телефон'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['контактный_email'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['дата_добавления'] ?? '') ?></td>
                <td>
                    <div class="action-buttons">
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="approve" value="<?= $item['id_участника'] ?>">
                            <button type="submit" class="approve-btn" 
                                    onclick="return confirm('Одобрить ВСЕХ участников команды <?= htmlspecialchars($item['название_команды'] ?? '') ?>?')">
                                Одобрить
                            </button>
                        </form>
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="reject" value="<?= $item['id_участника'] ?>">
                            <button type="submit" class="reject-btn" 
                                    onclick="return confirm('Отклонить заявку? Команда будет удалена, если это последний участник.')">
                                Отклонить
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>