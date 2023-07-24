<?php
require('db_connection.php');

// Sanitize and validate user input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productSQL = new ProductSQL($conn);

    if (isset($_POST['hidden'])) {
        if ($_POST['hidden'] === 'add') {
            $sku = $_POST['sku'];
            $product_name = $_POST['product_name'];
            $brand_name = $_POST['brand_name'];
            $quantity = $_POST['quantity'];
            $price = $_POST['price'];

            $result = $productSQL->addProduct($sku, $product_name, $brand_name, $quantity, $price);
            // Handle the result accordingly (e.g., display success or error message).
        } elseif ($_POST['hidden'] === 'update') {
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $brand_name = $_POST['brand_name'];
            $quantity = $_POST['quantity'];
            $price = $_POST['price'];

            $result = $productSQL->updateProduct($product_id, $product_name, $brand_name, $quantity, $price);
            // Handle the result accordingly (e.g., display success or error message).
        } elseif ($_POST['hidden'] === 'delete') {
            $product_id = $_POST['product_id'];

            $result = $productSQL->deleteProduct($product_id);
            // Handle the result accordingly (e.g., display success or error message).
        }
    }
}

class ProductSQL
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addProduct($sku, $product_name, $brand_name, $quantity, $price)
    {
        $sql = "INSERT INTO products (sku, product_name, brand_name, quantity, price) VALUES (:sku, :product_name, :brand_name, :quantity, :price)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':brand_name', $brand_name);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);

        if ($stmt->execute()) {
            return "New product successfully added.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    public function getProduct($product_id)
    {
        $sql = "SELECT * FROM products WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result;
        } else {
            return "No product found.";
        }
    }

    public function listProducts()
    {
        $sql = "SELECT * FROM products";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function updateProduct($product_id, $product_name, $brand_name, $quantity, $price)
    {
        $sql = "UPDATE products SET product_name = :product_name, brand_name = :brand_name, quantity = :quantity, price = :price WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':brand_name', $brand_name);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':product_id', $product_id);

        if ($stmt->execute()) {
            return "Product updated successfully.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    public function deleteProduct($product_id)
    {
        $sql = "DELETE FROM products WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);

        if ($stmt->execute()) {
            return "Product deleted successfully.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }
}
