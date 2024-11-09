<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id'])) 
{
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];

$query = "SELECT position FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) 
{
    die('Invalid user ID.');
}

$position = $user['position'];
if ($position == 'cook') 
{
    // Fetch all order items with their status
    $query = "SELECT order_items.*, menu.dish_name, menu.price, orders.table_id, orders.status AS order_status
              FROM order_items
              JOIN orders ON order_items.order_id = orders.order_id
              JOIN menu ON order_items.item_id = menu.item_id
              WHERE order_items.status = 'Pending' OR order_items.status = 'Ready'";
} 

else if ($position == 'waiter/waitress') 
{
    // Fetch orders placed by this user
    $query = "SELECT order_items.*, menu.dish_name, menu.price, orders.table_id, orders.status AS order_status
              FROM order_items
              JOIN orders ON order_items.order_id = orders.order_id
              JOIN menu ON order_items.item_id = menu.item_id
              WHERE orders.user_id = ? AND (order_items.status = 'Pending' OR order_items.status = 'Ready')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
}

$stmt = $conn->prepare($query);

if ($position == 'waiter/waitress') 
{
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$orders = [];

if ($result->num_rows == 0) 
{
    die('No orders found.');
}

while ($row = $result->fetch_assoc()) 
{
    $orders[] = $row;
}

$stmt->close();
$conn->close();

$groupedOrders = [];
foreach ($orders as $order) 
{
    $table_id = $order['table_id'];
    if (!isset($groupedOrders[$table_id])) 
    {
        $groupedOrders[$table_id] = [];
    }
    $groupedOrders[$table_id][] = $order;
}

if (empty($groupedOrders)) 
{
    die('No grouped orders found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2">

    <title>Order Preparation</title>
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header 
        {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            background: linear-gradient(90deg, #00796b 0%, #004d40 100%);
            padding: 10px;
            color: #fff;
        }
        .header .back-button 
        {
            display: <?php echo ($position == 'cook') ? 'none' : 'inline'; ?>;
        }
        h1 
        {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .back-button 
        {
            background-color: #00796b; 
            color: #ffffff; 
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 1em; 
            font-weight: bold;
            cursor: pointer; 
            text-decoration: none;
            display: inline-flex; 
            align-items: center; 
            transition: background-color 0.3s ease, transform 0.3s ease; 
        }

        .back-button:hover 
        {
            background-color: #004d40;
            transform: scale(1.05); 
        }

        .back-button::before 
        {
            content: '\2190'; 
            margin-right: 8px;
            font-size: 1.2em; 
        }

        table 
        {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td 
        {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th 
        {
            background-color: #f4f4f4;
        }
        .actions button 
        {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            color: white;
        }
        .actions .pending-btn 
        {
            background-color: #007bff;
        }
        .actions .ready-btn 
        {
            background-color: #28a745;
        }
        .actions .pending-btn:hover 
        {

            background-color: #0056b3;
        }
        .actions .ready-btn:hover 
        {
            background-color: #218838;
        }
        .table-section 
        {
            margin: 20px;
        }
        .table-header 
        {
            background-color: #00796b;
            color: white;
            padding: 10px;
            font-size: 1.2em;
            border-radius: 5px;
        }
        
        button.pending-btn,
        button.ready-btn 
        {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            color: white;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button.pending-btn 
        {
            background-color: #ffc107;
        }

        button.pending-btn:hover 
        {
            background-color: #e0a800;
            transform: scale(1.05);
        }

        button.ready-btn 
        {
            background-color: #28a745; 
        }

        button.ready-btn:hover 
        {
            background-color: #218838;
            transform: scale(1.05);
        }

        .status 
        {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: capitalize;
            font-size: 0.9em;
            text-align: center;
        }

        .status.pending 
        {
            background-color: #ffc107;
            color: #212529; 
        }

        .status.ready 
        {
            background-color: #28a745;
            color: #fff; 
        }

        .status:hover 
        {
            opacity: 0.9;
            cursor: pointer;
        }
    </style>

    <script>
        function confirmUpdate(form) 
        {
            const confirmation = confirm("Are you sure the food is ready?");
            if (confirmation) {
                form.submit();
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <?php if ($position != 'cook'): ?>
            <button class="back-button" onclick="window.location.href='tables.php'">Back</button>
            <?php endif; ?>
    </div>
    <h1>Active <em>Orders</em></h1>

    <?php foreach ($groupedOrders as $table_id => $orders): ?>
        <div class="table-section">
            <div class="table-header">Table Number: <?php echo htmlspecialchars($table_id); ?></div>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Spice Level</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['dish_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['spice_level']); ?></td>
                            <td><?php echo htmlspecialchars($order['description']); ?></td>
                            <td>
                                <?php if ($position == 'cook'): ?>
                                    <form method="post" action="status.php" style="display:inline;">
                                        <input type="hidden" name="order_item_id" value="<?php echo htmlspecialchars($order['order_item_id']); ?>">
                                        <?php if ($order['status'] == 'Pending'): ?>
                                            <button type="button" class="pending-btn" onclick="confirmUpdate(this.form)">Preparing..</button>
                                        <?php else: ?>
                                            <button type="button" class="ready-btn" onclick="confirmUpdate(this.form)">Ready</button>
                                        <?php endif; ?>
                                    </form>
                                    

                                    <?php else: ?>
                                        <span class="status <?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    <?php endif; ?>


                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</body>
</html>
