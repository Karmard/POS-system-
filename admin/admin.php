<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch admin username
$stmt = $conn->prepare("SELECT username FROM admins WHERE admin_id = ?");
if (!$stmt) {
    die("Failed to prepare statement: " . $conn->error);
}
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Failed to execute statement: " . $stmt->error);
}
$admin = $result->fetch_assoc();
$username = $admin['username'];

$hour = date('G');

if ($hour >= 5 && $hour < 12) {
    $greeting = "Good morning, $username!";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good afternoon, $username!";
} elseif ($hour >= 17 && $hour < 21) {
    $greeting = "Good evening, $username!";
} else {
    $greeting = "Good night, $username!";
}

// Handle date filter
$filter_date = isset($_POST['filter_date']) ? $_POST['filter_date'] : date('Y-m-d');

// Fetch orders for the filtered date
$stmt = $conn->prepare("
    SELECT o.order_id, m.dish_name, m.price, o.order_time, u.username AS waiter
    FROM orders o
    JOIN menu m ON o.order_id = m.item_id
    JOIN users u ON o.user_id = u.user_id
    WHERE DATE(o.order_time) = ?
");
if (!$stmt) {
    die("Failed to prepare statement: " . $conn->error);
}
$stmt->bind_param("s", $filter_date);
$stmt->execute();
$orders_result = $stmt->get_result();
if (!$orders_result) {
    die("Failed to execute statement: " . $stmt->error);
}

// Check if there are orders for the selected date
$has_orders = $orders_result->num_rows > 0;

// Calculate total amount
$stmt_total = $conn->prepare("
    SELECT SUM(m.price) AS total_amount
    FROM orders o
    JOIN menu m ON o.order_id = m.item_id
    WHERE DATE(o.order_time) = ?
");
if (!$stmt_total) {
    die("Failed to prepare statement: " . $conn->error);
}
$stmt_total->bind_param("s", $filter_date);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
if (!$total_result) {
    die("Failed to execute statement: " . $stmt_total->error);
}
$total_row = $total_result->fetch_assoc();
$total_amount = $total_row['total_amount'] ?: 0;

$stmt->close();
$stmt_total->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="../images/1212-removebg-preview.png" alt="Restaurant Logo">
                <span>Lagos Suya Spot</span>
            </div>
            <div class="greeting">
                <p><?php echo htmlspecialchars($greeting); ?></p>
            </div>
            <div class="logout">
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </header>

    <div id="mySidebar" class="sidebar">
        <a href="admin.php"><i class="fas fa-home"></i> Home</a>
        <a href="manage.php"><i class="fas fa-cog"></i> My Employees</a>
        <a href="menu.php"><i class="fas fa-utensils"></i> Modify Menu</a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> Settings</a>
    </div>

    <div id="main">
        <span class="openbtn" onclick="toggleNav()">&#9776; Menu</span>

        <div class="content">
            <h2>Order History</h2>
            
            <!-- Filter Form -->
            <form method="post" action="">
                <label for="filter_date">Filter by Date:</label>
                <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                <button type="submit">Filter</button>
            </form>

            <!-- Orders Table -->
            <?php if ($has_orders): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Dish Name</th>
                            <th>Price</th>
                            <th>Order Time</th>
                            <th>Waiter/Waitress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['dish_name']); ?></td>
                            <td><?php echo number_format($order['price'], 2); ?></td>
                            <td><?php echo date('g:i A', strtotime($order['order_time'])); ?></td>
                            <td><?php echo htmlspecialchars($order['waiter']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Total Amount -->
                <div class="total-amount">
                    <p><strong>Total Amount for <?php echo htmlspecialchars($filter_date); ?>:</strong> <?php echo number_format($total_amount, 2); ?> </p>
                </div>
            <?php else: ?>
                <p class="no-orders-message">No sit-in orders on <?php echo htmlspecialchars($filter_date); ?>.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleNav() {
            const sidebar = document.getElementById("mySidebar");
            const mainContent = document.getElementById("main");
            const openBtn = document.querySelector(".openbtn");

            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-250px";
                mainContent.style.marginLeft = "0";
                openBtn.innerHTML = "&#9776; Menu";
            } else {
                sidebar.style.left = "0";
                mainContent.style.marginLeft = "250px";
                openBtn.innerHTML = "&times; Close";
            }
        }
    </script>
</body>
</html>
