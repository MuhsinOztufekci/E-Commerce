<?php require('product_sql.php'); ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="assets/css/product_list.css">
</head>

<body>
    <!-- Müşteri kimliğini aldığımız div elementi -->
    <div>
        <?php $customerID = $_SESSION['customerID']; ?>
        <div id="customer_id" data-id="<?php echo $customerID; ?>"></div>
    </div>

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
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Veritabanından ürünleri getir
                    $productl = new ProductSQL($conn);
                    $products = $productl->listProducts();

                    // Her ürün için tablo satırı oluştur
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

        <!-- Sepet -->
        <div class="cart">
            <!-- Sepeti Temizleme Butonu -->
            <div class="clear-cart-container">
                <h2 class="cart-heading">Sepetim</h2>
                <button class="clear-cart-button" id="clearAll">Sepeti Temizle</button>
            </div>

            <table>
                <thead>
                    <!-- Sepet tablosu başlık satırı -->
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Marka</th>
                        <th>Adet</th>
                        <th>Fiyat</th>
                        <th>Toplam Fiyat</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <!-- Sepet içeriği bu kısımda oluşturulacak -->
                </tbody>
            </table>

            <!-- Toplam Sepet Değeri ve Sepeti Onaylama Butonu -->
            <div class="total-cart-container">
                <h2 class="total-cart-value">Toplam Sepet Değeri: <span id="cartTotalValue">0</span></h2>
                <button class="accept-basket" onclick="confirmCart()">Sepeti Onayla</button>
            </div>
        </div>
    </div>

    <!-- Gerekli JavaScript kütüphaneleri -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- AJAX ile sepetteki ürünleri getir ve tabloyu oluştur -->
    <script>
        // Sayfa yüklendiğinde çalışacak AJAX işlemi
        $(document).ready(function() {
            // Müşteri kimliğini al
            var customerID = $('#customer_id').data('id');
            // AJAX ile sepetteki ürünleri getir
            $.ajax({
                url: "http://localhost/website/basket_sql.php",
                data: {
                    customerID: customerID
                },
                type: "POST",
                success: function(result) {
                    var products = JSON.parse(result);
                    var table = "";

                    // Sepet içeriğini tabloya ekle
                    for (var i = 0; i < products.length; i++) {
                        var product = products[i];
                        table += "<tr>";
                        table += "<td>" + product.product_name + "</td>";
                        table += "<td>" + product.brand_name + "</td>";
                        table += "<td>" + product.basket_quantity + "</td>";
                        table += "<td>" + product.price + "</td>";
                        table += "<td class='totalprice'>" + product.total_price + "</td>";
                        table += "<td>";
                        table += "<button onclick='changeQuantity(" + product.id + ", -1)'>-</button>";
                        table += "<button onclick='changeQuantity(" + product.id + ", 1)'>+</button>";
                        table += "</td>";
                        table += "</tr>";
                    }
                    $('#tbody').append(table);

                    // Toplam fiyat hesaplama
                    var total = 0;
                    var totalElements = document.getElementsByClassName('totalprice');
                    for (var i = 0; i < totalElements.length; i++) {
                        total += parseFloat(totalElements[i].textContent);
                    }

                    // Toplam fiyatı ekrana eklemek için güncelle
                    var insertCalculate = document.getElementById('cartTotalValue');
                    insertCalculate.innerHTML = total;
                },
            });
        });
    </script>

    <!-- AJAX ile sepetteki ürün adetini güncelle -->
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

                    // Güncellenen sepetteki ürünleri tabloya ekle
                    for (var i = 0; i < products.length; i++) {
                        table1 += "<tr>";
                        table1 += "<td>" + products[i].product_name + "</td>";
                        table1 += "<td>" + products[i].brand_name + "</td>";
                        table1 += "<td>" + products[i].basket_quantity + "</td>";
                        table1 += "<td>" + products[i].price + "</td>";
                        table1 += "<td class='totalprice'>" + products[i].total_price + "</td>";
                        table1 += "<td>";
                        table1 += "<button onclick='changeQuantity(" + products[i].id + ", -1)'>-</button>";
                        table1 += "<button onclick='changeQuantity(" + products[i].id + ", 1)'>+</button>";
                        table1 += "</td>";
                        table1 += "</tr>";
                    }
                    var tbodyElement = document.getElementById("tbody");
                    tbodyElement.innerHTML = table1;

                    // Toplam fiyat hesaplama
                    var total = 0;
                    var totalElements = document.getElementsByClassName('totalprice');
                    for (var i = 0; i < totalElements.length; i++) {
                        total += parseFloat(totalElements[i].innerHTML);
                    }

                    // Toplam fiyatı ekrana eklemek için güncelle
                    var insertCalculate = document.getElementById('cartTotalValue');
                    insertCalculate.innerHTML = total;
                }
            });
        }
    </script>

    <!-- Sepeti Temizle Butonu -->
    <script>
        $("#clearAll").click(function() {
            var customerID = $('#customer_id').data('id');
            var islem = "clearAll";
            var data = {
                customerID: customerID,
                islem: islem
            };

            // AJAX ile sepetteki ürünleri temizle
            $.ajax({
                url: "http://localhost/website/basket_sql.php",
                data: data,
                type: "POST",
                success: function(result) {
                    var tbodyElement = document.getElementById("tbody");
                    tbodyElement.innerHTML = "";
                    var insertCalculate = document.getElementById('cartTotalValue');
                    insertCalculate.innerHTML = "0";
                },
                error: function(xhr, status, error) {
                    // AJAX hatası durumunda hata mesajını konsola yazdır
                    console.error(error);
                }
            });
        });
    </script>
</body>

</html>