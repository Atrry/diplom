$(document).ready(function() {
    // Загрузка списка турниров
    $.get('php/get_tournaments.php', function(data) {
        if (data && data.length) {
            data.forEach(function(tournament) {
                $('#tournamentSelector').append(
                    $('<option>', {
                        value: tournament.id,
                        text: tournament.name + ' (' + tournament.status + ')'
                    })
                );
            });
        } else {
            console.error('Нет данных о турнирах');
        }
    }).fail(function() {
        alert('Ошибка загрузки списка турниров');
    });

    // Обработчик изменения турнира
    $('#tournamentSelector').change(function() {
        const tournamentId = $(this).val();

        if (tournamentId) {
            loadBracket(tournamentId);
        } else {
            $('#bracket').empty();
        }
    });

    function loadBracket(tournamentId) {
        console.log(tournamentId);
    // Сначала проверяем статус турнира
    $.get('php/check_tournament_status.php?tournament_id=' + tournamentId, function(statusData) {
        statusData = JSON.parse(statusData);
        console.log(typeof statusData);
        console.log(statusData.status === 'регистрация');
        console.log(statusData.status == 'регистрация');
        if (statusData.status === 'регистрация') {
            $('#bracket').html('<div class="registration-message">Идёт регистрация на турнир.</div>');
            return;
        }
        
        // Если статус не "Регистрация", загружаем сетку как обычно
        $.get('php/bracket_data.php?tournament_id=' + tournamentId, function(data) {
            if (!data || !data.teams || !data.results) {
                console.error('Неверный формат данных турнирной сетки');
                return;
            }

            $('#bracket').empty();
            
            const saveData = {
                teams: data.teams,
                results: data.results
            };
            
            const container = $('#bracket');
            
            container.bracket({
                init: saveData,
                skipConsolationRound: true,
                teamWidth: 160,
                scoreWidth: 100,
                matchMargin: 100,
                roundMargin: 100,
            });

            // Остальной код обработки сетки остается без изменений
            setTimeout(() => {
                $('.score').each(function() {
                    const $score = $(this);
                    const matchId = findMatchId($score);
                    
                    $score.addClass('editable-score').on('click', function(e) {
                        e.stopPropagation();
                        const currentScore = $(this).text().trim();
                        const $parent = $(this).parent();
                        const isTeam1 = $parent.hasClass('team') && $parent.index() === 0;
                        
                        $(this).html(`<input class="score-input" value="${currentScore}" min="0">`);
                        $(this).find('input').focus().select();
                        
                        function saveScore() {
                            const newScore = $(this).find('input').val() || '0';
                            $(this).html(newScore);
                            
                            const $match = $(this).closest('.match');
                            const roundIndex = $match.closest('.round').index();
                            const matchIndex = $match.index();
                            
                            if (matchId) {
                                const score1 = isTeam1 ? parseInt(newScore) : parseInt($match.find('.score:first').text());
                                const score2 = !isTeam1 ? parseInt(newScore) : parseInt($match.find('.score:last').text());
                                
                                updateMatchScore(matchId, score1, score2);
                            }
                        }
                        
                        $(this).find('input').on('blur', saveScore);
                        $(this).find('input').on('keypress', function(e) {
                            if (e.which === 13) saveScore.call($score);
                        });
                    });
                });
            }, 100);

            if (data.matchIds) {
                data.matchIds.forEach((round, roundIndex) => {
                    round.forEach((matchId, matchIndex) => {
                        $(`.round:nth-child(${roundIndex + 1}) .match:nth-child(${matchIndex + 1})`).attr('data-match-id', matchId);
                    });
                });
            }
        }).fail(function(xhr) {
            alert('Ошибка загрузки турнирной сетки: ' + xhr.responseText);
        });
    }).fail(function() {
        alert('Ошибка проверки статуса турнира');
    });
}

    // Функция для поиска ID матча
    function findMatchId($element) {
        return $element.closest('.match').attr('data-match-id');
    }
    
    // Функция обновления счета на сервере
    function updateMatchScore(matchId, score1, score2) {
        $.ajax({
            url: 'php/data_update.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                matches: [{
                    matchId: matchId,
                    score1: score1,
                    score2: score2
                }]
            }),
            success: function(response) {
                if (response.error) {
                    alert('Ошибка сохранения: ' + response.error);
                } else {
                    console.log('Счет успешно обновлен');
                }
            },
            error: function(xhr) {
                alert('Ошибка сохранения: ' + xhr.responseText);
            }
        });
    }
});