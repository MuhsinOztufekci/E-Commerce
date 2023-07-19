<?php
// Sanitize and validate user input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['customerID'], $_POST['productID'])) {
        $customerID = $_POST['customerID'];
        $productID = $_POST['productID'];

        try {
            require_once("db_connection.php");
            $basketsql = new BasketSQL($conn);
            $basketsql->updateBasket($customerID, $productID);
        } catch (PDOException $e) {
            // Handle the database connection or query error here.
            // You may log the error or display a user-friendly message.
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

    public function updateBasket($customerID, $productID)
    {
        $price = $this->getProductPrice($productID);

        $sql = "UPDATE basket SET basket_quantity = basket_quantity + 1, total_price = (basket_quantity + 1) * :price WHERE customer_id = :customer_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':price', $price);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            // No row was updated, meaning the record doesn't exist, so we insert it.
            $this->insertBasket($customerID, $productID, $price);
        }
    }

    private function getProductPrice($productID)
    {
        $sql = "SELECT price FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $productID);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            // Handle the case when the product does not exist.
            // You may throw an exception or provide a default value.
            return 0;
        }

        return $result['price'];
    }

    private function insertBasket($customerID, $productID, $price)
    {
        $sql = "INSERT INTO basket (product_id, customer_id, total_price, basket_quantity) VALUES (:product_id, :customer_id, :total_price, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->bindParam(':product_id', $productID);
        $stmt->bindParam(':total_price', $price);
        $stmt->execute();
    }

    function productDetails($customerID)
    {
        $sql = "SELECT p.product_name, p.brand_name, b.basket_quantity, p.price, b.total_price 
            FROM products AS p
            INNER JOIN basket AS b ON p.id = b.product_id
            WHERE b.customer_id = :customer_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customerID);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
