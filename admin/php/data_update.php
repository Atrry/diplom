<?php
$host = '185.244.173.217';
$user = 'root';
$password = 'kdyKYAXM1';
$dbname = 'phygital';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Ошибка подключения к БД']));
}
$conn->set_charset('utf8mb4');

header('Content-Type: application/json');

// Получаем и декодируем JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['error' => 'Неверный JSON: '.json_last_error_msg()]));
}

if (!isset($data['matches'])) {
    die(json_encode(['error' => 'Отсутствует ключ "matches"']));
}

$response = ['success' => true, 'updated' => 0];

try {
    $conn->begin_transaction();
    
    foreach ($data['matches'] as $match) {
        if (!isset($match['matchId'], $match['score1'], $match['score2'])) {
            continue;
        }
        
        $stmt = $conn->prepare("UPDATE матчи SET счет_команды_1 = ?, счет_команды_2 = ? WHERE id_матча = ?");
        $stmt->bind_param("iii", $match['score1'], $match['score2'], $match['matchId']);
        if ($stmt->execute()) {
            $response['updated']++;
        }
        $stmt->close();
    }
    
    $conn->commit();
    echo json_encode($response);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>