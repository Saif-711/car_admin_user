<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require "connect.php";
$carId=intval($_GET['car_id']);
$userId=$_SESSION['user_id'];
$sql=$conn->prepare("DELETE FROM favourites WHERE car_id=? AND user_id=?");
$sql->bind_param("ii",$carId,$userId);
$sql->execute();
$sql->close();
header("Location:dashboard.php");
?>