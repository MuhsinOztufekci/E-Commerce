<?php
require_once("../interfaces/product_add_interface.php");
require_once("../../db_connection.php");

class ProductAdd implements ProductAddInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function wareHouseProductAdd($productId, $addQuantity, $warehouseId)
    {
        try {
            $this->conn->beginTransaction();

            // Get the current stock
            $wareHouseCurrentStock = $this->getWareHouseCurrentStock($productId, $warehouseId);

            // Calculate the new stock quantity
            $wareHouseNewStock = $wareHouseCurrentStock + $addQuantity;

            //Toplam Stok
            $productCurrentStock = $this->getProductCurrentStock($productId);

            $productNewStock = $productCurrentStock + $addQuantity;

            // Update the stock quantity in the database
            $this->updateStockQuantity($productId, $warehouseId, $wareHouseNewStock, $productNewStock);

            $this->conn->commit();
            return $wareHouseNewStock; // Return the updated stock quantity
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("An error occurred: " . $e->getMessage());
        }
    }

    private function createStockRecord($productId, $warehouseId, $addQuantity)
    {
        $sqlInsert = "INSERT INTO warehouse_product (product_id, stock, warehouse_id) VALUES (:product_id, :stock, :warehouse_id)";
        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->bindParam(':product_id', $productId);
        $stmtInsert->bindParam(':warehouse_id', $warehouseId);
        $stmtInsert->bindValue(':stock', 0); // Initialize the stock to 0

        if (!$stmtInsert->execute()) {
            throw new Exception("Error creating the stock record.");
        }
    }

    private function getWareHouseCurrentStock($productId, $warehouseId)
    {
        $currentStock = 0;
        $sql = "SELECT stock FROM warehouse_product WHERE product_id = :product_id AND warehouse_id = :warehouse_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':warehouse_id', $warehouseId);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result !== false) {
                $currentStock = $result['stock'];
            } else {
                $this->createStockRecord($productId, $warehouseId, $currentStock);
            }
        } else {
            throw new Exception("Error executing the query to fetch the current stock quantity.");
        }

        return $currentStock;
    }

    private function getProductCurrentStock($productId)
    {
        $currentStock = 0;
        $sql = "SELECT quantity FROM products WHERE id = :product_id ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result !== false) {
                $currentStock = $result['quantity']; // Corrected the column name here
            }
        } else {
            throw new Exception("Error executing the query to fetch the Product current stock quantity.");
        }

        return $currentStock;
    }

    private function updateStockQuantity($productId, $warehouseId, $newStock, $productNewStock)
    {
        // Update the warehouse-specific stock quantity in the database
        $sqlUpdateWarehouse = "UPDATE warehouse_product SET stock = :new_stock WHERE product_id = :product_id AND warehouse_id = :warehouse_id";
        $stmtUpdateWarehouse = $this->conn->prepare($sqlUpdateWarehouse);
        $stmtUpdateWarehouse->bindParam(':new_stock', $newStock);
        $stmtUpdateWarehouse->bindParam(':product_id', $productId);
        $stmtUpdateWarehouse->bindParam(':warehouse_id', $warehouseId);

        if (!$stmtUpdateWarehouse->execute()) {
            throw new Exception("Error updating the warehouse stock quantity.");
        }

        // Update the total stock quantity in the `product` table by adding the new stock value to the existing quantity
        $sqlUpdateProduct = "UPDATE products SET quantity = :new_stock WHERE id = :product_id"; // Corrected the table name here
        $stmtUpdateProduct = $this->conn->prepare($sqlUpdateProduct);
        $stmtUpdateProduct->bindParam(':new_stock', $productNewStock);
        $stmtUpdateProduct->bindParam(':product_id', $productId);

        if (!$stmtUpdateProduct->execute()) {
            throw new Exception("Error updating the total stock quantity.");
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $addQuantity = isset($_POST['add_quantity']) ? (int)$_POST['add_quantity'] : 0;
    $warehouseId = isset($_POST['warehouse_id']) ? (int)$_POST['warehouse_id'] : 0;
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    try {
        $productAdd = new ProductAdd($conn);
        $result = $productAdd->wareHouseProductAdd($productId, $addQuantity, $warehouseId);
        echo $result; // Echo the updated stock quantity to JavaScript
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
