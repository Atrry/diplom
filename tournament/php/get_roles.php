<?php
require '../../admin/config.php'; // ИЗМЕНИТЬ ПУТЬ

header('Content-Type: application/json');

$stmt = $sql->prepare('SELECT * FROM роли_в_команде');
$stmt->execute();
$result = $stmt->get_result();
$roles = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($roles);
?>