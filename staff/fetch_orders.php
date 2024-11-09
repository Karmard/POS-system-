<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];

$query = "SELECT position FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die('Invalid user ID.');
}

$position = $user['position'];
if ($position == 'cook') {
    $query = "SELECT order_items.*, menu.dish_name, menu.price, orders.table_id, orders.status AS order_status
              FROM order_items
              JOIN orders ON order_items.order_id = orders.order_id
              JOIN menu ON order_items.item_id = menu.item_id
              WHERE order_items.status = 'Pending' OR order_items.status = 'Ready'";
} else if ($position == 'waiter/waitress') {
    $query = "SELECT order_items.*, menu.dish_name, menu.price, orders.table_id, orders.status AS order_status
              FROM order_items
              JOIN orders ON order_items.order_id = orders.order_id
              JOIN menu ON order_items.item_id = menu.item_id
              WHERE orders.user_id = ? AND (order_items.status = 'Pending' OR order_items.status = 'Ready')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
}

$stmt = $conn->prepare($query);

if ($position == 'waiter/waitress') {
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();
$conn->close();

$groupedOrders = [];
foreach ($orders as $order) {
    $table_id = $order['table_id'];
    if (!isset($groupedOrders[$table_id])) {
        $groupedOrders[$table_id] = [];
    }
    $groupedOrders[$table_id][] = $order;
}

header('Content-Type: application/json');
echo json_encode($groupedOrders);
?>
