<?php
require('db_connection.php');


// // Ekleme komutu

// if (isset($_POST['hidden']) == 'add') {
//     $sku = $_POST['sku'];
//     $product_name = $_POST['product_name'];
//     $brand_name = $_POST['brand_name'];
//     $quantity = $_POST['quantity'];
//     $price = $_POST['price'];

//     addProduct($sku, $product_name, $brand_name, $quantity, $price);
// }
// if (isset($_POST['hidden']) === 'update') {
//     $product_name = $_POST['product_name'];
//     $brand_name = $_POST['brand_name'];
//     $quantity = $_POST['quantity'];
//     $price = $_POST['price'];

//     updateProduct($product_name, $brand_name, $quantity, $price);
// }
// if (isset($_POST['hidden']) === 'delete') {
//     $product_name = $_POST['product_name'];
//     $brand_name = $_POST['brand_name'];
//     $quantity = $_POST['quantity'];
//     $price = $_POST['price'];

//     deleteProduct($product_name, $brand_name, $quantity, $price);
// }

class productSQL
{
    function addProduct($sku, $product_name, $brand_name, $quantity, $price)
    {
        global $conn;
        $sql = "INSERT INTO products (sku, product_name, brand_name, quantity, price) VALUES (:sku ,:product_name, :brand_name, :quantity, :price)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':brand_name', $brand_name);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);

        if ($stmt->execute()) {
            echo "New product successfully added.";

            header("Location: http://localhost/website/product_list.php");
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Ürünü alma komutu

    function getProduct($product_id)
    {
        global $conn;

        $sql = "SELECT * FROM products WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo "Product ID: " . $result['product_id'] . "<br>";
            echo "Product Name: " . $result['product_name'] . "<br>";
            echo "Brand Name: " . $result['brand_name'] . "<br>";
            echo "Quantity: " . $result['quantity'] . "<br>";
            echo "Price: " . $result['price'] . "<br>";
        } else {
            echo "No product found.";
        }
    }

    function listProduct()
    {
        global $conn;

        $sql = "SELECT * FROM products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    // Güncelleme komutu

    function updateProduct()
    {
        global $conn;

        $sql = "UPDATE products SET product_name = :product_name, brand_name = :brand_name, quantity = :quantity, price = :price WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':brand_name', $brand_name);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);

        if ($stmt->execute()) {
            echo "Product updated successfully.";
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Silme komutu

    function deleteProduct($product_id)
    {
        global $conn;

        $sql = "DELETE FROM products WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);

        if ($stmt->execute()) {
            echo "Product deleted successfully.";
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }
}
