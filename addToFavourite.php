<?php
    session_start();
    require 'connect.php';
    if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit();
    }
    if(isset($_SESSION['role'])){
        if($_SESSION['role']!=='USER'){
            header("Location: login.php");
            exit();
        }
    }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $carId = intval($_POST['car_id']);
    $userId = $_SESSION['user_id'] ?? null;

    if ($carId && $userId) {
        $stmt = $conn->prepare("SELECT id FROM favourites WHERE user_id = ? AND car_id = ?");
        $stmt->bind_param("ii", $userId, $carId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO favourites(user_id, car_id) VALUES (?, ?)");
            $insert->bind_param("ii", $userId, $carId);
            $insert->execute();
        }
        header("Location: index.php");
        exit;
    }
}

?>