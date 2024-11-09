<?php
session_start();
include '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $table_id = $_POST['table_id'];
    $quantity = $_POST['quantity'];
    $spice_level = $_POST['spice-level'];
    $description = $_POST['description'];

    $query = "INSERT INTO pending_orders (table_id, item_id, quantity, spice_level, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiiss', $table_id, $item_id, $quantity, $spice_level, $description);
    
    if ($stmt->execute()) {
        echo 'Order added successfully.';
    } else {
        echo 'Error: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
