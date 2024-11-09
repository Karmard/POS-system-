<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add new food item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $dish_name = $_POST['dish_name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_name = $_FILES['image']['name'];
            $image_path = '../uploads/' . $image_name;
            move_uploaded_file($image_tmp_name, $image_path);
        } else {
            $image_path = null;
        }

        $stmt = $conn->prepare("INSERT INTO menu (dish_name, price, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $dish_name, $price, $description, $image_path);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Food item added successfully!";
        header("Location: menu.php");
        exit();
    }

    // Update food item
    if (isset($_POST['update_item'])) {
        $item_id = $_POST['item_id'];
        $dish_name = $_POST['dish_name'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_name = $_FILES['image']['name'];
            $image_path = '../uploads/' . $image_name;
            move_uploaded_file($image_tmp_name, $image_path);
        } else {
            $image_path = $_POST['existing_image']; // Keep existing image if no new image is uploaded
        }

        $stmt = $conn->prepare("UPDATE menu SET dish_name = ?, price = ?, description = ?, image_path = ? WHERE item_id = ?");
        $stmt->bind_param("sdssi", $dish_name, $price, $description, $image_path, $item_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Food item updated successfully!";
        header("Location: menu.php");
        exit();
    }

    // Delete food item
    if (isset($_POST['delete_item'])) {
        $item_id = $_POST['item_id'];

        $stmt = $conn->prepare("DELETE FROM menu WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Food item deleted successfully!";
        header("Location: menu.php");
        exit();
    }
}

// Fetch menu items
$query = "SELECT * FROM menu";
$result = $conn->query($query);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <style>
        /* Your existing CSS styles here */
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="../images/1212-removebg-preview.png" alt="Restaurant Logo">
                <span>Lagos Suya Spot</span>
            </div>
            <div class="logout">
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </header>

    <div id="mySidebar" class="sidebar">
        <a href="admin.php"><i class="fas fa-home"></i> Home</a>
        <a href="manage.php"><i class="fas fa-users"></i> Manage Users</a>
        <a href="menu.php"><i class="fas fa-utensils"></i> Modify Menu</a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> Settings</a>
    </div>

    <div id="main">
        <span class="openbtn" onclick="toggleNav()">&#9776; Menu</span>
    </div>

    <div class="form-container">
        <h2>Add New Food Item</h2>
        <form method="POST" enctype="multipart/form-data">
            <div>
                <label for="dish_name">Name of Food:</label>
                <input type="text" id="dish_name" name="dish_name" required>
            </div>
            <div>
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div>
                <label for="image">Image:</label>
                <input type="file" id="image" name="image">
            </div>
            <div>
                <button type="submit" name="add_item">Add Item</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <h2>Menu Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $result->fetch_assoc()): ?>
                <tr>
                    <form method="POST" enctype="multipart/form-data">
                        <td>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Image" width="100">
                        </td>
                        <td>
                            <input type="text" name="dish_name" value="<?php echo htmlspecialchars($item['dish_name']); ?>" required>
                        </td>
                        <td>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($item['price']); ?>" step="0.01" required>
                        </td>
                        <td>
                            <textarea name="description"><?php echo htmlspecialchars($item['description']); ?></textarea>
                        </td>
                        <td>
                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($item['image_path']); ?>">
                            <input type="file" name="image">
                            <button type="submit" name="update_item">Update</button>
                            <button type="submit" name="delete_item">Delete</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
