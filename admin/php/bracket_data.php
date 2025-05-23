<?php
$host = '185.244.173.217';
$user = 'root';
$password = 'kdyKYAXM1';
$dbname = 'phygital';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Ошибка подключения: " . $conn->connect_error);
$conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

$query = "
    SELECT 
        m.id_матча,
        m.раунд,
        m.номер_матча_в_раунде,
        c1.название_команды as команда1,
        c2.название_команды as команда2,
        m.счет_команды_1,
        m.счет_команды_2
    FROM матчи m
    LEFT JOIN команды c1 ON m.id_команды_1 = c1.id_команды
    LEFT JOIN команды c2 ON m.id_команды_2 = c2.id_команды
    WHERE m.id_турнира = 1
    ORDER BY m.раунд, m.номер_матча_в_раунде
";

$result = $conn->query($query);
$bracketData = ['teams' => [], 'results' => [], 'matchIds' => []];

while ($match = $result->fetch_assoc()) {
    $round = $match['раунд'] - 1;
    $matchNum = $match['номер_матча_в_раунде'] - 1;
    
    // ИЗМЕНЕНО: Гарантированное заполнение всех структур
    $bracketData['results'][$round][$matchNum] = [
        (int)$match['счет_команды_1'] ?? 0, 
        (int)$match['счет_команды_2'] ?? 0
    ];
    $bracketData['matchIds'][$round][$matchNum] = $match['id_матча'];
    
    if ($round === 0) {
        $bracketData['teams'][] = [
            $match['команда1'] ?? 'TBD', 
            $match['команда2'] ?? 'TBD'
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($bracketData, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
$conn->close();
?>