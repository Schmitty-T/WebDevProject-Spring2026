<?php
session_start();

// Connect to database
try {
    $pdo = new PDO("sqlite:workouts.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the logged-in user
$user_id = $_SESSION['user_id'] ?? null;

// If no user is logged in, use the first user in the database as a fallback for the demo
if (!$user_id) {
    $row = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $user_id = $row['id'] ?? 1;
}

// Create the ProgressEntries and Goals tables if they don't already exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ProgressEntries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            entry_date TEXT NOT NULL,
            weight REAL,
            body_fat REAL,
            muscle_mass REAL,
            workouts_done INTEGER DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS Goals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            goal_text TEXT NOT NULL,
            achieved INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
} catch (PDOException $e) {
    die("Could not create tables: " . $e->getMessage());
}

// Will hold an error or success message to show the user
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // CREATE: add a new goal
        if ($action === 'add_goal') {
            $goal_text = trim($_POST['goal_text'] ?? '');

            if ($goal_text === '') {
                $error = 'Please enter a goal before adding.';
            } elseif (strlen($goal_text) > 200) {
                $error = 'Goal is too long. Please keep it under 200 characters.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO Goals (user_id, goal_text) VALUES (?, ?)");
                $stmt->execute([$user_id, $goal_text]);
                $success = 'Goal added!';
            }
        }

        // UPDATE: toggle a goal between achieved and not achieved
        if ($action === 'toggle_goal') {
            $goal_id = (int) ($_POST['goal_id'] ?? 0);
            if ($goal_id > 0) {
                $stmt = $pdo->prepare("UPDATE Goals SET achieved = 1 - achieved WHERE id = ? AND user_id = ?");
                $stmt->execute([$goal_id, $user_id]);
            }
        }

        // DELETE: remove a goal
        if ($action === 'delete_goal') {
            $goal_id = (int) ($_POST['goal_id'] ?? 0);
            if ($goal_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM Goals WHERE id = ? AND user_id = ?");
                $stmt->execute([$goal_id, $user_id]);
                $success = 'Goal deleted.';
            }
        }

        // CREATE: log a new progress entry (weight, body fat, workouts done, etc.)
        if ($action === 'log_progress') {
            $entry_date = $_POST['entry_date'] ?? date('Y-m-d');
            $weight = $_POST['weight'] !== '' ? (float) $_POST['weight'] : null;
            $body_fat = $_POST['body_fat'] !== '' ? (float) $_POST['body_fat'] : null;
            $muscle_mass = $_POST['muscle_mass'] !== '' ? (float) $_POST['muscle_mass'] : null;
            $workouts_done = (int) ($_POST['workouts_done'] ?? 0);

            // Validate the inputs
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry_date)) {
                $error = 'Please choose a valid date.';
            } elseif ($weight !== null && ($weight < 50 || $weight > 600)) {
                $error = 'Weight must be between 50 and 600 lbs.';
            } elseif ($body_fat !== null && ($body_fat < 1 || $body_fat > 60)) {
                $error = 'Body fat must be between 1 and 60%.';
            } elseif ($muscle_mass !== null && ($muscle_mass < 30 || $muscle_mass > 400)) {
                $error = 'Muscle mass must be between 30 and 400 lbs.';
            } elseif ($workouts_done < 0 || $workouts_done > 10) {
                $error = 'Workouts done must be between 0 and 10.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO ProgressEntries (user_id, entry_date, weight, body_fat, muscle_mass, workouts_done)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $entry_date, $weight, $body_fat, $muscle_mass, $workouts_done]);
                $success = 'Progress logged!';
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }

    // If everything went well, redirect to clear the form so refreshing doesn't resubmit
    if ($error === '' && $action !== 'toggle_goal') {
        header("Location: progress.php?msg=" . urlencode($success));
        exit;
    }
}

// If we got redirected here with a success message in the URL, show it
if (isset($_GET['msg']) && $success === '') {
    $success = $_GET['msg'];
}

// Read all the data we need for display
try {
    // 1. Get the latest body metrics (most recent entry with weight/fat/muscle data)
    $stmt = $pdo->prepare("
        SELECT weight, body_fat, muscle_mass
        FROM ProgressEntries
        WHERE user_id = ? AND (weight IS NOT NULL OR body_fat IS NOT NULL OR muscle_mass IS NOT NULL)
        ORDER BY entry_date DESC, id DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $latest_body = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'weight' => null,
        'body_fat' => null,
        'muscle_mass' => null
    ];

    // 2. Get the total number of workouts completed by this user
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(workouts_done), 0) AS total FROM ProgressEntries WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_workouts = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Calculate the current streak (consecutive days from today with at least 1 workout)
    $stmt = $pdo->prepare("
        SELECT DISTINCT entry_date
        FROM ProgressEntries
        WHERE user_id = ? AND workouts_done > 0
        ORDER BY entry_date DESC
    ");
    $stmt->execute([$user_id]);
    $worked_dates = array_flip(array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'entry_date'));

    $streak = 0;
    $cursor = date('Y-m-d');
    // If user hasn't logged today yet, start counting from yesterday
    if (!isset($worked_dates[$cursor])) {
        $cursor = date('Y-m-d', strtotime('-1 day'));
    }
    while (isset($worked_dates[$cursor])) {
        $streak++;
        $cursor = date('Y-m-d', strtotime($cursor . ' -1 day'));
    }

    // 4. Count how many goals the user has achieved
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM Goals WHERE user_id = ? AND achieved = 1");
    $stmt->execute([$user_id]);
    $goals_achieved = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // 5. Get all the user's goals (so we can display them as a list)
    $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = ? ORDER BY achieved ASC, id DESC");
    $stmt->execute([$user_id]);
    $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Get workouts-per-day for the last 30 days (used for the chart)
    $stmt = $pdo->prepare("
        SELECT entry_date, SUM(workouts_done) AS wd
        FROM ProgressEntries
        WHERE user_id = ? AND entry_date >= date('now', '-30 days')
        GROUP BY entry_date
    ");
    $stmt->execute([$user_id]);
    $by_date = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $by_date[$row['entry_date']] = (int) $row['wd'];
    }

    // Build weekly chart data (last 7 days)
    $weekly_labels = [];
    $weekly_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $weekly_labels[] = date('D', strtotime($date));
        $weekly_data[] = $by_date[$date] ?? 0;
    }

    // Build monthly chart data (last 4 weeks, grouped weekly)
    $monthly_labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
    $monthly_data = [0, 0, 0, 0];
    for ($i = 27; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $week_index = 3 - intdiv($i, 7);
        $monthly_data[$week_index] += $by_date[$date] ?? 0;
    }

    // 7. Pick 3 random exercises from the Exercises table for the progress bars
    $stmt = $pdo->query("SELECT Exercise FROM Exercises ORDER BY RANDOM() LIMIT 3");
    $top_exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate a stable progress % for each (based on exercise name + user, plus a small boost from total workouts)
    $exercise_progress = [];
    foreach ($top_exercises as $exercise) {
        $base = abs(crc32($exercise['Exercise'] . $user_id)) % 50 + 30;
        $boost = min(20, (int) floor($total_workouts / 2));
        $exercise_progress[] = [
            'name' => $exercise['Exercise'],
            'percent' => min(100, $base + $boost)
        ];
    }

} catch (PDOException $e) {
    // If anything fails, use safe defaults so the page still renders
    $error = ($error ? $error . ' | ' : '') . 'Could not load progress data: ' . $e->getMessage();
    $latest_body = ['weight' => null, 'body_fat' => null, 'muscle_mass' => null];
    $total_workouts = 0;
    $streak = 0;
    $goals_achieved = 0;
    $goals = [];
    $weekly_labels = [];
    $weekly_data = [];
    $monthly_labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
    $monthly_data = [0, 0, 0, 0];
    $exercise_progress = [];
}


function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Progress</title>
    <link rel="stylesheet" href="progress.css" />
    <link rel="stylesheet" href="homepage.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Jura:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet" />

    <meta name="author" content="Santosh" />
</head>

<body>
    <!-- Top section with the logo and menu -->
    <header>
        <nav>
            <div id="nav-container">
                <img src="logo.jpeg" alt="Phantom Training Logo" id="logo" />
                <ul id="nav-bar">
                    <li class="page-link">
                        <a href="homepage.php">Home</a>
                    </li>
                    <li class="page-link">
                        <a href="dailyroutine.php">Daily Routine</a>
                    </li>
                    <li class="page-link">
                        <a href="workouts.php">Exercises</a>
                    </li>
                    <li class="page-link" id="current-page">
                        <a href="progress.php">Progress</a>
                    </li>
                    <li class="page-link">
                        <a href="contact_us.html">Contact Us</a>
                    </li>
                </ul>
            </div>
        </nav>
        <button id="themeToggle" aria-label="Toggle dark and light theme">
            Switch Theme
        </button>
    </header>
    <main>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>


        <!-- Title area for the page -->
        <div id="page-heading">
            <h1>Progress Tracker</h1>
            <p>"Track your journey to strength"</p>
        </div>

        <!-- Main workout numbers -->
        <div class="stats-row">
            <div class="stat-card">
                <h4>Total Workouts</h4>
                <p class="stat-value"><?= h($total_workouts) ?></p>
            </div>
            <div class="stat-card">
                <h4>Current Streak</h4>
                <p class="stat-value"><?= h($streak) ?> Days</p>
            </div>
            <div class="stat-card">
                <h4>Goals Achieved</h4>
                <p class="stat-value"><?= h($goals_achieved) ?></p>
            </div>
        </div>

        <!-- Basic body progress stats -->
        <div class="stats-row">
            <div class="stat-card">
                <h4>Weight</h4>
                <p class="stat-value">
                    <?= $latest_body['weight'] !== null ? h($latest_body['weight']) . ' lbs' : '—' ?>
                </p>
            </div>
            <div class="stat-card">
                <h4>Body Fat %</h4>
                <p class="stat-value">
                    <?= $latest_body['body_fat'] !== null ? h($latest_body['body_fat']) . '%' : '—' ?>
                </p>
            </div>
            <div class="stat-card">
                <h4>Muscle Mass</h4>
                <p class="stat-value">
                    <?= $latest_body['muscle_mass'] !== null ? h($latest_body['muscle_mass']) . ' lbs' : '—' ?>
                </p>
            </div>
        </div>

        <!-- Chart section with weekly and monthly view -->
        <div id="chart-section">
            <div id="chart-header">
                <h3>Progress Chart</h3>
                <div id="chart-toggle">
                    <button class="chart-btn active" id="weekly-btn">Weekly</button>
                    <button class="chart-btn" id="monthly-btn">Monthly</button>
                </div>
            </div>

            <!-- The bars will be added here through JavaScript -->
            <div id="chart-container"></div>
            <p id="chart-caption">Workouts completed</p>
        </div>

        <!-- Progress for different exercises -->
        <div id="exercise-section">
            <h3>Exercise Progress</h3>
            <?php foreach ($exercise_progress as $ep): ?>
                <div class="exercise-row">
                    <div class="exercise-info">
                        <p class="exercise-name">
                            <?= h($ep['name']) ?>
                        </p>
                        <p class="exercise-value">
                            <?= h($ep['percent']) ?>%
                        </p>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" data-progress="<?= h($ep['percent']) ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Goals section (add, view, mark complete, delete) -->
        <div id="goals-section">
            <h3>Current Goals</h3>
            <!-- Form to add a new goal -->
            <form method="post" class="goal-form" onsubmit="return validateGoalForm(this);">
                <input type="hidden" name="action" value="add_goal" />
                <input type="text" name="goal_text" placeholder="What's your next goal?" maxlength="200" required
                    class="goal-input" />
                <button type="submit" class="goal-add-btn">Add Goal</button>
            </form>

            <?php if (empty($goals)): ?>
                <p class="empty-msg">No goals yet. Add one above to get started!</p>
            <?php else: ?>
                <?php foreach ($goals as $i => $g): ?>
                    <div class="goal-row <?= $g['achieved'] ? 'achieved' : '' ?>">
                        <form method="post" class="goal-inline">
                            <input type="hidden" name="action" value="toggle_goal" />
                            <input type="hidden" name="goal_id" value="<?= h($g['id']) ?>" />
                            <input type="checkbox" onchange="this.form.submit()" <?= $g['achieved'] ? 'checked' : '' ?>
                                aria-label="Mark goal as achieved" />
                        </form>
                        <p class="goal-text">
                            <strong>Goal
                                <?= h($i + 1) ?>:
                            </strong>
                            <?= h($g['goal_text']) ?>
                        </p>
                        <form method="post" class="goal-inline">
                            <input type="hidden" name="action" value="delete_goal" />
                            <input type="hidden" name="goal_id" value="<?= h($g['id']) ?>" />
                            <button type="submit" class="goal-delete-btn" onclick="return showDeleteConfirm(this.form);"
                                aria-label="Delete goal">×</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Form to log today's progress (weight, body fat, workouts, etc.) -->
        <div id="log-section">
            <h3>Log Today's Progress</h3>
            <form method="post" class="log-form" onsubmit="return validateLogForm(this);">
                <input type="hidden" name="action" value="log_progress" />
                <div class="log-row">
                    <label>Date
                        <input type="date" name="entry_date" value="<?= h(date('Y-m-d')) ?>" required />
                    </label>
                    <label>Weight (lbs)
                        <input type="number" name="weight" min="50" max="600" step="0.1" placeholder="165" />
                    </label>
                    <label>Body Fat (%)
                        <input type="number" name="body_fat" min="1" max="60" step="0.1" placeholder="18" />
                    </label>
                    <label>Muscle Mass (lbs)
                        <input type="number" name="muscle_mass" min="30" max="400" step="0.1" placeholder="135" />
                    </label>
                    <label>Workouts Done
                        <input type="number" name="workouts_done" min="0" max="10" value="1" required />
                    </label>
                </div>
                <button type="submit" class="log-submit-btn">Save Entry</button>
            </form>
        </div>

        <!-- Custom confirm modal -->
        <div id="confirmModal" class="modal-overlay" style="display: none;">
            <div class="modal-box">
                <h3>Delete this goal?</h3>
                <p>This cannot be undone.</p>
                <div class="modal-buttons">
                    <button id="modalCancel" class="modal-btn modal-btn-cancel" type="button">Cancel</button>
                    <button id="modalConfirm" class="modal-btn modal-btn-delete" type="button">Delete</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Pass chart data from PHP to JavaScript -->
    <script>
        window.progressChartData = {
            weekly: { labels: <?= json_encode($weekly_labels) ?>, data: <?= json_encode($weekly_data) ?> },
            monthly: { labels: <?= json_encode($monthly_labels) ?>, data: <?= json_encode($monthly_data) ?> }
        };
    </script>

    <script src="progress.js"></script>
</body>

</html>