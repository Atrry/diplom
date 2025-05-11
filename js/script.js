function main() {
    create_bracket();
    $('#saveButton').on('click', update_date);
}

function create_bracket() {
    $.getJSON('php/bracket_data.php').done(function(data) {
        // ИЗМЕНЕНО: Создаем карту соответствия позиций матчей и их ID
        window.matchIdMap = {};
        
        // Заполняем карту ID матчей
        data.matchIds.forEach((round, roundIndex) => {
            round.forEach((matchId, matchIndex) => {
                const key = `${roundIndex}-${matchIndex}`;
                window.matchIdMap[key] = matchId;
            });
        });

        $('#bracket').bracket({
            init: data,
            skipConsolationRound: true
        });

        // Редактируемые элементы
        $('.team:not(.empty) .label').attr('contenteditable', true).addClass('editable-team');
        $('.score').attr('contenteditable', true)
            .addClass('editable-score')
            .on('input', function() {
                $(this).text($(this).text().replace(/[^\d]/g, '') || '0');
            })
            .on('keydown', function(e) {
                return [8, 46].includes(e.keyCode) || 
                    (e.keyCode >= 48 && e.keyCode <= 57) || 
                    (e.keyCode >= 96 && e.keyCode <= 105);
            });
    }).fail(console.error);
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
    }).done(console.log).fail(console.error);
}

$(document).ready(main);