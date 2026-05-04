<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];   // Capture email
    $address = $_POST['address']; // Capture address
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    try {
        $sql = "INSERT INTO users (username, email, address, password) 
                VALUES (:username, :email, :address, :password)";
        
        $stmt = $pdo->prepare($sql);
        
        // Execute with the new mapped values
        $stmt->execute([
            ':username' => $user, 
            ':email'    => $email,
            ':address'  => $address,
            ':password' => $pass
        ]);
        
        header("Location: login.html");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { 
            // This will trigger if either the username OR email is a duplicate 
            // (assuming you set the email column to UNIQUE in your DB)
            echo "Username or Email already taken.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>