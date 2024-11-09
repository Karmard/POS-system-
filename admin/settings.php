<?php
session_start();
include '../database/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch current admin details
$stmt = $conn->prepare("SELECT username, email FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$username = $admin['username'];

// Custom greeting message
$greeting = "How is your day going, $username?";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the password field is filled; if so, update the password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE admin_id = ?");
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE admin_id = ?");
        $stmt->bind_param("ssi", $username, $email, $admin_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Settings updated successfully.";
        header("Location: settings.php");
        exit();
    } else {
        $error_message = "Failed to update settings. Please try again.";
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
            .content {
                margin-top: 80px;
                background-color: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                max-width: 600px;
                width: 100%;
                font-size: 1.1rem;
                margin-left:150px;
            }

            h2 {
                margin-bottom: 20px;
                font-size: 1.6rem;
                color: #333;
                text-align: center;
            }

            form {
                display: flex;
                flex-direction: column;
            }

            label {
                margin-bottom: 5px;
                font-weight: bold;
                color: #333;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                padding: 10px;
                margin-bottom: 15px;
                border-radius: 5px;
                border: 1px solid #ccc;
                font-size: 1rem;
                width: 100%;
                box-sizing: border-box;
                transition: border-color 0.3s ease;
            }

            input[type="text"]:focus,
            input[type="email"]:focus,
            input[type="password"]:focus {
                border-color: #333;
            }

            button[type="submit"] {
                padding: 12px;
                background-color: #333;
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 1.1rem;
                cursor: pointer;
                transition: background-color 0.3s ease, transform 0.2s ease;
            }

            button[type="submit"]:hover {
                background-color: #575757;
                transform: scale(1.05);
            }

            button[type="submit"]:active {
                transform: scale(0.98);
            }

            .success-message,
            .error-message {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 5px;
                font-size: 1rem;
                text-align: center;
            }

            .success-message {
                background-color: #dff0d8;
                color: #3c763d;
                border: 1px solid #d6e9c6;
            }

            .error-message {
                background-color: #f2dede;
                color: #a94442;
                border: 1px solid #ebccd1;
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
            <h2>Settings</h2>
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php elseif (isset($_SESSION['message'])): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="settings.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>

                <label for="password">New Password (leave blank to keep current password):</label>
                <input type="password" id="password" name="password">

                <button type="submit">Update Settings</button>
            </form>
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
