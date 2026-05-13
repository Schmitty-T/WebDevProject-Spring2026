<!doctype html>
<?php
    $db = new PDO("sqlite:workouts.db");       
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("
        CREATE TABLE IF NOT EXISTS Exercises (
            Exercise VARCHAR(30) NOT NULL,
            MuscleGroup VARCHAR(10),
            TutorialVideo VARCHAR(100)
        );
        ");
        $musclegroup = $_GET['MuscleGroup'] ?? null;
        if($musclegroup){
            $stmt = $db->prepare("SELECT * FROM Exercises WHERE MuscleGroup = :group");
            $stmt -> execute(['group'=>$musclegroup]);       
        }else{
            $stmt = $db->query("SELECT * FROM Exercises");
        }        
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);        
      
?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Exercises</title>
    <link rel="stylesheet" href="exercises.css" />
    <link rel="stylesheet" href="homepage.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Jura:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
      rel="stylesheet"
    />

    <meta name="author" content="Hagen Cooley" />
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
            <li class="page-link">
              <a href="dailyroutine.php">Daily Routine</a>
            </li>
            <li class="page-link" id="current-page">
              <a href="workouts.php">Exercises</a>
            </li>
            <li class="page-link">
              <a href="progress.php">Progress</a>
            </li>
            <li class="page-link">
              <a href="contact_us.html">Contact Us</a>
            </li>
          </ul>
        </div>
      </nav>
      <button id="themeToggle" aria-label="Toggle dark and light theme">Switch Theme</button>
      <div id="SearchContainer">
          <input type='text' id='ExerciseSearch' placeholder='Search' autocomplete='off'/>
      </div>
      <div class="CartItemsContainer">
            <table class="ExerciseContainer">
                <thead>
                    <tr>
                        <th>Exercises</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($exercises as $exercise): ?>
                                <tr>
                                    <td class="titlecell">
                                        <?php echo $exercise['Exercise'];?><br><!-- comment -->
                                        <div class="buttoncontainer">
                                            <p class="musclegroupplaceholder"><?php echo $exercise['MuscleGroup'];?></p>
                                            <a class="tutorialvideobutton" type="button" target="_blank" href="<?php echo $exercise['TutorialVideo'];?>" name="Tutorial">Tutorial</a>
                                        </div>
                                    </td>                                                    
                                </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="FilterContainer">
                <thead>
                    <tr>
                        <th colspan="2">Muscle Groups</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <a href="workouts.php?MuscleGroup=Chest">Chest</a>
                        </td>
                        <td>
                            <a href="workouts.php?MuscleGroup=Arms">Arms</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a href="workouts.php?MuscleGroup=Back">Back</a>
                        </td>
                        <td>
                            <a href="workouts.php?MuscleGroup=Shoulders">Shoulders</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a href="workouts.php?MuscleGroup=Abs">Abs</a>
                        </td>
                        <td>
                            <a href="workouts.php?MuscleGroup=Legs">Legs</a>
                        </td>
                    </tr>
                </tbody>
            </table><!-- comment -->
      </div>
    </header>

    

    <script src="workout.js"></script>
  </body>
</html>
