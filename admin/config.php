<?php
<<<<<<< HEAD
$host = '185.244.173.217';
$username = 'user';
$password = 'kdyKYAXM1';
=======
$host = 'localhost';
$username = 'root';
$password = '';
>>>>>>> ba75e01af8f40709fc7b64da51b8218d92825623
$db = 'phygital';

$sql = new mysqli($host, $username, $password, $db);
if ($sql->connect_error) {
    die("Ошибка подключения!");
}

$sql->set_charset("utf8mb4");
?>