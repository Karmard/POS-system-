<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch total number of employees
$count_query = "SELECT COUNT(*) AS total FROM users";
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_employees = $count_row['total'];

// Fetch users from the database
$query = "SELECT * FROM users";
$result = $conn->query($query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (isset($_POST['add_user'])) 
    {
        // Add user logic
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $position = $_POST['position'];
        $gender = $_POST['gender'];

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, position, gender) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $password, $position, $gender);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "User added successfully!";
        header("Location: manage.php");
        exit();
    }

    if (isset($_POST['update_user'])) 
    {
        // Update user logic
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $position = $_POST['position'];
        $gender = $_POST['gender'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, position = ?, gender = ?, status = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $username, $email, $position, $gender, $status, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "User updated successfully!";
        header("Location: manage.php");
        exit();
    }

    if (isset($_POST['suspend_user']) || isset($_POST['unsuspend_user'])) 
        {
            $user_id = $_POST['user_id'];

            $stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $current_status = $user['status'];
            $new_status = ($current_status === 'active') ? 'suspended' : 'active';

            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_status, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "User status updated successfully!";
            header("Location: manage.php");
            exit();
        }

    if (isset($_POST['delete_user']))
    {
        // Delete user logic
        $user_id = $_POST['user_id'];

        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "User deleted successfully!";
        header("Location: manage.php");
        exit();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <style>
        .form-container {
            margin-top: 80px; 
            padding: 20px;
            background: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 1000px;
            margin: 80px auto 20px auto;
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .form-horizontal {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-horizontal div {
            flex: 1 1 45%;
        }

        .form-horizontal label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-horizontal input, .form-horizontal select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-horizontal button {
            background-color: #00796b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .form-horizontal button:hover {
            background-color: #004d40;
        }

        .table-container 
        {
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 70%;
            overflow-x: auto;
        }

        table 
        {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th 
        {
            padding: 12px 15px;
            text-align: left;
            background-color: #00796b;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #004d40; 
        }

        td 
        {
            padding: 12px 15px;
            text-align: left;
            border: none;
        }

        tr:nth-child(even) 
        {
            background-color: #f9f9f9;
        }

        tr:hover 
        {
            background-color: #e0f2f1;
        }

        input[type="text"], 
        input[type="email"], 
        input[type="password"], 
        select 
        {
            border: none; 
            padding: 6px 12px;
            border-radius: 4px;
            outline: none; 
            box-shadow: none;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="text"]:focus, 
        input[type="email"]:focus, 
        input[type="password"]:focus, 
        select:focus {
            border: none; 
            box-shadow: none;
        }

        .actions {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .actions button 
        {
            background: #00796b;
            color: #fff;
            border: none; 
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .actions button:hover 
        {
            background: #004d40;
            transform: scale(1.05);
        }

        .actions .edit 
        {
            background: #ffa000;
        }

        .actions .suspend 
        {
            background: #ff6f00;
        }

        .actions .terminate 
        {
            background: #c62828;
        }

        .actions .delete 
        {
            background: #b71c1c;
        }

</style>


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
        <h2>Sign Up new Employee</h2>
        <form method="POST" class="form-horizontal">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="position">Position:</label>
                <select id="position" name="position" required>
                    <option value="cook">Cook</option>
                    <option value="waiter/waitress">Waiter/Waitress</option>
                    <option value="IT">IT</option>
                </select>
            </div>
            <div>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <button type="submit" name="add_user">Add User</button>
            </div>
        </form>
    </div>

    <div class="table-container">
    <h2>My Employees (<?php echo $total_employees; ?>)</h2>
    <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <form method="POST" class="update-form">
                        <td><input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></td>
                        <td><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></td>
                        <td>
                            <select name="position" required>
                                <option value="cook" <?php echo $user['position'] === 'cook' ? 'selected' : ''; ?>>Cook</option>
                                <option value="waiter/waitress" <?php echo $user['position'] === 'waiter/waitress' ? 'selected' : ''; ?>>Waiter/Waitress</option>
                                <option value="IT" <?php echo $user['position'] === 'IT'? 'selected' : ''; ?>>IT<option>
                            </select>
                        </td>
                        <td>
                            <select name="gender" required>
                                <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $user['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </td>
                        <td>
                            <select name="status" required>
                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                <option value="terminated" <?php echo $user['status'] === 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                            </select>
                        </td>
                        <td class="actions">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <button type="submit" name="update_user">Save</button>
                            <?php if ($user['status'] === 'active'): ?>
                                <button type="submit" name="suspend_user" class="suspend">Suspend</button>
                            <?php else: ?>
                                <button type="submit" name="unsuspend_user" class="suspend">Unsuspend</button>
                            <?php endif; ?>
                            <button type="submit" name="delete_user" class="delete">Delete</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div id="notification" style="display: none; position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); padding: 15px; background-color: #ffeb3b; color: #000; border-radius: 4px; z-index: 1000; transition: opacity 0.5s ease; text-align: center;"></div>
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

                function showNotification(message) 
                    {
                        var notification = document.getElementById("notification");
                        notification.textContent = message;
                        notification.style.display = 'block';
                        setTimeout(function() 
                        {
                            notification.style.opacity = 0;
                            setTimeout(function() 
                            {
                                notification.style.display = 'none';
                                notification.style.opacity = 1;
                            }, 500);
                        }, 3000);
                    }


                    <?php if (isset($_SESSION['message'])): ?>
                        showNotification('<?php echo htmlspecialchars($_SESSION['message']); ?>');
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
            </script>
            
</body>
</html>
