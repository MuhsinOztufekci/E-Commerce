<?php
require("../../db_connection.php");
require('../classes/product_list_sql.php');
require('../classes/product_add_sql.php');
// Include the ProductAdd class file here

if (isset($_GET['warehouseId'])) {
    $warehouseId = $_GET['warehouseId'];
    try {
        $productList = new ProductList($conn);
        $products = $productList->getAllProducts($warehouseId);

        if (empty($products)) {
            echo "Bu Depoda Ürün Kalmamıştır.";
        }
    } catch (Exception $e) {
        // Handle the exception (display error message, log, etc.)
        echo "An error occurred: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <style>
         /* Reset default styles for margin and padding */
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        table,
        th,
        td {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-list {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cart-heading {
            color: #333;
            font-size: 24px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
        }

        thead {
            background-color: #f2f2f2;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }

        th {
            font-weight: bold;
            color: #333;
        }

        td {
            color: #444;
        }

        /* Input style */
        .form input[type="number"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 80px;
            margin-right: 5px;
        }

        /* Submit button style */
        .form button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form button:hover {
            background-color: #2980b9;
        }


        /* Make the table responsive for smaller screens */
        @media screen and (max-width: 600px) {
            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 8px 10px;
            }

            .cart-heading {
                font-size: 20px;
            }

            /* Form alignment */
            .form {
                display: flex;
                align-items: center;
            }

        }

        /* Button style */
        .form button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form button:hover {
            background-color: #2980b9;
        }

        /* No products message style */
        .no-products {
            color: #666;
            font-size: 18px;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>


<body>
    <div class="container">
        <!-- Ürün Listesi -->
        <div class="product-list">
            <!-- Your product list table HTML code remains the same.
                ... 
            -->
            <table>
                <thead>
                    <!-- Tablo başlık satırı -->
                    <tr>
                        <th>Ürün ID</th>
                        <th>Ürün Adı</th>
                        <th>Marka Adı</th>
                        <th>Toplam Stok Miktarı</th>
                        <th>Bu Depodaki Stok Miktarı</th>
                        <th>Stok Girişi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo $product['id'] ?></td>
                            <td><?php echo $product['product_name'] ?></td>
                            <td><?php echo $product['brand_name'] ?></td>
                            <td><?php echo $product['total_stock'] ?></td>
                            <td id="stock-<?php echo $product['id'] ?>"><?php echo $product['stock'] ?></td>
                            <td>
                                <form class="form" data-product-id="<?php echo $product['id'] ?>">
                                    <input type="number" name="add_quantity"></input>
                                    <input type="hidden" name="warehouse_id" value="<?php echo $warehouseId ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id'] ?>">
                                    <button type="submit">Stok Ekle</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(".form").submit(function(event) {
            event.preventDefault(); // Prevent form submission

            var form = $(this);
            var addQuantity = form.find('input[name="add_quantity"]').val();
            var warehouseId = form.find('input[name="warehouse_id"]').val();
            var productId = form.data('product-id');

            var data = {
                add_quantity: addQuantity,
                warehouse_id: warehouseId,
                product_id: productId
            };

            $.ajax({
                url: "../classes/product_add_sql.php", // Correct the AJAX URL if needed
                data: data,
                type: "POST",
                success: function(result) {
                    // Update the stock for the specific product in the table
                    $("#stock-" + productId).text(result);
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error: " + error);
                }
            });
        });
    });
</script>