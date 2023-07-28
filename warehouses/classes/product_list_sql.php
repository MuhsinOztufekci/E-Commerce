<?php

require("../interfaces/product_list_interface.php");
require("../../db_connection.php");

class ProductList implements ProductListInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    
    public function getAllProducts($warehouseId)
    {
        $sql = "SELECT p.id, p.product_name, p.brand_name, p.quantity AS total_stock,
        IFNULL(wh.stock, 0) AS stock
        FROM products AS p
        LEFT JOIN warehouse_product AS wh ON p.id = wh.product_id AND wh.warehouse_id = :warehouse_id
        GROUP BY p.id, p.product_name, p.brand_name";

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->bindParam(':warehouse_id', $warehouseId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle the database query error here
            throw new Exception("Error retrieving products.");
        }
    }
}
