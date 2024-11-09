<?php
include '../database/connection.php';

$query = isset($_GET['q']) ? $_GET['q'] : '';

$stmt = $conn->prepare("SELECT * FROM menu WHERE dish_name LIKE CONCAT('%', ?, '%')");
$stmt->bind_param('s', $query);
$stmt->execute();
$result = $stmt->get_result();

$menu_items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($menu_items);

$stmt->close();
$conn->close();
