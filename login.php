<?php
session_start();
include './database/connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Check in the admins table for plaintext passwords
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // Check if the password matches the hashed format
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['role'] = $admin['role'];
            header("Location: loading.php?redirect=admin/admin.php");
            exit();
        } else {
            $error = "Invalid password for admin.";
        }
    } else {
        // Check in the users table for plaintext passwords
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Check if the password matches the hashed format
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['position'] = $user['position'];
                
                if ($user['position'] === 'cook') {
                    header("Location: loading.php?redirect=staff/prepare.php");
                } elseif ($user['position'] === 'waiter/waitress') {
                    header("Location: loading.php?redirect=staff/tables.php");
                } else {
                    $error = "Invalid position for user.";
                }
                exit();
            } else {
                $error = "Invalid password for user.";
            }
        } else {
            $error = "User not found.";
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e0f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        h2 {
            color: #00796b;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container {
            background: #ffffff;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 2px;
            font-weight: 600;
            color: #00796b;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #00796b;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #004d40;
            outline: none;
        }
        input[type="submit"] {
            background-color: #00796b;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        input[type="submit"]:hover {
            background-color: #004d40;
            transform: scale(1.05);
        }
        input[type="submit"]:active {
            background-color: #003d34;
        }
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
        @media (max-width: 600px) {
            .form-container {
                width: 90%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login to Continue</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>
            
            <input type="submit" value="Login">
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
