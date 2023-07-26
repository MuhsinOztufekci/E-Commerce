<?php
// Include the database connection file
require_once("db_connection.php");

// Handle user input and perform actions accordingly
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Clear all items from the basket for a given customer
    if (isset($_POST['islem']) && $_POST['islem'] == 'clearAll') {
        // Create BasketSQL instance with the database connection
        $basketsql = new BasketSQL($conn);
        $customerID = $_POST['customerID'];
        // Clear the basket for the customer
        $basketsql->clearBasket($customerID);
        // Get updated product details after clearing the basket
        $data = $basketsql->getProductDetails($customerID);
        http_response_code(200); // Success
        // Return the updated product details in JSON format
        echo json_encode($data);
    }

    // Update the quantity of a product in the basket
    if (isset($_POST['customerID']) && isset($_POST['productID']) && isset($_POST['change'])) {
        $customerID = $_POST['customerID'];
        $productID = $_POST['productID'];
        $change = $_POST['change'];

        try {
            // Create BasketSQL instance with the database connection
            $basketsql = new BasketSQL($conn);
            // Update the basket for the customer with the new quantity
            $basketsql->updateBasket($customerID, $productID, $change);
            // Get updated product details after updating the basket
            $data = $basketsql->getProductDetails($customerID);
            http_response_code(200); // Success
            // Return the updated product details in JSON format
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500); // Internal server error
            echo "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['customerID'])) {
        try {
            $customerID = $_POST['customerID'];
            // Create BasketSQL instance with the database connection
            $basketsql = new BasketSQL($conn);
            // Get product details for the customer's basket
            $data = $basketsql->getProductDetails($customerID);
            http_response_code(200); // Success
            // Return the product details in JSON format
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500); // Internal server error
            echo "Error: " . $e->getMessage();
        }
    }
}

// Class representing the basket functionality and database operations
class BasketSQL
{
    private $conn;

    // Constructor to set the database connection
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Update the basket for a customer with a product and quantity change
    public function updateBasket($customerID, $productID, $change)
    {
        // Get product information
        $productPrice = $this->getProductPrice($productID);
        $productQuantity = $this->productStockChecker($productID);

        // Get basket item if it exists
        $basketItem = $this->getBasketItem($customerID, $productID);
        if (!$basketItem) {
            // Basket item does not exist, insert a new row for the product
            $this->insertBasket($customerID, $productID, $productPrice);
            return true;
        } else {
            $currentQuantity = $basketItem['basket_quantity']; // Fetch the current quantity from the basketItem

            // Calculate the new quantity based on the change
            $newQuantity = max($currentQuantity + $change, 1);
            // Ensure the new quantity does not exceed the product stock or maximum allowed quantity (20)
            if ($productQuantity <= 0 || $productQuantity < $newQuantity || $currentQuantity + $change > 20) {
                return false;
            }

            // Basket item exists, update quantity and total_price
            $this->updateExistingBasket($customerID, $productID, $newQuantity, $productPrice);
        }

        return true;
    }

    // Function to fetch a specific basket item for a customer and product
    private function getBasketItem($customerID, $productID)
    {
        $sql = "SELECT * FROM basket WHERE customer_id = :customer_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Function to check the stock quantity of a product
    private function productStockChecker($productID)
    {
        $sql = "SELECT quantity FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $productID);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['quantity'];
    }

    // Function to update the quantity and total price of an existing basket item
    private function updateExistingBasket($customerID, $productID, $newQuantity, $price)
    {
        $totalPrice = $newQuantity * $price;

        $sql = "UPDATE basket SET basket_quantity = :new_quantity, total_price = :total_price WHERE customer_id = :customer_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':new_quantity', $newQuantity);
        $stmt->bindParam(':total_price', $totalPrice);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->execute();
    }

    // Function to calculate the total price of all items in the basket for a customer
    public function totalPrice($customerID)
    {
        $totalPrice = 0;
        $sql = "SELECT total_price FROM basket WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $totalPrice += $result['total_price'];
        }

        return $totalPrice;
    }

    // Function to clear all items from the basket for a given customer
    public function clearBasket($customerID)
    {
        $sql = "DELETE FROM basket WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->execute();
    }

    // Function to get product details from the basket for a customer
    public function getProductDetails($customerID)
    {
        $sql = "SELECT p.id, p.product_name, p.brand_name, b.basket_quantity, p.price, b.total_price 
            FROM products AS p
            INNER JOIN basket AS b ON p.id = b.product_id
            WHERE b.customer_id = :customer_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Function to get the price of a product by its ID
    // Used in the upper functions for updating and inserting basket items
    private function getProductPrice($productID)
    {
        $sql = "SELECT price FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $productID);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new Exception("Product not found");
        }

        return $result['price'];
    }

    // Function to insert a product that does not exist in the basket
    private function insertBasket($customerID, $productID, $productPrice)
    {
        $sql = "INSERT INTO basket (product_id, customer_id, total_price, basket_quantity) VALUES (:product_id, :customer_id, :total_price, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':total_price', $productPrice);
        $stmt->execute();
    }
}
