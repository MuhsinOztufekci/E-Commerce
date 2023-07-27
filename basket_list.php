<!DOCTYPE html>
<html>

<head>
    <title>Sepetim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f2f2f2;
        }
        
        h1 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
        }

        .highlight {
            background-color: yellow;
        }
    </style>
</head>

<body>
    <h1>Sepetim</h1>
    <table>
        <thead>
            <tr>
                <th>Ürün Adı</th>
                <th>Marka</th>
                <th>Stok Miktarı</th>
                <th>Fiyat</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // $products = listProduct();
            // $cartItems = ['Product A', 'Product C']; // Örnek olarak sepetinizdeki ürünlerin adlarını içeren bir dizi

            foreach ($products as $product) {
                $highlightClass = in_array($product['product_name'], $cartItems) ? 'highlight' : '';
            ?>
                <tr class="<?php echo $highlightClass; ?>">
                    <td><?php echo $product['product_name'] ?></td>
                    <td><?php echo $product['brand_name'] ?></td>
                    <td><?php echo $product['quantity'] ?></td>
                    <td><?php echo $product['price'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>