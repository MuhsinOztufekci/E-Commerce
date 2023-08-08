<?php
session_start();
require('db_connection.php');
require('warehouses/classes/warehouse_managment_sql.php');

interface PaymentGatewayInterface
{
    public function processPayment($customerID, &$errorMessage = "");
}

class PaymentManager
{
    private $conn;
    private $paymentGateway;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->paymentGateway = new YourPaymentGateway(); // Replace with the actual payment gateway implementation
    }

    public function processPayment($customerID)
    {
        // Basketten ürünleri aldık.
        $products = $this->getProductsFromBasket($customerID);

        if (empty($products)) {
            http_response_code(400); // Bad Request
            echo "Basket is empty. Payment cannot be processed.";
            exit;
        }

        $grandTotal = 0;
        foreach ($products as $product) {
            $grandTotal += $product["total_price"];
        }

        $orderID = $this->addOrder($customerID, $grandTotal);

        foreach ($products as $product) {
            $productID = $product["id"];
            $quantity = $product["basket_quantity"];
            $this->addOrderDetails($orderID, $productID, $quantity);
            $this->lowerStockAmount($productID, $quantity);
        }

        $cleared = $this->clearBasket($customerID);

        $warehouse = new WarehouseManager($this->conn);
        $result = $warehouse->handle($orderID);

        print_r($result) ;

        die();


        return $cleared && $this->paymentGateway->processPayment($customerID);
    }
    private function getProductsFromBasket($customerID)
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

    private function addOrder($customerID, $grandTotal)
    {
        $sql = "INSERT INTO `order` (customer_id, grand_total) VALUES (:customer_id, :grand_total)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':grand_total', $grandTotal);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    private function addOrderDetails($orderID, $productID, $quantity)
    {

        if (is_array($quantity)) {
            foreach ($quantity as $qty) {
                for ($i = 0; $i < $qty; $i++) {
                    $sql = "INSERT INTO order_details (order_id, product_id, quantity) VALUES (:order_id, :product_id, 1)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':order_id', $orderID);
                    $stmt->bindParam(':product_id', $productID);
                    $stmt->execute();
                }
            }
        } else {
            for ($i = 0; $i < $quantity; $i++) {
                $sql = "INSERT INTO order_details (order_id, product_id, quantity) VALUES (:order_id, :product_id, 1)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':order_id', $orderID);
                $stmt->bindParam(':product_id', $productID);
                $stmt->execute();
            }
        }
    }

    private function lowerStockAmount($productID, $quantity)
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

    private function clearBasket($customerID)
    {
        $sql = "DELETE FROM basket WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);

        return $stmt->execute();
    }
}

class YourPaymentGateway implements PaymentGatewayInterface
{
    public function processPayment($customerID, &$errorMessage = "")
    {
        // Your actual payment gateway integration code here
        // Return true for successful payment, false otherwise
        // Also, handle the error message if necessary
        // For example:
        // $errorMessage = "Payment failed: Invalid credit card number."

        return true;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerID = $_POST['customerID'];
    $paymentManager = new PaymentManager($conn);
    $paymentResult = $paymentManager->processPayment($customerID);

    if ($paymentResult) {
        echo "Payment successful! Basket cleared.";
    } else {
        http_response_code(500); // Internal Server Error
        echo "Payment successful, but basket clearance failed.";
    }
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
