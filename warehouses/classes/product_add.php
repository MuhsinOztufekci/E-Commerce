<?php

class ProductAdd implements ProductAddInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function wareHouseProductAdd($product_id, $stock, $warehouse_id)
    {
        $sql = "INSERT INTO warehouse_product (product_id, stock, warehouse_id) VALUES (:product_id, :stock, :warehouse_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':warehouse_id', $warehouse_id);

        if ($stmt->execute()) {
            return "New product successfully added.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }
}
