<?php
require '../admin/config.php';

$tournament_id = $_GET['id'] ?? 0;
if ($tournament_id <= 0) {
    die('Неверный ID турнира');
}

// Получаем информацию о турнире
$stmt = $sql->prepare('SELECT * FROM турниры WHERE id_турнира = ?');
$stmt->bind_param('i', $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
$tournament = $result->fetch_assoc();

if (!$tournament) {
    die("Турнир с ID $tournament_id не найден!");
}

// Проверяем количество зарегистрированных команд
$stmt = $sql->prepare('SELECT COUNT(*) as team_count FROM команды WHERE id_команды IN (SELECT id_команды FROM участники_команд WHERE id_турнира = ?)');
$stmt->bind_param('i', $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
$team_count = $result->fetch_assoc()['team_count'];

// Проверяем, достигнуто ли максимальное количество команд
if ($tournament['статус'] === 'регистрация' && 
    $tournament['максимальное_количество_команд'] !== NULL && 
    $team_count >= $tournament['максимальное_количество_команд']) {
    // Обновляем статус турнира, если достигнуто максимальное количество команд
    $update_stmt = $sql->prepare('UPDATE турниры SET статус = "регистрация завершена" WHERE id_турнира = ?');
    $update_stmt->bind_param('i', $tournament_id);
    $update_stmt->execute();
    $tournament['статус'] = 'регистрация завершена';
}

$registration_open = ($tournament['статус'] === 'регистрация' && 
                    ($tournament['максимальное_количество_команд'] === NULL || 
                     $team_count < $tournament['максимальное_количество_команд']));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title><?= htmlspecialchars($tournament['название_турнира']) ?> | Фиджитал Спорт</title>
</head>
<body>
<header>
    <a href="../index.php" class="logo"><h1>ССК "Хаски"</h1></a>
</header>
<nav>
    <a href="../index.php">Главная</a>
    <a href="../gallery/">Галерея</a>
    <a href="../news-page/">Новости</a>
    <a href="../contacts/">Контакты</a>
</nav>
<main>
    <div class="btn-title">
        <a href="../index.php" class="view-link">Назад</a>
        <h1 class="title"><?= htmlspecialchars($tournament['название_турнира']) ?></h1>
    </div>
    
    <?php if (in_array($tournament['статус'], ['в процессе', 'завершен'])): ?>
        <!-- Отображаем турнирную сетку -->
        <div class="tournament-grid">
            <div id="bracket"></div>
        </div>
        
        <script>
            const tournamentId = <?= $tournament_id ?>;
        </script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js"></script>
        <script src="js/script.js"></script>
        
    <?php elseif ($tournament['статус'] === 'регистрация' && $registration_open): ?>
        <!-- Отображаем форму регистрации -->
        <div class="reg-for-tournamennt">
            <div class="btn">
                <button id="registerTeamBtn" class="btn-register">Зарегистрировать команду</button>
            </div>

            <div id="registrationModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 class="modal-title">Регистрация команды на турнир: <?= htmlspecialchars($tournament['название_турнира']) ?></h2>
                    <form id="teamRegistrationForm">
                        <input type="hidden" id="tournament" name="tournament" value="<?= $tournament['id_турнира'] ?>">
                        
                        <div class="form-group">
                            <label for="teamName">Название команды:</label>
                            <input type="text" id="teamName" required>
                            <div id="teamNameError" class="error-message"></div>
                        </div>
                        
                        <div class="form-group">
                        <label>Контактное лицо (капитан):</label>
                        <input type="text" id="teamLeader" placeholder="ФИО капитана" required>
                        <input type="tel" id="leaderPhone" placeholder="+7 (___) ___-__-__" required>
                        <div id="phoneError" class="error-message"></div>
                        <input type="email" id="leaderEmail" placeholder="Email" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Участники команды (минимум 1):</label>
                            <div id="membersList">
                                <!-- Список добавленных участников -->
                            </div>
                            <div id="membersError" class="error-message"></div>
                            <div class="add-member">
                                <input type="text" id="newMember" placeholder="ФИО участника">
                                <select id="memberRole">
                                    <!-- Роли будут загружены через AJAX -->
                                </select>
                                <button type="button" id="addMemberBtn" class="btn-submit">Добавить</button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-register">Зарегистрировать</button>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        // Открытие/закрытие модального окна
        document.getElementById('registerTeamBtn')?.addEventListener('click', function() {
            document.getElementById('registrationModal').style.display = 'block';
            loadRoles(); // Загружаем роли при открытии модального окна
        });

        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('registrationModal').style.display = 'none';
            resetForm();
        });

        // Закрытие модального окна при клике вне его
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('registrationModal')) {
                document.getElementById('registrationModal').style.display = 'none';
                resetForm();
            }
        });

        // Сброс формы
        function resetForm() {
            document.getElementById('teamRegistrationForm').reset();
            document.getElementById('membersList').innerHTML = '';
            clearErrors();
        }

        // Очистка сообщений об ошибках
        function clearErrors() {
            document.getElementById('teamNameError').textContent = '';
            document.getElementById('phoneError').textContent = '';
            document.getElementById('membersError').textContent = '';
        }

        // Загрузка ролей из базы данных
        async function loadRoles() {
            try {
                const response = await fetch('php/get_roles.php');
                const roles = await response.json();
                
                const roleSelect = document.getElementById('memberRole');
                roleSelect.innerHTML = '';
                
                // Добавляем только роли "Игрок" и "Запасной" (исключаем "Капитана")
                roles.forEach(role => {
                    if (role.id_роли !== 1) { // Пропускаем роль "Капитан"
                        const option = document.createElement('option');
                        option.value = role.id_роли;
                        option.textContent = role.название_роли;
                        roleSelect.appendChild(option);
                    }
                });
            } catch (error) {
                console.error('Ошибка при загрузке ролей:', error);
            }
        }

        // Проверка уникальности названия команды
        async function isTeamNameUnique(teamName) {
            try {
                const response = await fetch('php/check_team_name.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ team_name: teamName })
                });
                const result = await response.json();
                return result.is_unique;
            } catch (error) {
                console.error('Ошибка при проверке названия команды:', error);
                return false;
            }
        }

        // Проверка уникальности телефона
        async function isPhoneUnique(phone) {
            try {
                const response = await fetch('php/check_phone.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ phone: phone })
                });
                const result = await response.json();
                return result.is_unique;
            } catch (error) {
                console.error('Ошибка при проверке телефона:', error);
                return false;
            }
        }

        // Добавление участника команды
        document.getElementById('addMemberBtn')?.addEventListener('click', function() {
            const memberName = document.getElementById('newMember').value.trim();
            const memberRole = document.getElementById('memberRole').value;
            const roleName = document.getElementById('memberRole').options[document.getElementById('memberRole').selectedIndex].text;
            
            if (memberName) {
                const memberItem = document.createElement('div');
                memberItem.className = 'member-item';
                memberItem.innerHTML = `
                    <span>${memberName} (${roleName})</span>
                    <input type="hidden" name="members[]" value='{"name":"${memberName.replace(/"/g, '&quot;')}","role":${memberRole}}'>
                    <button type="button" class="remove-member">×</button>
                `;
                
                document.getElementById('membersList').appendChild(memberItem);
                document.getElementById('newMember').value = '';
                document.getElementById('membersError').textContent = '';
                
                memberItem.querySelector('.remove-member').addEventListener('click', function() {
                    memberItem.remove();
                });
            } else {
                document.getElementById('membersError').textContent = 'Введите ФИО участника';
            }
        });

        // Валидация телефона в реальном времени
       document.getElementById('leaderPhone').addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, ''); // Удаляем всё, кроме цифр
        if (value.startsWith('7') || value.startsWith('8')) {
            value = '7' + value.substring(1); // Приводим к формату +7
        }
        if (value.length > 11) value = value.substring(0, 11); // Ограничиваем 11 цифрами
        
        let formatted = '';
        if (value.length > 0) formatted = '+7 ';
        if (value.length > 1) formatted += '(' + value.substring(1, 4);
        if (value.length > 4) formatted += ') ' + value.substring(4, 7);
        if (value.length > 7) formatted += '-' + value.substring(7, 9);
        if (value.length > 9) formatted += '-' + value.substring(9, 11);
        
        this.value = formatted;
    });

        // Основной обработчик формы
        document.getElementById('teamRegistrationForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors();
            
            const tournamentId = <?= $tournament_id ?>;
            const teamName = document.getElementById('teamName').value.trim();
            const leaderName = document.getElementById('teamLeader').value.trim();
            const leaderEmail = document.getElementById('leaderEmail').value.trim();
            const leaderPhone = document.getElementById('leaderPhone').value.trim();
            
            // Валидация данных
            let isValid = true;
            
            if (!teamName) {
                document.getElementById('teamNameError').textContent = 'Введите название команды';
                isValid = false;
            }
            
            if (!leaderName) {
                alert('Введите ФИО капитана');
                isValid = false;
            }
            
            if (!leaderPhone || !/^[\d\+][\d\-\(\)\s]{9,}$/.test(leaderPhone)) {
                document.getElementById('phoneError').textContent = 'Введите корректный номер телефона';
                isValid = false;
            }
            
            if (!leaderEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(leaderEmail)) {
                alert('Введите корректный email');
                isValid = false;
            }
            
            const memberItems = document.querySelectorAll('#membersList .member-item');
            if (memberItems.length < 1) {
                document.getElementById('membersError').textContent = 'Добавьте хотя бы одного участника команды';
                isValid = false;
            }
            
            if (!isValid) return;
            
            // Проверка уникальности названия команды
            if (!await isTeamNameUnique(teamName)) {
                document.getElementById('teamNameError').textContent = 'Команда с таким названием уже зарегистрирована';
                return;
            }
            
            // Проверка уникальности телефона
            if (!await isPhoneUnique(leaderPhone)) {
                document.getElementById('phoneError').textContent = 'Этот телефон уже используется другой командой';
                return;
            }
            
            // Сбор данных участников
            const members = Array.from(document.querySelectorAll('#membersList input')).map(input => {
                const data = JSON.parse(input.value);
                return {
                    name: data.name,
                    role_id: data.role
                };
            });
            
            // Добавляем капитана в список участников
            members.unshift({
                name: leaderName,
                role_id: 1 // Капитан
            });
            
            // Отображение загрузки
            const submitBtn = document.querySelector('#teamRegistrationForm .btn-register');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
            
            // Отправка данных на сервер
            try {
                const response = await fetch('php/register_team.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        tournament_id: tournamentId,
                        team_name: teamName,
                        leader_name: leaderName,
                        leader_phone: leaderPhone,
                        leader_email: leaderEmail,
                        members: members
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Команда успешно зарегистрирована!');
                    document.getElementById('registrationModal').style.display = 'none';
                    resetForm();
                    // Обновляем страницу после успешной регистрации
                    location.reload();
                } else {
                    alert('Ошибка: ' + (result.error || 'Не удалось зарегистрировать команду'));
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке данных');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        });
        </script>
        
    <?php elseif ($tournament['статус'] === 'регистрация завершена'): ?>
        <p>Регистрация на турнир завершена. Максимальное количество команд достигнуто.</p>
        
    <?php else: ?>
        <p>Турнир еще не начался или находится в неопределенном статусе.</p>
    <?php endif; ?>
</main>
</body>
</html>