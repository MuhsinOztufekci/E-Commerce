<?php
include("auth.php");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Giriş Yap</title>
    <link rel="stylesheet" type="text/css" href="./assets/css/login.css">
</head>

<body>
    <div class="center">
        <!-- Content here -->

        <h2>Giriş Yap</h2>
        <form method="POST">
            <label for="email">E-posta:</label>
            <input type="email" name="email" required><br>

            <label for="password">Şifre:</label>
            <input type="password" name="password" required><br>

            <input type="submit" value="Login">
        </form>

        <p>Henüz üye değil misiniz? <a href="register.php">Üye Ol</a></p>

    </div>

    <?php

    if ($_POST) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $auth = new Auth();
        $auth->login($email, $password);
    }
    ?>


</body>

</html>