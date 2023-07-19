<?php

require("db_connection.php");

class Auth
{
    // This Function Will Be Used For Register.
    public function register($name, $surname, $email, $password)
    {
        global $conn;
        // Validate inputs
        if (!$name || !$surname || !$email || !$password) {
            echo "Missing fields in the form.";
            return;
        }

        // Check if user already exists
        if ($this->isUserExists($email)) {
            echo "User already exists.";
            return;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $sql = "INSERT INTO customer (name, surname, email, password) VALUES (:name, :surname, :email, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            echo "Welcome To The Club.";
            // After register, redirect to the main page
            header("Location: http://localhost/website/dashboard.php");
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }


    // Method for login process
    // Method for login process
    public function login($email, $password)
    {
        global $conn;

        // Check if user exists
        if ($this->isUserExists($email)) {

            // Retrieve the user from the database based on the email
            $user = $this->getUserByEmail($email);

            $hashedPassword = $user['password'];

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                $this->startSession($user);
                header("Location: http://localhost/website/dashboard.php");
                exit();
            } else {
                $this->displayErrorMessage("Incorrect password. Please try again.");
            }
        } else {
            $this->displayErrorMessage("User does not exist. Please check your email or register an account.");
        }
    }




    // Start the session and set session variables
    private function startSession($user)
    {
        session_start();
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['Name'] = $user['name']; // Assuming the username is stored in $user['username']
        $_SESSION['customerID'] = $user['id'];
    }

    // Redirect to a given page
    private function redirect($page)
    {
        $baseUrl = "http://localhost/website/";
        $url = $baseUrl . $page;
        header("Location: $url");
        exit();
    }

    // Display an error message
    private function displayErrorMessage($message)
    {
        echo $message;
    }

    // Method for logging out
    public function logout()
    {
        session_start();
        $_SESSION['isLoggedIn'] = false;
        session_destroy();

        header('Location: login.php');
        exit();
    }
    // Check if a user already exists in the database based on email
    private function isUserExists($email)
    {
        global $conn;
        $sql = "SELECT email FROM customer WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }

    // Retrieve user from the database based on email
    private function getUserByEmail($email)
    {
        global $conn;
        $sql = "SELECT * FROM customer WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
