<?php

// # error reporting
// ini_set('display_errors', 1);
// error_reporting(E_ALL | E_STRICT);

require('db_connection.php');
require('warehouses/classes/warehouse_managment_sql.php'); // Include the file where WarehouseManager class is defined

use PHPUnit\Framework\TestCase;

class WarehouseTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Set up your database connection or other environment here
        $this->conn = new PDO("mysql:host=localhost;dbname=admin", 'root', "");
    }

    public function testWareHouse()
    {
        // WarehouseManager class instance
        $WarehouseManager = new WarehouseManager($this->conn);

        // Test data
        $data = $WarehouseManager->sortedArray([
            15=> 3,
            17=> 5,
            18=> 15,
            3=> 2,
        ]);

        // Expected result
        $expectedResult = [
            18=> 15,
            17=> 5,
            15=> 3,
            3=> 2,
        ];

        $this->assertEquals($expectedResult, $data);
    }
}
