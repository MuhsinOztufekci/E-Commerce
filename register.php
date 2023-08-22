<?php
include("auth.php");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Kayıt Ol</title>
    <style>
        /* CSS Styles */

        .center {
            text-align: center;
            padding: 20px;
        }


        body {
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
            height: 100vh;
            margin: 0;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 5px;
            width: 400px;
        }

        label {
            color: #333333;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #cccccc;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* Error message style */
        .error-message {
            color: red;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="center">
        <h3>Register</h3>
        <form method="POST">
            <label for="name">Ad:</label>
            <input type="text" name="name" required><br>

            <label for="surname">Soyad:</label>
            <input type="text" name="surname" required><br>

            <label for="email">E-posta:</label>
            <input type="email" name="email" required><br>

            <label for="password">Şifre:</label>
            <input type="password" name="password" required><br>

            <input type="submit" value="Register">
        </form>
        <p>Üye girişi için buraya <a href="login.php">Giriş yap.</a></p>
    </div>
    <?php

    if ($_POST) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $auth = new Auth();

        $auth->register($name, $surname, $email, $password);
    }

    ?>
</body>

</html>