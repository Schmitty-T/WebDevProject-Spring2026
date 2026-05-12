<?php
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO('sqlite:workouts.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    try {
        $row = $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['id'] ?? 1;
    } catch (Exception $e) {
        $user_id = 1;
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS ProgressEntries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        entry_date TEXT NOT NULL,
        weight REAL,
        body_fat REAL,
        muscle_mass REAL,
        workouts_done INTEGER DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        throw new Exception('Invalid request payload.');
    }

    $workouts_done = isset($payload['workouts_done']) ? (int) $payload['workouts_done'] : 0;
    $entry_date = $payload['entry_date'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry_date)) {
        $entry_date = date('Y-m-d');
    }

    if ($workouts_done < 0) {
        $workouts_done = 0;
    }
    if ($workouts_done > 10) {
        $workouts_done = 10;
    }

    $stmt = $pdo->prepare('SELECT id FROM ProgressEntries WHERE user_id = ? AND entry_date = ? LIMIT 1');
    $stmt->execute([$user_id, $entry_date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $stmt = $pdo->prepare('UPDATE ProgressEntries SET workouts_done = ? WHERE id = ?');
        $stmt->execute([$workouts_done, $existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO ProgressEntries (user_id, entry_date, workouts_done) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $entry_date, $workouts_done]);
    }

    $stmt = $pdo->prepare('SELECT COALESCE(SUM(workouts_done), 0) AS total_workouts FROM ProgressEntries WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $total_workouts = (int) $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'entry_date' => $entry_date,
        'workouts_done' => $workouts_done,
        'total_workouts' => $total_workouts,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
