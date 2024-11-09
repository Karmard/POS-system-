<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_item_id = filter_var($_POST['order_item_id'], FILTER_SANITIZE_NUMBER_INT);

    // Validate order_item_id
    if (!filter_var($order_item_id, FILTER_VALIDATE_INT)) {
        die('Invalid order item ID.');
    }

    // Fetch current status
    $query = "SELECT status FROM order_items WHERE order_item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $order_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_item = $result->fetch_assoc();

    if (!$order_item) {
        die('Order item not found.');
    }

    $current_status = $order_item['status'];

    // Determine new status
    if ($current_status == 'Pending') {
        $new_status = 'Ready';
    } else if ($current_status == 'Ready') {
        $new_status = 'Completed'; // Change to any appropriate status if needed
    } else {
        die('Invalid current status.');
    }

    // Update the order item status
    $query = "UPDATE order_items SET status = ? WHERE order_item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $new_status, $order_item_id);

    if ($stmt->execute()) {
        header('Location: prepare.php'); // Redirect back to the orders page
        exit();
    } else {
        die('Failed to update status.');
    }

    $stmt->close();
}

$conn->close();
?>
