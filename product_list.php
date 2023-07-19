<?php
require('basket_sql.php');
require('product_sql.php');
?>

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
                <tbody>
                    <?php $customerID = $_SESSION['customerID']; ?>
                    <div id="customer_id" data-id="<?php echo $customerID; ?>"></div>
                    <?php
                    $basket = new BasketSQL($conn);
                    $basketProducts = $basket->productDetails($customerID);
                    foreach ($basketProducts as $basketProd) : ?>
                        <tr>
                            <td><?php echo $basketProd['product_name'] ?></td>
                            <td><?php echo $basketProd['brand_name'] ?></td>
                            <td><?php echo $basketProd['basket_quantity'] ?></td>
                            <td><?php echo $basketProd['price'] ?></td>
                            <td><?php echo $basketProd['total_price'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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

                });
            });
        });
    </script>

</body>

</html>