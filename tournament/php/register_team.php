<?php
require '../../admin/config.php'; // ИЗМЕНИТЬ ПУТЬ

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $sql->begin_transaction();
    
    // Создаем команду
    $stmt = $sql->prepare('INSERT INTO команды (название_команды, контактное_лицо, контактный_телефон, контактный_email) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', 
        $data['team_name'], 
        $data['leader_name'], 
        $data['leader_phone'], 
        $data['leader_email']
    );
    $stmt->execute();
    $teamId = $sql->insert_id;
    
    // Добавляем участников
    $stmt = $sql->prepare('INSERT INTO заявка_на_турнир (id_команды, id_турнира, фио, id_роли, контактный_телефон, контактный_email) VALUES (?, ?, ?, ?, ?, ?)');
    
    foreach ($data['members'] as $member) {
        $stmt->bind_param('iisiss', 
            $teamId, 
            $data['tournament_id'], 
            $member['name'], 
            $member['role_id'], 
            $data['leader_phone'], 
            $data['leader_email']
        );
        $stmt->execute();
    }
    
    $sql->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $sql->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>