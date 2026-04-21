<!doctype html>
<?php
$db = new PDO("sqlite:workouts.db");

/* DAILY MUSCLE GROUP */
$groups = ["Chest", "Back", "Legs", "Shoulders", "Arms", "Abs"];
$dayIndex = date("w");
$selectedGroup = $groups[$dayIndex % count($groups)];

/* GET RANDOM EXERCISES */
$stmt = $db->prepare("
    SELECT * FROM Exercises 
    WHERE MuscleGroup = :group 
    ORDER BY RANDOM() 
    LIMIT 5
");
$stmt->execute(['group' => $selectedGroup]);

$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daily Routine</title>

  <link rel="stylesheet" href="homepage.css">
  <link rel="stylesheet" href="dailyroutine.css">
</head>

<body>

<header>
  <nav>
    <div id="logo-container">
      <a href="homepage.html">
        <img src="logo.jpeg" alt="logo">
      </a>
    </div>

    <div id="nav-container">
      <h2>Phantom Training</h2>

      <div id="nav-bar">
        <div class="page-link">
          <a href="homepage.html">Home</a>
        </div>

        <div class="page-link" id="current-page">
          <a href="dailyroutine.php">Daily Routine</a>
        </div>

        <div class="page-link">
          <a href="workouts.php">Exercises</a>
        </div>

        <div class="page-link">
          <a href="progress.html">Progress</a>
        </div>

        <div class="page-link">
          <a href="contact_us.html">Contact Us</a>
        </div>
      </div>
    </div>
  </nav>

  <button id="themeToggle">Dark Mode</button>
</header>

<main>

<div class="routine-container">

  <!-- LEFT SIDE -->
  <div class="routine-main">
    <h2><?php echo $selectedGroup; ?> Day</h2>

    <table>
      <?php foreach($exercises as $index => $exercise): ?>
        <tr id="row<?php echo $index; ?>">
          <td>
            <?php echo $exercise['Exercise']; ?><br>
            <a href="<?php echo $exercise['TutorialVideo']; ?>" target="_blank">
              Watch Tutorial
            </a>
          </td>
          <td><?php echo $exercise['MuscleGroup']; ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- RIGHT SIDE -->
  <div class="routine-sidebar">
    <h2>Checklist</h2>

    <div class="checklist">
      <?php foreach($exercises as $index => $exercise): ?>
        <label>
          <input type="checkbox" data-index="<?php echo $index; ?>">
          <?php echo $exercise['Exercise']; ?>
        </label>
      <?php endforeach; ?>
    </div>

    <button onclick="clearProgress()">Clear Progress</button>
  </div>

</div>

</main>

<script src="dailyroutine.js"></script>
<script src="script.js"></script>

</body>
</html>