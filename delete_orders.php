<?php
        
        $conn = new mysqli("localhost", "root", "", "car_store");
        if ($conn->connect_error) {
        die("Invalid Connection " . $conn->connect_error);
        }
        $orderId=intval($_GET['order_id']);
        $stmt=$conn->prepare("Delete from orders where id=?");
        $stmt->bind_param("i",$orderId);
        $stmt->execute();
        $stmt->close();
        header("Location:Admin_Dashboard.php#requests")

?>