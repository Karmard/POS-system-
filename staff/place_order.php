<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;

if ($table_id > 0) {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert data from pending_orders to orders
        $query = "INSERT INTO orders (table_id, item_id, quantity, spice_level, description, price, total_price)
                  SELECT table_id, item_id, quantity, spice_level, description, price, (price * quantity) as total_price
                  FROM pending_orders WHERE table_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('i', $table_id);
        $stmt->execute();

        // Check if rows were inserted
        if ($stmt->affected_rows === 0) {
            throw new Exception('No rows inserted into orders table.');
        }

        // Delete the records from pending_orders
        $query = "DELETE FROM pending_orders WHERE table_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('i', $table_id);
        $stmt->execute();

        // Check if rows were deleted
        if ($stmt->affected_rows === 0) {
            throw new Exception('No rows deleted from pending_orders table.');
        }

        // Commit the transaction
        $conn->commit();

        // Respond with success
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        error_log($e->getMessage()); // Log the error for debugging
        echo json_encode(['status' => 'error', 'message' => 'Transaction failed.']);
    } finally {
        $stmt->close();
        $conn->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid table ID.']);
}
?>
