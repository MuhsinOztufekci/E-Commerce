<?php
require_once("db_connection.php");

// Sanitize and validate user input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['islem']) && $_POST['islem'] == 'clearAll') {
        $basketsql = new BasketSQL($conn);
        $customerID = $_POST['customerID'];
        $basketsql->clearBasket($customerID);
        $data = $basketsql->getProductDetails($customerID);
        http_response_code(200); // Success
        echo json_encode($data);
    }


    if (isset($_POST['customerID']) && isset($_POST['productID']) && isset($_POST['change'])) {
        $customerID = $_POST['customerID'];
        $productID = $_POST['productID'];
        $change = $_POST['change'];

        try {
            $basketsql = new BasketSQL($conn);
            $basketsql->updateBasket($customerID, $productID, $change);
            $data = $basketsql->getProductDetails($customerID);
            http_response_code(200); // Success
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500); // Internal server error
            echo "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['customerID'])) {
        try {
            $customerID = $_POST['customerID'];
            $basketsql = new BasketSQL($conn);
            $data = $basketsql->getProductDetails($customerID);
            http_response_code(200); // Success
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500); // Internal server error
            echo "Error: " . $e->getMessage();
        }
    }
}

class BasketSQL
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function updateBasket($customerID, $productID, $change)
    {
        $price = $this->getProductPrice($productID);

        $sql = "SELECT * FROM basket WHERE customer_id = :customer_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // No row exists, insert new row
            $this->insertBasket($customerID, $productID, $price);
        } else {
            // Row exists, update quantity and total_price
            if ($change == 1) {
                $this->updateExistingBasketUpper($customerID, $productID, $price);
            } elseif ($change == -1) {
                $this->updateExistingBasketLower($customerID, $productID, $price);
            }
        }
    }

    // Gets upper of given product basketQuantity
    private function updateExistingBasketUpper($customerID, $productID, $price)
    {
        $sql = "UPDATE basket SET basket_quantity = basket_quantity + 1, total_price = (basket_quantity) * :price  WHERE customer_id = :customer_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':price', $price);
        $stmt->execute();
    }

    private function beforeUpdateStockCheckker($productID)
    {
        $sql = "SELECT quantity FROM products WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productID);
        $stmt->execute();
    }
    // Gets lower of given product basketQuantity
    private function updateExistingBasketLower($customerID, $productID, $price)
    {
        $sql = "UPDATE basket SET basket_quantity = basket_quantity - 1, total_price = (basket_quantity) * :price  WHERE customer_id = :customer_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':price', $price);
        $stmt->execute();
    }

    public function totalPrice($customerID)
    {
        $totalPrice = 0;
        $sql = "SELECT total_price FROM basket WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $result =  $stmt->execute();

        $result['price'] += $totalPrice;

        return $totalPrice;
    }
    public function clearBasket($customerID)
    {
        $sql = "DELETE FROM basket WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->execute();
    }

    // Gets products price
    // Using in the upper functions
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
    // İnsert a product that does not exsist in basket
    private function insertBasket($customerID, $productID, $price)
    {
        $sql = "INSERT INTO basket (product_id, customer_id, total_price, basket_quantity) VALUES (:product_id, :customer_id, :total_price, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':total_price', $price);
        $stmt->execute();
    }

    // Gets product details to return table 
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
}
