<?php
    session_start();
    include '../database/connection.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_item'])) {
            $dish_name = $_POST['dish_name'];
            $price = $_POST['price'];
            $description = $_POST['description'];
            
            // IMAGE UPLOAD
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) {
                    die("Upload directory does not exist.");
                }
                if (!is_writable($upload_dir)) {
                    die("Upload directory is not writable.");
                }

                $image_tmp_name = $_FILES['image']['tmp_name'];
                $image_name = basename($_FILES['image']['name']);
                $image_path = $upload_dir . $image_name;

                if (move_uploaded_file($image_tmp_name, $image_path)) {

                } else {
                    die("Failed to upload image.");
                }
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

        //FOOD ITEM UPDATE
        if (isset($_POST['update_item'])) {
            $item_id = $_POST['item_id'];
            $dish_name = $_POST['dish_name'];
            $price = $_POST['price'];
            $description = $_POST['description'];
            $existing_image = $_POST['existing_image'];

            if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) 
                {
                    die("Upload directory does not exist.");
                }
                if (!is_writable($upload_dir)) 
                {
                    die("Upload directory is not writable.");
                }

                $new_image_name = $upload_dir . basename($_FILES['new_image']['name']);
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $new_image_name)) {
                    $new_image_path = $new_image_name;
                } else {
                    die("Failed to upload image.");
                }
            } 
            else 
            {
                $new_image_path = $existing_image;
            }

            $stmt = $conn->prepare("UPDATE menu SET dish_name = ?, price = ?, description = ?, image_path = ? WHERE item_id = ?");
            $stmt->bind_param("sdssi", $dish_name, $price, $description, $new_image_path, $item_id);
            $stmt->execute();
            $_SESSION['message'] = "Food item updated successfully!";
            $stmt->close();
            header("Location: menu.php");
            exit();
        }

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

            #main 
            {
                padding: 2rem;
            }

            .form-container 
            {
                background-color: #ffffff;
                padding: 2rem;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                max-width: 1200px;
                margin: 0 auto;
            }

            .form-container h2 
            {
                margin-bottom: 1rem;
                font-size: 1.5rem;
            }

            .form-container form 
            {
                display: flex;
                flex-wrap: wrap;
                gap: 1.8rem;
            }

            .form-container form div 
            {
                flex: 1;
                min-width: 200px; 
            }

            .form-container label 
            {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: bold;
            }

            .form-container input[type="text"],
            .form-container input[type="number"],
            .form-container textarea,
            .form-container input[type="file"] 
            {
                width: 100%;
                padding: 0.8rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1rem;
            }

            .form-container textarea 
            {
                height: 19px;
            }

            .form-container button 
            {
                background-color: #4CAF50;
                color: white;
                padding: 0.75rem 1.8rem;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1rem;
                margin-top: 1.8rem;
                flex-basis: 100%;
            }

            .form-container button:hover 
            {
                background-color: #45a049;
            }

        .table-container 
        {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table-container h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .table-container th, .table-container td {
            padding: 1rem;
            text-align: left;
            vertical-align: middle;
        }

        .table-container th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            border-bottom: 2px solid #ddd;
        }

        .table-container tr:not(:last-child) {
            border-bottom: 1px solid #ddd;
        }

        .table-container tr:hover {
            background-color: #f9f9f9;
        }

        .table-container img {
            max-width: 100px;
            height: 50px;
            border-radius: 4px;
        }

        .table-container input[type="text"],
        .table-container input[type="number"],
        .table-container textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .table-container textarea {
            height: 20px;
            resize: vertical;
        }

        .table-container button {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            text-transform: uppercase;
        }

        .table-container button:hover {
            background-color: #c0392b;
        }

        .table-container .edit-btn {
            background-color: #3498db;
        }

        .table-container .edit-btn:hover {
            background-color: #2980b9;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .search-container input[type="text"] {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
            font-size: 1rem;
        }

        .search-container button {
            background-color: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-left: 0.5rem;
        }

        .search-container button:hover {
            background-color: #45a049;
        }

        .search-results {
            margin: 1rem auto;
            max-width: 1200px;
        }

                /* Custom popup styles */
                .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .popup-content button {
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 1.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }
        .popup-content button:hover {
            background-color: #45a049;
        }

    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="../images/1212-removebg-preview.png" alt="Restaurant Logo">
                <span>Lagos Suya Spot</span>
            </div>

            <div class="search-container">
            <input type="text" id="search-bar" placeholder="Search food items..." oninput="searchItems()">
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
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Image" width="100" id="image-<?php echo $item['item_id']; ?>" onclick="triggerFileInput(<?php echo $item['item_id']; ?>)" style="cursor: pointer;">
                            <input type="file" name="new_image" accept="image/*" style="display:none;" id="file-input-<?php echo $item['item_id']; ?>" onchange="previewImage(<?php echo $item['item_id']; ?>)">
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
                            <button type="submit" name="update_item">Update</button>
                            <button type="submit" name="delete_item">Delete</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>


        </table>
    </div>

    
    <!-- Custom Popup -->
    <div id="popup" class="popup">
        <div class="popup-content">
            <p id="popup-message">Message goes here</p>
            <button onclick="closePopup()">OK</button>
        </div>
    </div>

    <script>
    function triggerFileInput(itemId) {
        document.getElementById('file-input-' + itemId).click();
    }

    function previewImage(itemId) {
        const fileInput = document.getElementById('file-input-' + itemId);
        const imgElement = document.getElementById('image-' + itemId);
        const reader = new FileReader();

        reader.onload = function(e) {
            imgElement.src = e.target.result;
        }

        if (fileInput.files[0]) {
            reader.readAsDataURL(fileInput.files[0]);
        }
    }
</script>

<script>
function searchItems() {
    const searchTerm = document.getElementById('search-bar').value;

    if (searchTerm.length >= 3) {
        // Perform search if input has 3 or more characters
        fetch(`search.php?query=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => updateTable(data))
            .catch(error => console.error('Error:', error));
    } else if (searchTerm.length === 0) {
        // Refresh the page to show all items if search input is cleared
        window.location.href = 'menu.php';
    }
}

function updateTable(items) {
    const tableBody = document.querySelector('.table-container tbody');
    tableBody.innerHTML = ''; // Clear existing rows

    items.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${item.image_path}" alt="Image" width="100"></td>
            <td>${item.dish_name}</td>
            <td>${item.price}</td>
            <td>${item.description}</td>
            <td>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" value="${item.item_id}">
                    <input type="hidden" name="existing_image" value="${item.image_path}">
                    <button type="submit" name="update_item">Update</button>
                    <button type="submit" name="delete_item">Delete</button>
                </form>
            </td>
        `;
        tableBody.appendChild(row);
    });
}
</script>

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

        function showPopup(message) {
            const popup = document.getElementById('popup');
            const popupMessage = document.getElementById('popup-message');
            popupMessage.textContent = message;
            popup.style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['message'])): ?>
                showPopup('<?php echo $_SESSION['message']; ?>');
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        });

    </script>



</body>
</html>
