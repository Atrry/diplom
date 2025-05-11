<?php
$host = '185.244.173.217'; //mysql
$username = 'user';
$password = 'kdyKYAXM1';
$db = 'phygital';

/*$host = '127.0.0.1';
$username = 'root';
$password = '';
$db = 'phygital';*/

$sql = new mysqli($host, $username, $password, $db);
if ($sql->connect_error) {
    die("Ошибка подключения!");
}

$sql->set_charset("utf8mb4");
?>
