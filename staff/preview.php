<?php
session_start();
include '../database/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Fetch the username
$query = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['username'] = htmlspecialchars($user['username']);
} else {
    $_SESSION['username'] = 'Guest';
    die('Invalid user ID.');
}

// Fetch the table number
$query = "SELECT table_number FROM tables WHERE table_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $table_id);
$stmt->execute();
$result = $stmt->get_result();
$table = $result->fetch_assoc();

if ($table) {
    $_SESSION['table_number'] = htmlspecialchars($table['table_number']);
} else {
    $_SESSION['table_number'] = 'Unknown';
    die('Invalid table ID.');
}

// Handle form submissions for editing or removing orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = intval($_POST['order_id']);

    if ($_POST['action'] === 'update') {
        $quantity = intval($_POST['quantity']);
        $spice_level = htmlspecialchars($_POST['spice_level']);
        $description = htmlspecialchars($_POST['description']);

        $query = "UPDATE pending_orders SET quantity = ?, spice_level = ?, description = ? WHERE order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issi', $quantity, $spice_level, $description, $order_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($_POST['action'] === 'remove') {
        $query = "DELETE FROM pending_orders WHERE order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $stmt->close();
    }

    // Return JSON response
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle placing the order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $total_amount = $_SESSION['total_amount'];

    // Check if user_id exists
    $query = "SELECT COUNT(*) FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        die('User ID does not exist.');
    }

    // Insert into `orders`
    $query = "INSERT INTO orders (table_id, total_amount, user_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('idi', $table_id, $total_amount, $user_id);
    $stmt->execute();
    $order_id = $stmt->insert_id; // Get the last inserted order_id
    $stmt->close();

    // Insert into `order_items`
    $query = "INSERT INTO order_items (order_id, item_id, quantity, spice_level, description)
              SELECT ?, item_id, quantity, spice_level, description
              FROM pending_orders
              WHERE table_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $order_id, $table_id);
    $stmt->execute();
    $stmt->close();

    // Update the status of pending_orders
    $query = "UPDATE pending_orders SET status = 'Completed' WHERE table_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $table_id);
    $stmt->execute();
    $stmt->close();

    // Clear session orders
    unset($_SESSION['orders']);
    unset($_SESSION['total_amount']);

    // Redirect or show a confirmation message
    header('Location: prepare.php'); // Adjust the redirection as needed
    exit();
}

// Fetch pending orders for the given table_id
$query = "SELECT pending_orders.*, menu.dish_name, menu.price 
          FROM pending_orders 
          JOIN menu ON pending_orders.item_id = menu.item_id 
          WHERE pending_orders.table_id = ? AND pending_orders.status = 'Pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $table_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    $row['total_price'] = $row['price'] * $row['quantity'];
    $orders[] = $row;
    $total_amount += $row['total_price'];
}

// Store orders data in session
$_SESSION['orders'] = $orders;
$_SESSION['total_amount'] = $total_amount;

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Preview</title>
    <style>
        /* General styles for the page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            background: linear-gradient(90deg, #00796b 0%, #004d40 100%);
            padding: 10px;
            color: #fff;
        }

        .total-amount {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
        }

        form {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        button[type="submit"] {
            padding: 10px 20px;
            margin: 0 auto;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            display: block;
            width: 50%;
            margin-top: 20px;
        }

        .back-button {
            font-size: 16px;
            color: #fff;
            background: linear-gradient(90deg, #c4f0eb 0%, #004d40 100%);
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            text-decoration: none;
            margin: 0;
        }

        .back-button:hover {
            background-color: #003d7a;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .highlight {
            color: #007BFF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f4f4f4;
        }

        .editable input,
        .editable select {
            border: none;
            background: #f9f9f9;
        }

        .actions button {
            margin-right: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .actions .edit-btn {
            background-color: #ffc107;
            color: white;
        }

        .actions .remove-btn {
            background-color: #dc3545;
            color: white;
        }

        .actions .edit-btn:hover {
            background-color: #e0a800;
        }

        .actions .remove-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="back-button" onclick="window.location.href='serve.php?table_id=<?php echo $table_id; ?>'">&larr; Back</button>
    </div>
    <h1>Pending <em>Orders</em> for Table <span class="highlight"><?php echo $_SESSION['table_number']; ?></span></h1>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Spice Level</th>
                    <th>Description</th>
                    <th>Total Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)) : ?>
                    <?php foreach ($orders as $order) : ?>
                        <tr data-order-id="<?php echo $order['order_id']; ?>">
                            <td><?php echo htmlspecialchars($order['dish_name']); ?></td>
                            <td class="editable"><input type="number" name="quantity" value="<?php echo $order['quantity']; ?>" min="1"></td>
                            <td class="editable">
                                <select name="spice_level">
                                    <option value="Mild" <?php echo $order['spice_level'] == 'Mild' ? 'selected' : ''; ?>>Mild</option>
                                    <option value="Medium" <?php echo $order['spice_level'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="Hot" <?php echo $order['spice_level'] == 'Hot' ? 'selected' : ''; ?>>Hot</option>
                                    <option value="Extra Hot" <?php echo $order['spice_level'] == 'Extra Hot' ? 'selected' : ''; ?>>Extra Hot</option>
                                </select>
                            </td>
                            <td class="editable"><input type="text" name="description" value="<?php echo htmlspecialchars($order['description']); ?>"></td>
                            <td><?php echo number_format($order['total_price'], 2); ?></td>
                            <td class="actions">
                                <button type="button" class="edit-btn" onclick="editOrder(<?php echo $order['order_id']; ?>)">Edit</button>
                                <button type="button" class="remove-btn" onclick="removeOrder(<?php echo $order['order_id']; ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No pending orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="total-amount">Total: $<?php echo number_format($total_amount, 2); ?></p>
        <button type="submit" name="place_order">Place Order</button>
    </form>

    <script>
        function editOrder(orderId) {
            const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
            const quantity = row.querySelector('input[name="quantity"]').value;
            const spiceLevel = row.querySelector('select[name="spice_level"]').value;
            const description = row.querySelector('input[name="description"]').value;

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('quantity', quantity);
            formData.append('spice_level', spiceLevel);
            formData.append('description', description);
            formData.append('action', 'update');

            fetch('', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Order updated successfully.');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function removeOrder(orderId) {
            if (!confirm('Are you sure you want to remove this order?')) {
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('action', 'remove');

            fetch('', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.querySelector(`tr[data-order-id="${orderId}"]`).remove();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
