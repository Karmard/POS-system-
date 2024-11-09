<?php
include '../database/connection.php';

// Get the search query from the URL
$query = isset($_GET['query']) ? $_GET['query'] : '';

$response = [];

// Prepare the SQL statement to search for menu items
if ($query) {
    // Search for items where dish_name contains the query
    $stmt = $conn->prepare("SELECT * FROM menu WHERE dish_name LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    $stmt->close();
} else {
    // No query provided, fetch all items
    $stmt = $conn->prepare("SELECT * FROM menu");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    $stmt->close();
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
