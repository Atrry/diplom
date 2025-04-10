<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'];

$stmt = $sql->prepare("DELETE FROM новости WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header('Location: dashboard.php');
exit();
?>