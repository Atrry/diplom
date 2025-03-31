<?php
$host = 'localhost';
$username = 'root';
$password = '';
$db = 'phygital';

$sql = new mysqli($host, $username, $password, $db);
if ($sql->connect_error) {
    die("Ошибка подключения!");
}

$sql->set_charset("utf8mb4");
?>