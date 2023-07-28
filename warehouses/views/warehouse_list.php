<?php
include("warehouse_sql.php");

$warehouseModel = new WareHouseModel($conn);
$warehouses = $warehouseModel->listwarehouse();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depolar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f7f7f7;
        }

        h1 {
            text-align: center;
        }

        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>Depolar</h1>

    <p><a href="http://localhost/website/warehouses/views/warehouse_add.php"><b>Yeni Depo Oluştur</b></a></p>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Daily Order Quantity</th>
                    <th>Priority Value</th>
                    <th>Detay</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($warehouses as $warehouseItem) { ?>
                    <tr>
                        <td><?php echo $warehouseItem['warehouse_name']; ?></td>
                        <td><?php echo $warehouseItem['daily_order_limit']; ?></td>
                        <td><?php echo $warehouseItem['priority_value']; ?></td>
                        <td><button onclick="redirect(<?php echo $warehouseItem['id']; ?>)">Stok Listeleme</button></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- AJAX ile sepetteki ürün adetini güncelle -->
    <script>
        function redirect(warehouseId) {
            // Construct the URL with the warehouseId parameter
            var url = "http://localhost/website/warehouses/views/product_list.php?warehouseId=" + warehouseId;

            // Redirect to the new URL
            window.location.href = url;
        }
    </script>
</body>

</html>