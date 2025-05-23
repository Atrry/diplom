<?php
require '../../admin/config.php'; // ИЗМЕНИТЬ ПУТЬ

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';

$stmt = $sql->prepare('SELECT COUNT(*) as count FROM команды WHERE контактный_телефон = ?');
$stmt->bind_param('s', $phone);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['is_unique' => $row['count'] === 0]);
?>