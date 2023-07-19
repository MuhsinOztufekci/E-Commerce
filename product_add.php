<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input[type="hidden"] {
            display: none;
        }

        button[type="submit"] {
            display: block;
            width: 100%;
            background-color: #4CAF50;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <h1>Add Product</h1>
    <form action="product_sql.php" method="post">

        <label for="sku">Stock Kepping Unit (SKU):</label>
        <input type="text" id="sku" name="sku" placeholder="Please enter SKU" required>

        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" placeholder="Please enter product name" required>

        <label for="brand_name">Brand Name:</label>
        <input type="text" id="brand_name" name="brand_name" placeholder="Please enter brand name" required>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" placeholder="Please enter quantity" required>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" placeholder="Please enter price" required>

        <input type="hidden" name="hidden" value="add">
        <button type="submit">Add</button>
    </form>
</body>

</html>