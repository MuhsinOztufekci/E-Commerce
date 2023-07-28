<?php
session_start();
require('db_connection.php');

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerID = $_POST['customerID']; // We get customerID from session
    $grandTotal = 0; // Calculating grand total

    // Create an instance of the FinishedPayment class
    $finishedPayment = new FinishedPayment($conn);

    // Get products from the basket for the customer
    $products = $finishedPayment->getProductsFromBasket($customerID);

    // Check if there are products in the basket
    if (empty($products)) {
        http_response_code(400); // Bad Request
        echo "Basket is empty. Payment cannot be processed.";
        exit;
    }

    // Iterate through the products and calculate the grand total
    foreach ($products as $product) {
        $grandTotal += $product["total_price"];
    }

    // Add order to the order table
    $finishedPayment->addOrderTable($customerID, $grandTotal);

    // Get the order ID for the newly inserted order
    $orderID = $conn->lastInsertId();

    // Add order details for each product to the order_details table
    foreach ($products as $product) {
        $productID = $product["id"];
        $quantity = $product["basket_quantity"];
        $finishedPayment->addOrderDetailsTable($orderID, $productID, $quantity);

        $finishedPayment->lowerStockAmount($productID, $quantity);
    }


    // Clear the user's basket after completing the payment process
    $cleared = $finishedPayment->clearBasket($customerID);

    if ($cleared) {
        // Send a response back to the JavaScript function
        echo "Payment successful! Basket cleared.";
    } else {
        // Return an error response if the basket clearance failed
        http_response_code(500); // Internal Server Error
        echo "Payment successful, but basket clearance failed.";
    }
} else {
    // Return an error response if the request method is not POST
    http_response_code(405);
    echo "Invalid request method.";
}

class FinishedPayment
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getProductsFromBasket($customerID)
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

    public function addOrderTable($customerID, $grandTotal)
    {
        $sql = "INSERT INTO `order` (customer_id, grand_total) VALUES (:customer_id, :grand_total)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':grand_total', $grandTotal);
        $stmt->execute();
    }

    public function addOrderDetailsTable($orderID, $productID, $quantity)
    {
        $sql = "INSERT INTO order_details (order_id, product_id, quantity) VALUES (:order_id, :product_id, :quantity)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();
    }

    public function clearBasket($customerID)
    {
        $sql = "DELETE FROM basket WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);

        return $stmt->execute();
    }

    public function lowerStockAmount($productID, $quantity)
    {
        $sql = "UPDATE products SET quantity = (quantity - :quantity) WHERE id = :product_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $productID);
            $stmt->bindParam(':quantity', $quantity);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Handle the error here or log it
            return false;
        }
    }
}
