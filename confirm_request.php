<?php
// accept_order.php
require 'connect.php';

if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);
    
    // Update order status to accepted
    $stmt = $conn->prepare("UPDATE orders SET status = 'accepted' WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();    
    header("Location: Admin_Dashboard.php#requests"); // redirect back to admin page
    exit;
} else {
    echo "Invalid request";
}
?>
