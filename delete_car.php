<?php
//delete cars
  $conn = new mysqli("localhost", "root", "", "car_store");
        if ($conn->connect_error) {
        die("Invalid Connection " . $conn->connect_error);
        }
    $carId=$_GET['car_id'];
     $conn->query("DELETE FROM orders WHERE car_id = $carId");
    $stmt=$conn->prepare("Delete from cars where id=?");
    $stmt->bind_param("i",$carId);
    $stmt->execute();
    $stmt->close();
    header("Location: Admin_Dashboard.php");
    exit();
?>