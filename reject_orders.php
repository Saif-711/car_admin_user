<?php
$conn = new mysqli("localhost", "root", "", "car_store");
if ($conn->connect_error) {
    die("Invalid Connection: " . $conn->connect_error);
}
$orderId = intval($_GET['order_id']);
$stmt = $conn->prepare("UPDATE orders SET status = 'rejected' WHERE id = ?");
$stmt->bind_param("i", $orderId);
if ($stmt->execute()) {
    header("Location: Admin_Dashboard.php#requests");
    exit();
} else {
    echo "Error updating record: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
