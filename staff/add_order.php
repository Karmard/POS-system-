<?php
    session_start();
    include '../database/connection.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo 'User not logged in.';
            exit;
        }

        // Get POST data
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        $spice_level = isset($_POST['spice-level']) ? htmlspecialchars($_POST['spice-level']) : '';
        $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';

        // Validate input
        if ($item_id <= 0 || $table_id <= 0 || $quantity <= 0 || empty($spice_level)) {
            echo 'Invalid input.';
            exit;
        }

        // Prepare and execute the SQL query
        $query = "INSERT INTO orders (item_id, table_id, quantity, spice_level, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiiss', $item_id, $table_id, $quantity, $spice_level, $description);

        if ($stmt->execute()) {
            echo 'Order added successfully';
        } else {
            echo 'Failed to add order. Error: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        echo 'Invalid request method.';
    }

    $conn->close();
?>
