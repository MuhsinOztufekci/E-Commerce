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
        $basketProducts = $this->getBasketProducts($orderId);
        $warehouseDetails = $this->getWarehouseDetails();
        $checkedProducts = [];

        foreach ($warehouseDetails as $warehouse) {
            $warehouseId = $warehouse['id'];

            foreach ($basketProducts as $product) {
                $productId = $product['product_id'];
                $productQuantity = $product['quantity'];
                $stock = $this->stockChecker($warehouseId, $productId);

                if ($stock > 0) {
                    $checkedProducts[$warehouseId][$productId] = true;
                } else   $checkedProducts[$warehouseId][$productId] = false;
            }
        }

        print_r($checkedProducts);
        $countTrue = $this->resultFilter($checkedProducts);
        print_r($countTrue);

        $result = $this->bestWarehouse($countTrue);
        // print_r($countTrue);
    }

    public function resultFilter($checkedProducts)
    {
        $countTrue = [];

        foreach ($checkedProducts as $warehouseId => $products) {
            $count = 0;

            foreach ($products as $productId => $value) {
                if ($value === true) {
                    $count++;
                }
            }

            $countTrue[$warehouseId] = $count;
        }

        return $countTrue;
    }

    public function bestWarehouse($countTrue)
    {
        $tempArray = [];
        foreach ($countTrue as $key => $val) {
            $tempArray[$key] = $val;
        }

        echo "Dizi:<br>";
        arsort($tempArray);
        print_r($tempArray);

        

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
        $sql = "UPDATE warehouse_products SET stock WHERE product_id = :product_id AND order_id = :order_id AND warehouse_id = :warehouse_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':warehouse_id', $warehouseId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':order_id', $orderId);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
