<?php
session_start();
require("../website/include/header.php");
include("include/footer.php");

// Check if the user is logged in
if (!isset($_SESSION['isLoggedIn'])) header('Location: login.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/header.css">

<body>

    <?php
    print_r($_SESSION);

    if ($_GET["sayfa"]) {
        $sayfa = $_GET["sayfa"];
        if ($sayfa == "sepet") {
            include("product_list.php");
        }
    }

    if ($_GET["sayfa"]) {
        $sayfa = $_GET["sayfa"];
        if ($sayfa == "logout") {
            include("auth.php");
            $auth = new Auth();
            $auth->logout($email, $password);
        }
    }
    if ($_GET["sayfa"]) {
        $sayfa = $_GET["sayfa"];
        if ($sayfa == "depolar") {
            include("warehouses/views/warehouse_list.php");
        }
    }


    ?>

</body>



</html>