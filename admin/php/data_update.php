<?php
$host = '185.244.173.217';
$user = 'root';
$password = 'kdyKYAXM1';
$dbname = 'phygital';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД']));
}
$conn->set_charset('utf8mb4');

header('Content-Type: application/json');

// Получаем и декодируем JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Проверяем структуру данных
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['error' => 'Invalid JSON: '.json_last_error_msg()]));
}

if (!isset($data['matches'])) {
    die(json_encode(['error' => 'Missing "matches" key']));
}

$response = [
    'success' => true,
    'teams' => [],
    'total_teams' => 0,
    'processed_matches' => 0,
    'updated_matches' => 0
];

try {
    $conn->begin_transaction();
    
    foreach ($data['matches'] as $match) {
        if (!isset($match['team1'], $match['team2'], $match['score1'], $match['score2'], $match['matchId'])) {
            continue;
        }
        
        // Собираем уникальные команды
        if (!in_array($match['team1'], $response['teams'])) {
            $response['teams'][] = $match['team1'];
        }
        
        if (!in_array($match['team2'], $response['teams'])) {
            $response['teams'][] = $match['team2'];
        }
        
        $response['processed_matches']++;
        
        // Обновляем матч в базе данных
        $stmt = $conn->prepare("UPDATE матчи SET счет_команды_1 = ?, счет_команды_2 = ? WHERE id_матча = ?");
        $stmt->bind_param("iii", $match['score1'], $match['score2'], $match['matchId']);
        if ($stmt->execute()) {
            $response['updated_matches']++;
        }
        $stmt->close();
    }
    
    $conn->commit();
    
    $response['total_teams'] = count($response['teams']);
    echo json_encode($response);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>





<?php

/*
try {
    $conn->begin_transaction();
    foreach ($input['matches'] as $match) {
        // Логирование полученных данных
        error_log("Обработка матча: " . print_r($match, true));

        // Ищем ID команд
        $stmt = $conn->prepare("SELECT id_команды FROM команды WHERE название_команды = ?");
        $stmt->bind_param('s', $match['team1']);
        $stmt->execute();
        $id1 = $stmt->get_result()->fetch_assoc()['id_команды'] ?? null;
        $stmt->close();

        $stmt = $conn->prepare("SELECT id_команды FROM команды WHERE название_команды = ?");
        $stmt->bind_param('s', $match['team2']);
        $stmt->execute();
        $id2 = $stmt->get_result()->fetch_assoc()['id_команды'] ?? null;
        $stmt->close();

        if (!$id1 || !$id2) {
            throw new Exception("Команда не найдена: " . ($id1 ? $match['team2'] : $match['team1']));
        }

        // Обновляем матч по ID команд и турниру
        $updateStmt = $conn->prepare("
            UPDATE матчи 
            SET счет_команды_1 = ?, счет_команды_2 = ?
            WHERE id_команды_1 = ? 
              AND id_команды_2 = ?
              AND id_турнира = 1
        ");
        $updateStmt->bind_param('iiii', $match['score1'], $match['score2'], $id1, $id2);
        if (!$updateStmt->execute()) {
            throw new Exception("Ошибка обновления: " . $updateStmt->error);
        }
        $updateStmt->close();
    }
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Обновлено записей: ' . count($input['matches'])]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
*/
?>