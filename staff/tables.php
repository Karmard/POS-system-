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

        $query = "SELECT * FROM tables";
        $result = $conn->query($query);
        $tables = [];
        if ($result->num_rows > 0) 
        {
            while ($row = $result->fetch_assoc()) 
            {
                $tables[] = $row;
            }
        } 
        
        else 
        {
            $tables[] = ['table_id' => 0, 'table_number' => 'No Tables Found']; // For debugging
        }

        $stmt->close();
        $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Tables</title>
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

        h1 
        {
            margin: 20px 0;
            color: #00796b;
            font-size: 2.5rem;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        em
        {
            color: orange;
        }

        .tables-container 
        {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .table 
        {
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .table:hover 
        {
            transform: scale(1.05);
        }

        .table img 
        {
            width: 80%;
            height: auto;
            display: block;
        }

        .table-number 
        {
            position: absolute;
            font-size: 1.5rem;
            color: #00796b;
            font-weight: bold;
        }

        .large-table 
        {
            width: 160px;
            height: 160px;
        }

        .corner-table 
        {
            width: 140px;
            height: 140px;
            border: 2px solid #00796b;
        }

        .middle-table 
        {
            width: 130px;
            height: 130px;
            background-color: #e0f2f1;
        }

        @media (max-width: 768px)
         {
            .table 
            {
                width: 100px;
                height: 100px;
            }

            .large-table 
            {
                width: 140px;
                height: 140px;
            }

            .corner-table 
            {
                width: 120px;
                height: 120px;
            }

            .middle-table 
            {
                width: 110px;
                height: 110px;
            }
        }

        .highlight 
        {
            color: rgb(81, 215, 249);
            font-weight: bold; 
        }

    </style>
</head>
<body>
    <h1>Hello <span class="highlight"><?php echo $username; ?></span>, select <em>table</em> being served</h1>
    <div class="tables-container">
        <?php if (!empty($tables)): ?>
            <?php foreach ($tables as $index => $table): ?>
                <?php
                $tableClass = '';
                if ($index % 8 == 0) 
                {
                    $tableClass = 'large-table';
                } 
                
                elseif ($index % 5 == 0) 
                {
                    $tableClass = 'corner-table';
                } 
                
                elseif ($index % 3 == 0) 
                {
                    $tableClass = 'middle-table';
                }
                ?>
                <a href="serve.php?table_id=<?php echo urlencode($table['table_id']); ?>" class="table <?php echo $tableClass; ?>">
                    <img src="../images/table.png" alt="Table">
                    <div class="table-number"><?php echo htmlspecialchars($table['table_number']); ?></div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tables available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
