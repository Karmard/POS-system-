<?php
    session_start();
    include '../database/connection.php';

    if (!isset($_SESSION['user_id'])) 
    {
        die('User not logged in.');
    }

    $user_id = $_SESSION['user_id'];

    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) 
    {
        $user = $result->fetch_assoc();
        $username = htmlspecialchars($user['username']);
    } 
    
    else 
    {
        $username = 'Guest';
    }

    $table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

    $query = "SELECT table_number FROM tables WHERE table_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $table_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $table = $result->fetch_assoc();

    if ($table) 
    {
        $table_number = htmlspecialchars($table['table_number']);
    } 
    
    else 
    {
        $table_number = 'Unknown';
    }

    $query = "SELECT * FROM menu";
    $result = $conn->query($query);
    $menu_items = [];
    if ($result->num_rows > 0) 
    {
        while ($row = $result->fetch_assoc()) 
        {
            $menu_items[] = $row;
        }
    } 
    
    else 
    {
        $menu_items[] = ['item_id' => 0, 'dish_name' => 'No Items Found', 'price' => 0, 'description' => '', 'image_path' => '']; // For debugging
    }

    $stmt->close();
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            background-image: url('../images/restaurant-background.jpg');
            background-size: cover;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header 
        {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: linear-gradient(90deg, #00796b 0%, #004d40 100%);
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .header .back-button 
        {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s;
        }

        .header .back-button:hover 
        {
            color: #b2dfdb;
        }

        .header .search-container 
        {
            flex: 1;
            text-align: center;
        }

        .header .search-container input 
        {
            width: 50%;
            padding: 8px;
            border: none;
            border-radius: 20px;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header .check-orders-button 
        {
            background: #fd1414;
            border: none;
            color: #fff;
            font-size: 1rem;
            padding: 8px 8px;
            border-radius: 50px;
            margin-right: 50px;
            cursor: pointer;
            transition: background-color 0.3s;
            flex-shrink: 0;
        }

        .header .check-orders-button:hover 
        {
            background-color: #00796b;
        }

        .menu-container 
        {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            padding: 80px 20px 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .menu-item 
        {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            text-align: center;
            padding: 10px;
            height: 250px;
        }

        .menu-item:hover 
        {
            transform: scale(1.05);
        }

        .menu-item img 
        {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .menu-details 
        {
            padding: 5px;
        }

        .dish-name 
        {
            font-size: 1.1rem;
            color: #00796b;
            font-weight: bold;
        }

        .price 
        {
            font-size: 1rem;
            color: #333;
        }

        .description 
        {
            font-size: 0.85rem;
            color: #666;
        }

        .highlight 
        {
            color: rgb(81, 215, 249);
            font-weight: bold; 
        }

        .modal 
        {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.4); 
        }

        .modal-content 
        {
            background-color: #fff;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #ccc;
            width: 90%; 
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .modal-title 
        {
            color: #00796b;
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
        }

        .close 
        {
            color: #00796b;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus 
        {
            color: #004d40;
        }

        .modal-content label 
        {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            color: #333;
        }

        .modal-content input,
        .modal-content textarea 
        {
            width: calc(100% - 20px);
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box; 
        }

        .modal-content textarea 
        {
            height: 100px;
        }

        .modal-content select 
            {
                width: calc(100% - 20px);
                margin-bottom: 15px;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 1rem;
                box-sizing: border-box; 
            }

        .submit-button 
        {
            display: block;
            width: 100%;
            background-color: #00796b;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .submit-button:hover 
        {
            background-color: #004d40;
        }

    </style>
</head>
<body>
    <div class="header">
        <button class="back-button" onclick="window.location.href='tables.php'">&larr; Back</button>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search menu...">
        </div>
        <button class="check-orders-button" onclick="window.location.href='preview.php?table_id=<?php echo $table_id; ?>'">Preview</button>
    </div>
    <h1 style="text-align: center; margin: 80px 20px 20px; color: #00796b;">
        You are taking order for table <span class="highlight"><?php echo $table_number; ?></span>
    </h1>

    <div class="menu-container" id="menu-container">
        <!-- Menu items will be populated here by JavaScript -->
    </div>

    <!-- Modal -->
    <div id="order-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-title">Additional info for [Dish Name]</h2>
            <form id="order-form">
                <input type="hidden" id="item-id" name="item_id">
                <input type="hidden" id="table-id" name="table_id" value="<?php echo $table_id; ?>">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" required>
                <label for="spice-level">Spice Level:</label>
                <select id="spice-level" name="spice-level" required>
                    <option value="">Select Spice Level</option>
                    <option value="Hot">Hot</option>
                    <option value="Medium">Medium</option>
                    <option value="Mild">Mild</option>
                    <option value="Normal">Normal</option>
                </select>
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
                <button type="submit" class="submit-button">Add to Order</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const menuContainer = document.getElementById('menu-container');
            const modal = document.getElementById('order-modal');
            const closeModal = document.querySelector('.close');
            const orderForm = document.getElementById('order-form');
            const modalTitle = document.querySelector('.modal-title');

            function fetchMenuItems(query = '') {
                fetch('fetch_menu.php?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => 
                    {
                        let html = '';
                        if (data.length) {
                            data.forEach(item => 
                            {
                                html += `
                                    <div class="menu-item" data-item-id="${item.item_id}" data-dish-name="${item.dish_name}">
                                        <img src="${item.image_path}" alt="${item.dish_name}">
                                        <div class="menu-details">
                                            <div class="dish-name">${item.dish_name}</div>
                                            <div class="price">Ksh.${parseFloat(item.price).toFixed(2)}</div>
                                            <div class="description">${item.description}</div>
                                        </div>
                                    </div>
                                `;
                            });
                        } 
                        
                        else 
                        {
                            html = '<p>No menu items found.</p>';
                        }
                        menuContainer.innerHTML = html;
                    })
                    .catch(error => console.error('Error fetching menu items:', error));
            }

            fetchMenuItems();

            searchInput.addEventListener('input', function() 
            {
                const query = searchInput.value.trim();
                fetchMenuItems(query);
            });

            menuContainer.addEventListener('click', function(event) 
            {
                const item = event.target.closest('.menu-item');
                if (item) 
                {
                    const itemId = item.getAttribute('data-item-id');
                    const dishName = item.getAttribute('data-dish-name');
                    document.getElementById('item-id').value = itemId;
                    modalTitle.textContent = `Additional info for ${dishName}`;
                    modal.style.display = 'block';
                }
            });

            // Modal close logic
            function closeModalFunction() 
            {
                modal.style.display = 'none';
            }

            closeModal.addEventListener('click', closeModalFunction);
            window.addEventListener('click', function(event) 
            {
                if (event.target === modal) 
                {
                    closeModalFunction();
                }
            });

            //  form submission
            orderForm.addEventListener('submit', function(event) 
            {
                event.preventDefault();
                const formData = new FormData(orderForm);
                fetch('add_pending.php', 
                {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('Order added successfully')) 
                    {
                        alert('Order added successfully!');
                        closeModalFunction();
                    } 
                    
                    else 
                    {
                        alert('Failed to add order.');
                    }
                })
                .catch(error => 
                {
                    console.error('Error:', error);
                    alert('An error occurred.');
                });
            });
        });
    </script>
</body>
</html>
