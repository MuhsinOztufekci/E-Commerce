<?php

class ProductList implements ProductListInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function wareHouseProductList($warehouse_id)
    {
        $sql = "SELECT wp.id, wp.stock, p.product_name, p.brand_name FROM warehouse_product AS wp, products AS p WHERE warehouse_id = :warehouse_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':warehouse_id', $warehouse_id);

        if ($stmt->execute()) {
            return $stmt->fetchAll(); // Assuming you want to fetch all the rows from the database.
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }
}
