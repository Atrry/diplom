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

// Проверки безопасности сессии
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

// Обработка одобрения заявки с случайным распределением команд
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
            $id_команды = $application['id_команды'];
            $id_турнира = $application['id_турнира'];
            
            // Переносим всех участников команды в таблицу участников
            $team_members_stmt = $sql->prepare("SELECT * FROM заявка_на_турнир 
                                              WHERE id_команды = ? AND id_турнира = ?");
            $team_members_stmt->bind_param("ii", $id_команды, $id_турнира);
            $team_members_stmt->execute();
            $team_members_result = $team_members_stmt->get_result();
            $team_members = $team_members_result->fetch_all(MYSQLI_ASSOC);
            
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
            
            // Удаляем заявку
            $delete_stmt = $sql->prepare("DELETE FROM заявка_на_турнир 
                                        WHERE id_команды = ? AND id_турнира = ?");
            $delete_stmt->bind_param("ii", $id_команды, $id_турнира);
            $delete_stmt->execute();
            
            // Проверяем, все ли команды зарегистрированы
            $approved_teams_stmt = $sql->prepare("
                SELECT COUNT(DISTINCT id_команды) as count 
                FROM участники_команд 
                WHERE id_турнира = ?
            ");
            $approved_teams_stmt->bind_param("i", $id_турнира);
            $approved_teams_stmt->execute();
            $approved_result = $approved_teams_stmt->get_result();
            $approved_count = $approved_result->fetch_assoc()['count'];
            
            $max_teams_stmt = $sql->prepare("
                SELECT максимальное_количество_команд 
                FROM турниры 
                WHERE id_турнира = ?
            ");
            $max_teams_stmt->bind_param("i", $id_турнира);
            $max_teams_stmt->execute();
            $max_result = $max_teams_stmt->get_result();
            $max_teams = $max_result->fetch_assoc()['максимальное_количество_команд'];
            
            // Если все команды зарегистрированы, создаем матчи со случайным распределением
            if ($approved_count == $max_teams) {
                // Получаем список всех команд турнира в случайном порядке
                $teams_stmt = $sql->prepare("
                    SELECT DISTINCT id_команды 
                    FROM участники_команд 
                    WHERE id_турнира = ?
                    ORDER BY RAND()
                ");
                $teams_stmt->bind_param("i", $id_турнира);
                $teams_stmt->execute();
                $teams_result = $teams_stmt->get_result();
                $teams = $teams_result->fetch_all(MYSQLI_ASSOC);
                
                // Создаем матчи для первого раунда
                $match_num = 1;
                for ($i = 0; $i < count($teams); $i += 2) {
                    $team1 = $teams[$i]['id_команды'];
                    $team2 = isset($teams[$i+1]) ? $teams[$i+1]['id_команды'] : NULL;
                    
                    $insert_match_stmt = $sql->prepare("
                        INSERT INTO матчи 
                        (id_турнира, раунд, номер_матча_в_раунде, id_команды_1, id_команды_2, статус) 
                        VALUES (?, 1, ?, ?, ?, 'ожидается')
                    ");
                    $insert_match_stmt->bind_param("iiii", $id_турнира, $match_num, $team1, $team2);
                    $insert_match_stmt->execute();
                    
                    $match_num++;
                }
                
                // Обновляем статус турнира
                $update_tournament_stmt = $sql->prepare("
                    UPDATE турниры 
                    SET статус = 'в процессе' 
                    WHERE id_турнира = ?
                ");
                $update_tournament_stmt->bind_param("i", $id_турнира);
                $update_tournament_stmt->execute();
            }
            
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

// Обработка отклонения заявки (удаление всей команды)
if (isset($_GET['reject'])) {
    $id_участника = (int)$_GET['reject'];
    
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
            $id_команды = $application['id_команды'];
            
            // 1. Удаляем ВСЕХ участников этой команды из таблицы заявок
            $delete_applications_stmt = $sql->prepare("
                DELETE FROM заявка_на_турнир 
                WHERE id_команды = ?
            ");
            $delete_applications_stmt->bind_param("i", $id_команды);
            $delete_applications_stmt->execute();
            
            // 2. Удаляем ВСЕХ участников этой команды из таблицы участников
            $delete_participants_stmt = $sql->prepare("
                DELETE FROM участники_команд 
                WHERE id_команды = ?
            ");
            $delete_participants_stmt->bind_param("i", $id_команды);
            $delete_participants_stmt->execute();
            
            // 3. Удаляем саму команду
            $delete_team_stmt = $sql->prepare("
                DELETE FROM команды 
                WHERE id_команды = ?
            ");
            $delete_team_stmt->bind_param("i", $id_команды);
            $delete_team_stmt->execute();
            
            // Фиксируем транзакцию
            $sql->commit();
            
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            // Откатываем транзакцию в случае ошибки
            $sql->rollback();
            die("Ошибка при отклонении заявки: " . $e->getMessage());
        }
    }
}

// Получаем список заявок с группировкой по командам и турнирам
$applicationsQuery = $sql->query('
    SELECT 
        з.id_команды,
        з.id_турнира,
        т.название_турнира,
        к.название_команды,
        к.контактный_телефон,
        к.контактный_email,
        MIN(з.дата_добавления) as дата_подачи,
        GROUP_CONCAT(DISTINCT CONCAT(з.фио, "|", з.id_роли) SEPARATOR "||") as участники
    FROM заявка_на_турнир з
    LEFT JOIN турниры т ON з.id_турнира = т.id_турнира
    LEFT JOIN команды к ON з.id_команды = к.id_команды
    GROUP BY з.id_команды, з.id_турнира, т.название_турнира, к.название_команды, к.контактный_телефон, к.контактный_email
    ORDER BY т.название_турнира, к.название_команды
');

$applications = $applicationsQuery->fetch_all(MYSQLI_ASSOC);

// Получаем список ролей для отображения
$rolesQuery = $sql->query('SELECT * FROM роли_в_команде');
$roles = [];
while ($role = $rolesQuery->fetch_assoc()) {
    $roles[$role['id_роли']] = $role['название_роли'];
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
        .application-block {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            border-radius: 5px;
            overflow: hidden;
        }
        .application-header {
            background-color: #f5f5f5;
            padding: 10px 15px;
            cursor: pointer;
            font-weight: bold;
        }
        .application-content {
            padding: 15px;
            display: none;
        }
        .team-name {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .contact-info {
            margin-bottom: 15px;
        }
        .members-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .members-table th, .members-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .members-table th {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function toggleApplication(id) {
            const content = document.getElementById('content-' + id);
            if (content.style.display === 'block') {
                content.style.display = 'none';
            } else {
                content.style.display = 'block';
            }
        }
    </script>
</head>
<body>
<header>
    <a href="dashboard.php" class="nav-btn">Назад</a>
    <h1>ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
    <a href="?logout" class="logout-button">Выйти</a>
</header>

<div class="container">
    <h2 id="news-container">Заявки на турнир</h2>
    
    <?php foreach ($applications as $index => $application): 
        // Получаем первого участника команды для этого турнира (для кнопок действий)
        $stmt = $sql->prepare("SELECT id_участника FROM заявка_на_турнир WHERE id_команды = ? AND id_турнира = ? LIMIT 1");
        $stmt->bind_param("ii", $application['id_команды'], $application['id_турнира']);
        $stmt->execute();
        $result = $stmt->get_result();
        $first_member = $result->fetch_assoc();
    ?>
    <div class="application-block">
        <div class="application-header" onclick="toggleApplication(<?= $index ?>)">
            Заявка на регистрацию на турнир: <?= htmlspecialchars($application['название_турнира'] ?? '') ?>
        </div>
        <div class="application-content" id="content-<?= $index ?>">
            <div class="team-name">Команда: <?= htmlspecialchars($application['название_команды'] ?? '') ?></div>
            
            <div class="contact-info">
                Контактный телефон капитана: <?= htmlspecialchars($application['контактный_телефон'] ?? '') ?><br>
                Контактный email капитана: <?= htmlspecialchars($application['контактный_email'] ?? '') ?>
            </div>
            
            <table class="members-table">
                <tr>
                    <th>ФИО</th>
                    <th>Роль</th>
                </tr>
                <?php 
                $members = explode('||', $application['участники']);
                foreach ($members as $member):
                    list($fio, $role_id) = explode('|', $member);
                ?>
                <tr>
                    <td><?= htmlspecialchars($fio) ?></td>
                    <td><?= htmlspecialchars($roles[$role_id] ?? $role_id) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <div>Дата подачи заявки: <?= htmlspecialchars($application['дата_подачи'] ?? '') ?></div>
            
            <div class="action-buttons">
                <?php if ($first_member): ?>
                <form method="get" style="display: inline;">
                    <input type="hidden" name="approve" value="<?= $first_member['id_участника'] ?>">
                    <button type="submit" class="approve-btn" 
                            onclick="return confirm('Одобрить ВСЕХ участников команды <?= htmlspecialchars($application['название_команды'] ?? '') ?>? Команда будет добавлена в турнир со случайным распределением.')">
                        Одобрить
                    </button>
                </form>
                <form method="get" style="display: inline;">
                    <input type="hidden" name="reject" value="<?= $first_member['id_участника'] ?>">
                    <button type="submit" class="reject-btn" 
                            onclick="return confirm('Вы уверены? Будет удалена ВСЯ КОМАНДА со всеми участниками и заявками!')">
                        Отклонить
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>