<?php
header('Content-Type: application/json');

$host = '185.244.173.217';
$user = 'root';
$password = 'kdyKYAXM1';
$dbname = 'phygital';

try {
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения: " . $conn->connect_error);
    }
    
    // Получаем ID турнира из GET-параметра
    $tournament_id = isset($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
    if ($tournament_id <= 0) {
        throw new Exception("Неверный ID турнира");
    }
    
    $query = "SELECT 
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
        WHERE m.id_турнира = ?
        ORDER BY m.раунд, m.номер_матча_в_раунде
    "; 
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Ошибка запроса: " . $conn->error);
    }
    
    $bracketData = ['teams' => [], 'results' => [], 'matchIds' => []];

    while ($match = $result->fetch_assoc()) {
        $round = $match['раунд'] - 1;
        $matchNum = $match['номер_матча_в_раунде'] - 1;
        
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

    echo json_encode($bracketData, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>