<?php
require('db_connection.php');

class WarehouseManager
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function handle($orderId)
    {
        // Sepetteki ürünleri ve depo bilgilerini veri tabanından çeker.
        $basketProducts = $this->getBasketProducts($orderId);
        $warehouseDetails = $this->getWarehouseDetails();

        //Ürünler ve depoları kontrol için döner sonuçlarını associative array olarak döner.
        $checkedProducts = $this->firstControl($basketProducts, $warehouseDetails);
        // print_r($checkedProducts);

        // Hangi depoda daha fazla ürün bulunuyor sayıları toplayarak geri döner.
        $countTrue = $this->arrayFilter($checkedProducts);
        // print_r($countTrue);

        // Öncelik değerine göre ve  sıralanmış depoları geri döner.
        $result = $this->sortedArray($countTrue);

        // sortedArray fonkisonundan geri dönen arrayin en üstteki değerini alır.
        $chosenWareHouse = key($result);
        // echo "Key";
        // echo "</br>";
        // print_r($chosenWareHouse);

        // En yüksek depodan update atıldı atılamayan ürünler arrayde tutuldu.
        $remainBasketProducts = $this->actionControl($basketProducts, $chosenWareHouse, $orderId);
        // echo "burası remain basket";

        if (empty($remainBasketProducts)) {
            echo "Bütün ürünler başarıyla dağıtıldı.";
            exit;
        }
        // Buraya kadar tamam.
        // Tekrar hangi depolarda hangi ürünler var fonksiyonuna attık.
        $secondChecked = $this->firstControl($remainBasketProducts, $warehouseDetails);

        $secondCountTrue = $this->arrayFilter($secondChecked);

        $result = $this->sortedArray($secondCountTrue);

        $secondChosenWareHouse = key($result);

        // 2.defa update fonksiyonuna attım.
        $remainBasketProducts2 = $this->actionControl($remainBasketProducts, $secondChosenWareHouse, $orderId);

        if (empty($remainBasketProducts2)) {
            echo "Bütün ürünler başarıyla dağıtıldı.";
            exit;
        } else  print_r($remainBasketProducts2);
    }

    public function actionControl($basketProducts, $chosenWareHouse, $orderId)
    {

        $remainBasketProducts = array();
        print_r($basketProducts);

        foreach ($basketProducts as $key => $product) {

            $productId = $product['product_id'];
            $productQuantity = $product['quantity'];
            $warehouseId = $chosenWareHouse;
            $stock = $this->stockChecker($warehouseId, $productId);

            if ($stock >= 1) {
                // echo "<br>";
                // echo "if";
                // echo "$key";
                // echo "</br>";
                $this->updateWarehouseInfo($warehouseId, $productId, $orderId);
                $this->stockDelete($warehouseId, $productId, $orderId);
            } else {
                // echo "</br>";
                // echo "else";
                // echo "$productId";
                // echo "</br>";
                $remainBasketProducts[$key]['product_id'] = $productId;
                $remainBasketProducts[$key]['quantity'] = $productQuantity;
                print_r($remainBasketProducts);
            }
        }
        return $remainBasketProducts;
    }

    public function firstControl($basketProducts, $warehouseDetails)
    {
        $checkedProducts = array();

        foreach ($warehouseDetails as $warehouse) {
            $warehouseId = $warehouse['id'];

            foreach ($basketProducts as $key => $product) {
                $productId = $product['product_id'];
                $productQuantity = $product['quantity'];
                $stock = $this->stockChecker($warehouseId, $productId);

                if ($stock >= 1) {
                    $checkedProducts[$warehouseId][$key][$productId] = true;
                } else {
                    $checkedProducts[$warehouseId][$key][$productId] = false;
                }
            }
        }

        return $checkedProducts;
    }

    public function arrayFilter($checkedProducts)
    {
        $countTrue = [];

        foreach ($checkedProducts as $warehouseId => $key) {
            $count = 0;

            foreach ($key as $key1 => $product) {

                foreach ($product as $productId => $value) {
                    if ($value === true) {
                        $count++;
                    }
                }
            }

            $countTrue[$warehouseId] = $count;
        }

        return $countTrue;
    }

    public function sortedArray($countTrue)
    {
        $tempArray = [];
        foreach ($countTrue as $key => $val) {
            $tempArray[$key] = $val;
        }

        echo "Dizi:<br>";
        arsort($tempArray);
        print_r($tempArray);


        return $tempArray;
    }

    public function stockChecker($warehouseId, $productId)
    {
        $sql = "SELECT stock FROM warehouse_product WHERE warehouse_id = :warehouse_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':warehouse_id', $warehouseId);
        $stmt->bindValue(':product_id', $productId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // if (!$result) {
        //     throw new Exception("Error fetching stock: " . implode(' ', $stmt->errorInfo()));
        // }

        return $result;
    }

    public function getBasketProducts($orderId)
    {
        $sql = "SELECT product_id, quantity FROM order_details WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return "Error: " . $stmt->errorInfo()[2];
        }

        return $result;
    }

    public function getWarehouseDetails()
    {
        $sql = "SELECT priority_value, id, warehouse_name, daily_order_limit FROM warehouse ORDER BY priority_value";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return "Error: " . $stmt->errorInfo()[2];
        }

        return $result;
    }

    public function updateWarehouseInfo($warehouseInfo, $productId, $orderId)
    {
        $sql = "UPDATE order_details SET warehouse_info = :warehouse_info WHERE product_id = :product_id AND order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':warehouse_info', $warehouseInfo);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':order_id', $orderId);

        if (!$stmt->execute()) {
            throw new Exception("Error updating warehouse info: " . implode(' ', $stmt->errorInfo()));
        }
    }

    public function stockDelete($warehouseId, $productId, $orderId)
    {
        $sql = "UPDATE warehouse_product SET stock = stock-1 WHERE product_id = :product_id AND warehouse_id = :warehouse_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':warehouse_id', $warehouseId);
        $stmt->bindParam(':product_id', $productId);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
