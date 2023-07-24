<?php require('product_sql.php'); ?>
<!DOCTYPE html>
<html>

<head>
    <style>
        /* styles.css */
        /* styles.css */

        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
        }

        .container {
            display: flex;
            justify-content: space-between;
        }

        /* Product List and Cart Containers */
        .product-list,
        .cart {
            width: 48%;
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .product-list h2,
        .cart h2 {
            margin: 0 0 15px;
            font-size: 20px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        /* Add To Cart Button */
        .add-to-cart {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .add-to-cart:hover {
            background-color: #45a049;
        }

        /* Cart Buttons */
        .cart button {
            margin-right: 5px;
            padding: 5px 8px;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
        }

        .cart button.minus-btn {
            background-color: #f44336;
        }

        .cart button.plus-btn {
            background-color: #2196F3;
        }

        /* Clear Cart Button */
        .clear-cart-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .clear-cart-button {
            background-color: #f44336;
            padding: 8px 15px;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        .clear-cart-button:hover {
            background-color: #d32f2f;
        }

        /* Cart Heading */
        .cart-heading {
            margin: 0;
        }

        /* Center align empty cart message */
        .cart-empty td {
            text-align: center;
        }

        /* Align clear cart button to the right */
        .clear-cart {
            text-align: right;
            margin-bottom: 15px;
        }

        .total-cart-value {
            text-align: center;
            margin-top: 20px;
        }

        .total-cart-value h2 {
            margin: 0;
            font-size: 18px;
        }

        .total-cart-value #cartTotalValue {
            font-weight: bold;
            color: #2196F3;
            /* You can choose any color you prefer */
        }

        /* Add more styles and media queries if needed */
    </style>
</head>

<body>
    <div> <!-- Getting customerID from Session -->
        <?php $customerID = $_SESSION['customerID']; ?>
        <div id="customer_id" data-id="<?php echo $customerID; ?>"></div>
    </div>
    <div class="container">
        <div class="product-list">
            <h2 class="cart-heading">Ürünler</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Markası</th>
                        <th>Stok Miktarı</th>
                        <th>Ücret</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Getting products from db  
                    $productl = new ProductSQL($conn);
                    $products = $productl->listProducts();

                    foreach ($products as $product) :
                    ?>
                        <tr>
                            <td><?php echo $product['product_name'] ?></td>
                            <td><?php echo $product['brand_name'] ?></td>
                            <td><?php echo $product['quantity'] ?></td>
                            <td><?php echo $product['price'] ?></td>
                            <td>
                                <button onclick="changeQuantity(<?php echo $product['id']; ?>, 1)" data-id="<?php echo $product['id']; ?>">Sepete Ekle</button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart">
            <div class="clear-cart-container">
                <h2 class="cart-heading">Sepetim</h2>
                <button class="clear-cart-button" onclick="clearAll()" id="clearAll">Clear Cart</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Marka</th>
                        <th>Adet</th>
                        <th>Fiyat</th>
                        <th>Toplam Fiyat</th>
                        <th>İşlemler</th>

                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>

            <div class="total-cart-value">
                <h2>Toplam Sepet Değeri: <span id="cartTotalValue">0</span></h2>
            </div>

        </div>

        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

        <script>
            // Gets basket products while system loading 
            $(document).ready(function() {

                var customerID = $('#customer_id').data('id');
                $.ajax({
                    url: "http://localhost/website/basket_sql.php",
                    data: {
                        customerID: customerID
                    }, // Sending the customerID as an object
                    type: "POST",
                    success: function(result) {
                        var products = JSON.parse(result);
                        var table;

                        for (var i = 0; i < products.length; i++) {
                            var product = products[i];
                            table += "<tr>";
                            table += "<td>" + product.product_name + "</td>";
                            table += "<td>" + product.brand_name + "</td>";
                            table += "<td>" + product.basket_quantity + "</td>";
                            table += "<td>" + product.price + "</td>";
                            table += "<td>" + products[i].total_price + "</td>";
                            table += "<td>" + "<button onclick='changeQuantity(" + products[i].id + ", -1)'>-</button>";
                            table += "<button onclick='changeQuantity(" + products[i].id + ", 1)'>+</button>" + "</td>";
                            table += "</tr>";
                        }
                        $('#tbody').append(table);
                    },

                });
            });
        </script>


        <script>
            function changeQuantity(productID, change) {
                var customerID = $('#customer_id').data('id');
                var data = {
                    productID: productID,
                    customerID: customerID,
                    change: change
                };
                $.ajax({
                    url: "http://localhost/website/basket_sql.php",
                    data: data,
                    type: "POST",
                    success: function(result) {
                        var products = JSON.parse(result);

                        var table1 = "";

                        for (var i = 0; i < products.length; i++) {
                            table1 += "<tr>";
                            table1 += "<td>" + products[i].product_name + "</td>";
                            table1 += "<td>" + products[i].brand_name + "</td>";
                            table1 += "<td>" + products[i].basket_quantity + "</td>";
                            table1 += "<td>" + products[i].price + "</td>";
                            table1 += "<td>" + products[i].total_price + "</td>";
                            table1 += "<td>" + "<button onclick='changeQuantity(" + products[i].id + ", -1)'>-</button>";
                            table1 += "<button onclick='changeQuantity(" + products[i].id + ", 1)'>+</button>" + "</td>";
                            table1 += "</tr>";
                        }

                        var tbodyElement = document.getElementById("tbody");

                        tbodyElement.innerHTML = table1;
                    }
                });
            }
        </script>

        <!-- Belongs to Clear Basket button   -->
        <script>
            function clearAll() {
                $("#clearAll").click(function() {
                    var customerID = $('#customer_id').data('id');
                    var islem = "clearAll";
                    var data = {
                        customerID: customerID,
                        islem: islem
                    };

                    $.ajax({
                        url: "http://localhost/website/basket_sql.php",
                        data: data,
                        type: "POST",
                        success: function(result) {
                            var tbodyElement = document.getElementById("tbody");
                            tbodyElement.innerHTML = "";
                        },
                        error: function(xhr, status, error) {
                            // Handle error here if the AJAX request fails
                            console.error(error);
                        }
                    });
                });
            }
        </script>







</body>

</html>