<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%;
        }

        .result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Warehouse Registration</h1>
        <form method="POST" action="warehouse_sql.php">
            <input type="text" name="warehouse_name" placeholder="Warehouse Name" required>
            <input type="number" name="daily_order_limit" placeholder="Daily Order Limit" required>
            <input type="number" name="priority_value" placeholder="Priority Value" required>
            <button type="submit">Register Warehouse</button>
        </form>
        <!-- <div class="result">
            <?php
            if (isset($_POST['result'])) {
                $result = $_POST['result'];
                if ($_POST['status'] === 'success') {
                    echo '<p class="success">' . $result . '</p>';
                } else {
                    echo '<p class="error">' . $result . '</p>';
                }
            }
            ?>
        </div> -->
    </div>
</body>

</html>