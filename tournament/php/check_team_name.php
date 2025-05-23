<?php
require '../../admin/config.php'; // ИЗМЕНИТЬ ПУТЬ

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$teamName = $data['team_name'] ?? '';

$stmt = $sql->prepare('SELECT COUNT(*) as count FROM команды WHERE название_команды = ?');
$stmt->bind_param('s', $teamName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['is_unique' => $row['count'] === 0]);
?>