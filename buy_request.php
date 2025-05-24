<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $carId = intval($_POST['car_id']);
    $userId = $_SESSION['user_id'];

    $conn = new mysqli("localhost", "root", "", "car_store");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

  
    $check = $conn->prepare("SELECT * FROM orders WHERE car_id = ? AND user_id = ?");
    $check->bind_param("ii", $carId, $userId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        header("Location: index.php?msg=Request_already_sent");
    } else {
 
        $stmt = $conn->prepare("INSERT INTO orders (user_id, car_id, order_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $userId, $carId);

        if ($stmt->execute()) {
            header("Location: index.php?msg=Request_sent_successfully");
        } else {
            header("Location: index.php?msg=Error_sending_request");
        }
    }

    $conn->close();
} else {
    header("Location: index.php?msg=Invalid_request");
}
