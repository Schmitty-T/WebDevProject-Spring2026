<?php
    $db = new PDO("sqlite:workouts.db");       
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $search = $_GET['q'] ?? '';
    $stmt = $db->prepare("SELECT * FROM Exercises
                          WHERE Exercise LIKE :search
                          OR MuscleGroup LIKE :search");
    $stmt ->execute([
        ':search' => '%' . $search . '%'
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($results);      
?>
