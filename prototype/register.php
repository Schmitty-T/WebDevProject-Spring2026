<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Securely hashes the password

    try {
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $user, ':password' => $pass]);
        
        echo "Registration successful! <a href='login.html'>Login here</a>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error code for "Unique constraint failed"
            echo "Username already taken.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>