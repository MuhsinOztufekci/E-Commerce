<?php

// Warehouse Management Interface
interface WarehouseManagement
{
    // Pulls the products that customer buy
    public function getAllProducts($product_id);

    // Pulls warehouses priority values
    public function getPriorityValue();

    // İs product exist in warehouse
    public function isProductExist($product_id, $warehouse_id);

    // Update relatinal tables 
    public function updateTables($product_id, $order_id, $warehouse_info);
}
