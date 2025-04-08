<?php
$host = 'mysql';
$username = 'user';
$password = 'kdyKYAXM1';
$db = 'phygital';

$sql = new mysqli($host, $username, $password, $db);
if ($sql->connect_error) {
    die("Ошибка подключения!");
}

$sql->set_charset("utf8mb4");
?>
