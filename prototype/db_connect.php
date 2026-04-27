<?php
try {
    // Connect to the SQLite database provided by your teammate
    $pdo = new PDO("sqlite:workouts.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>