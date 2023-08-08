<?php
// Include the file that contains the database connection ($conn variable)
require('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the POST request
    $warehouse_name = $_POST['warehouse_name'];
    $daily_order_limit = $_POST['daily_order_limit'];
    $priority_value = $_POST['priority_value'];

    // Create WarehouseModel instance and register the warehouse
    $warehouseModel = new WarehouseModel($conn);
    $result = $warehouseModel->register($warehouse_name, $daily_order_limit, $priority_value);
    echo $result; // Output the result message to the client
}

class WarehouseModel
{
    private $conn;
    private $table = 'warehouse';

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function register($warehouseName, $dailyOrderLimit, $priorityValue)
    {
        // Input validation
        if (empty($warehouseName) || empty($dailyOrderLimit) || empty($priorityValue)) {
            return 'Warehouse name and priority value are required.';
        }

        // Check total warehouse count
        $totalCount = $this->howManyWareHouse();
        // System can have n warehouse
        $maxWarehouseCount = 4; // Maximum warehouse count
        if ($totalCount >= $maxWarehouseCount) {
            return 'Too many warehouses. The operation has been canceled.';
        }

        // Check if priority value is already used
        if (!$this->priorityValueCheck($priorityValue)) {
            return 'The priority value is already used. Please choose a different priority value.';
        }

        // Insert warehouse data into the database using a prepared statement
        $query = "INSERT INTO " . $this->table . " (warehouse_name, daily_order_limit, priority_value) VALUES (:warehouseName, :daily_order_limit, :priorityValue)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':warehouseName', $warehouseName);
        $stmt->bindValue(':daily_order_limit', $dailyOrderLimit);
        $stmt->bindValue(':priorityValue', $priorityValue);
        if ($stmt->execute()) {
            return 'New warehouse added successfully.';
        } else {
            return 'Error while adding warehouse.';
        }
    }

    public function howManyWareHouse()
    {
        $query = "SELECT COUNT(*) AS total_warehouses FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total_warehouses'];
    }

    public function priorityValueCheck($priorityValue)
    {
        $query = "SELECT COUNT(*) AS total FROM " . $this->table . " WHERE priority_value = :priorityValue";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':priorityValue', $priorityValue);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] > 0 ? false : true;
    }

    public function listWareHouse()
    {
        $query = "SELECT * FROM warehouse ORDER BY priority_value";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
}
