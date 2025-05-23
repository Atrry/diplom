document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('registrationModal');
    const btn = document.getElementById('registerTeamBtn');
    const span = document.querySelector('.close');
    
// Открытие модального окна
btn.onclick = function() {
    modal.classList.add('active');
    loadTournaments();
    loadRoles();
}

// Закрытие модального окна
span.onclick = function() {
    modal.classList.remove('active');
}

// Закрытие при клике вне окна
window.onclick = function(event) {
    if (event.target == modal) {
        modal.classList.remove('active');
    }
}
    
    // Добавление участника
    document.getElementById('addMemberBtn').onclick = function() {
        const name = document.getElementById('newMember').value.trim();
        const roleId = document.getElementById('memberRole').value;
        const roleText = document.getElementById('memberRole').options[document.getElementById('memberRole').selectedIndex].text;
        
        if (name) {
            const memberItem = document.createElement('div');
            memberItem.className = 'member-item';
            memberItem.innerHTML = `
                <span>${name} (${roleText})</span>
                <span class="remove-member" data-name="${name}" data-role="${roleId}">×</span>
            `;
            document.getElementById('membersList').appendChild(memberItem);
            document.getElementById('newMember').value = '';
        }
    };
    
    // Удаление участника
    document.getElementById('membersList').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-member')) {
            e.target.parentElement.remove();
        }
    });
    
    // Загрузка турниров
    function loadTournaments() {
    fetch('?action=getTournaments')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Ошибка сервера:', data.error);
                return;
            }
            const select = document.getElementById('tournament');
            select.innerHTML = '';
            if (data.length === 0) {
                const option = document.createElement('option');
                option.textContent = 'Нет доступных турниров';
                option.disabled = true;
                option.selected = true;
                select.appendChild(option);
            } else {
                data.forEach(tournament => {
                    const option = document.createElement('option');
                    option.value = tournament.id;
                    option.textContent = tournament.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Ошибка при загрузке турниров:', error);
        });
    }
    
    // Загрузка ролей
    function loadRoles() {
        fetch('?action=getRoles')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('memberRole');
                select.innerHTML = '';
                data.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.name;
                    select.appendChild(option);
                });
            });
    }
    
    // Отправка формы
    document.getElementById('teamRegistrationForm').onsubmit = function(e) {
        e.preventDefault();
        
        const teamData = {
            tournamentId: document.getElementById('tournament').value,
            teamName: document.getElementById('teamName').value,
            leader: {
                name: document.getElementById('teamLeader').value,
                phone: document.getElementById('leaderPhone').value,
                email: document.getElementById('leaderEmail').value
            },
            members: []
        };
        
        // Собираем участников
        document.querySelectorAll('.member-item').forEach(item => {
            teamData.members.push({
                name: item.querySelector('span:first-child').textContent.split(' (')[0],
                roleId: item.querySelector('.remove-member').dataset.role
            });
        });
        
        // Отправка данных на сервер
        fetch('?action=registerTeam', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(teamData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Команда успешно зарегистрирована!');
                modal.style.display = 'none';
                // Очищаем форму
                document.getElementById('teamRegistrationForm').reset();
                document.getElementById('membersList').innerHTML = '';
            } else {
                alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            alert('Ошибка сети: ' + error.message);
        });
    };
});
