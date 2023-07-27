<?php require('product_sql.php'); ?>
<!DOCTYPE html>
<html>

<head>

</head>

<body>
    <div class="container">
        <!-- Ürün Listesi -->
        <div class="product-list">
            <h2 class="cart-heading">Ürünler</h2>
            <table>
                <thead>
                    <!-- Tablo başlık satırı -->
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Markası</th>
                        <th>Stok Miktarı</th>
                        <th>Ücret</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($products as $product) :
                    ?>
                        <tr>
                            <td><?php echo $product['product_name'] ?></td>
                            <td><?php echo $product['brand_name'] ?></td>
                            <td><?php echo $product['quantity'] ?></td>
                            <td><?php echo $product['price'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

</body>

</html>