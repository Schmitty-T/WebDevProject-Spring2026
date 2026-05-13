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

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Jura:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet"
  />
</head>
<body>

  <header>
      <nav>
        <div id="nav-container">
          <img src="logo.jpeg" alt="Phantom Training Logo" id="logo" />
          <ul id="nav-bar">
            <li class="page-link">
              <a href="homepage.php">Home</a>
            </li>
            <li class="page-link" id="current-page">
              <a href="dailyroutine.php">Daily Routine</a>
            </li>
            <li class="page-link">
              <a href="workouts.php">Exercises</a>
            </li>
            <li class="page-link">
              <a href="progress.php">Progress</a>
            </li>
            <li class="page-link">
              <a href="contact_us.php">Contact Us</a>
            </li>
          </ul>
        </div>
      </nav>
      <button id="themeToggle" aria-label="Toggle dark and light theme">Switch Theme</button>
    </header>
</body>
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

<script src="script.js"></script>
<script src="dailyroutine.js"></script>


</body>
</html>