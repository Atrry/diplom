<?php
$host = '185.244.173.217';
$user = 'root';
$password = 'kdyKYAXM1';
$dbname = 'phygital';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Ошибка подключения: " . $conn->connect_error);

$query = "SELECT id_турнира as id, название_турнира as name, статус as status FROM турниры ORDER BY дата_начала DESC";
$result = $conn->query($query);

$tournaments = [];
while ($row = $result->fetch_assoc()) {
    $tournaments[] = $row;
}

header('Content-Type: application/json');
echo json_encode($tournaments, JSON_UNESCAPED_UNICODE);
$conn->close();
?>