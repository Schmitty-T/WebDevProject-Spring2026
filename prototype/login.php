<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $user]);
    $db_user = $stmt->fetch();

    if ($db_user && password_verify($pass, $db_user['password'])) {
        $_SESSION['username'] = $db_user['username'];
        header("Location: homepage.php"); // Redirect to your new PHP homepage
        exit();
    } else {
        echo "Invalid username or password. <a href='login.html'>Try again</a>";
    }
}
?>