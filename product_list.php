<?php require('product_sql.php'); ?>
<!DOCTYPE html>
<html>

<head>
    <style>
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

        .product-list,
        .cart {
            width: 48%;
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .product-list h2,
        .cart h2 {
            margin-top: 0;
        }

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

        .add-to-cart {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .cart-heading {
            margin-top: 30px;
        }

        .cart-empty td {
            text-align: center;
        }
    </style>
</head>

<body>
    <div> <!-- Getting customerID from Session -->
        <?php $customerID = $_SESSION['customerID']; ?>
        <div id="customer_id" data-id="<?php echo $customerID; ?>"></div>
    </div>
    <div class="container">
        <div class="product-list">
            <h2>Ürünler</h2>
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
                    $productl = new productSQL();
                    $products = $productl->listProduct();

                    foreach ($products as $product) :
                    ?>
                        <tr>
                            <td><?php echo $product['product_name'] ?></td>
                            <td><?php echo $product['brand_name'] ?></td>
                            <td><?php echo $product['quantity'] ?></td>
                            <td><?php echo $product['price'] ?></td>
                            <td><button class="btn add-to-cart" data-id="<?php echo  $product['id']; ?>">Sepete Ekle</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart">
            <h2 class="cart-heading">Sepetim</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Marka</th>
                        <th>Adet</th>
                        <th>Fiyat</th>
                        <th>Toplam Fiyat</th>
                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
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
                        table += "<td>" + product.total_price + "</td>";
                        table += "</tr>";
                    }
                    $('#tbody').append(table);
                },

            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.add-to-cart').on('click', function() {
                var productID = $(this).data('id');
                var customerID = $('#customer_id').data('id');
                var data = {
                    productID: productID,
                    customerID: customerID
                };
                $.ajax({
                    url: "http://localhost/website/basket_sql.php",
                    data: data,
                    type: "POST",
                    success: function(result2) {

                        var products = JSON.parse(result2);

                        // Solution of undefined mistake
                        var table1 = "";

                        for (var i = 0; i < products.length; i++) {
                            {
                                table1 += "<tr>";
                                table1 += "<td>" + products[i].product_name + "</td>";
                                table1 += "<td>" + products[i].brand_name + "</td>";
                                table1 += "<td>" + products[i].basket_quantity + "</td>";
                                table1 += "<td>" + products[i].price + "</td>";
                                table1 += "<td>" + products[i].total_price + "</td>";
                                table1 += "</tr>";
                            };

                        }

                        var tbodyElement = document.getElementById("tbody");

                        tbodyElement.innerHTML = "";

                        tbodyElement.innerHTML = table1;


                    }

                });
            });
        });
    </script>



</body>

</html>