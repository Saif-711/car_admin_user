<?php
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
        header("Location: login.php");
        exit();
    }
    if(isset($_GET['id'])){
        $userId = intval($_GET['id']);
        $conn = new mysqli("localhost", "root", "", "car_store");
        if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: Admin_Dashboard.php");
            exit();
        } else {
            echo "there is an error with delete the user" . htmlspecialchars($conn->error);
            $stmt->close();
            $conn->close();
            exit();
        }
    }
?>



