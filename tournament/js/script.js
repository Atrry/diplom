function main() {
    create_bracket();
    $('#saveButton').on('click', update_date);
}

function create_bracket() {
    $.getJSON('php/user_bracket_data.php', { tournament_id: tournamentId }) // Путь изменён
    .done(function(data) {
        console.log('Получены данные:', data);
        
        if (!data.teams || data.teams.length === 0) {
            console.error('Нет данных о командах');
            $('#bracket').html('<p>Нет данных о командах для отображения</p>');
            return;
        }

        window.matchIdMap = {};
        
        data.matchIds.forEach((round, roundIndex) => {
            round.forEach((matchId, matchIndex) => {
                const key = `${roundIndex}-${matchIndex}`;
                window.matchIdMap[key] = matchId;
            });
        });

        // Инициализируем турнирную сетку с кастомными параметрами
        $('#bracket').bracket({
            init: data,
            skipConsolationRound: true,
            teamWidth: 160,
            scoreWidth: 100,
            matchMargin: 100,
            roundMargin: 100
        });

        setTimeout(() => {
            $('.team .label').each(function() {
                const text = $(this).text().trim();
                if (!text || text === 'null') {
                    $(this).text('TBD');
                }
            });
        }, 100);

        $('.team .label').attr('contenteditable', false).removeClass('editable-team');
        $('.score').attr('contenteditable', false).removeClass('editable-score');
    })
    .fail(function(error) {
        console.error('Ошибка загрузки данных:', error);
        $('#bracket').html('<p style="color: red;">Ошибка загрузки турнирной сетки</p>');
    });
}

function update_date() {
    const dataToSave = { matches: [] };

    $('.match').each(function() {
        const $match = $(this);
        const roundIndex = $(this).closest('.round').index();
        const matchIndex = $(this).index();
        const matchId = window.matchIdMap[`${roundIndex}-${matchIndex}`];

        dataToSave.matches.push({
            matchId,
            team1: $match.find('.team:first .label').text().trim(),
            team2: $match.find('.team:last .label').text().trim(),
            score1: parseInt($match.find('.score:first').text()) || 0,
            score2: parseInt($match.find('.score:last').text()) || 0
        });
    });

    console.log('Отправляемые данные:', dataToSave);
    
    $.ajax({
        url: 'php/data_update.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(dataToSave)
    }).done(function(response) {
        console.log('Данные успешно сохранены:', response);
        alert('Изменения сохранены успешно!');
    }).fail(function(error) {
        console.error('Ошибка сохранения:', error);
        alert('Ошибка при сохранении изменений!');
    });
}

$(document).ready(main);