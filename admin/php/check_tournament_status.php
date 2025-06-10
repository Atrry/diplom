<?php
$host = '185.244.173.217';
$user = 'root';
$password = 'kdyKYAXM1';
$dbname = 'phygital';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Ошибка подключения: " . $conn->connect_error);

$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : 0;
if ($tournament_id <= 0) {
    die(json_encode(['error' => 'Неверный ID турнира']));
}

$query = "SELECT статус as status FROM турниры WHERE id_турнира = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['status' => $row['status']], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'Турнир не найден']);
}

$conn->close();
?>