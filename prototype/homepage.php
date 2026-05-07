<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Homepage | Phantom Training</title>
    <link rel="stylesheet" href="homepage.css" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />

    <meta name="author" content="David Mbagwu" />
  </head>
  <body>
    <header>
      <nav>
        <div id="logo-container">
          <a href="homepage.php">
            <img src="logo.jpeg" alt="Phantom Training Logo" id="logo" />
          </a>
        </div>
        <div id="nav-container">
          <div id="nav-header">
            <h2>Phantom Training</h2>
          </div>
          <ul id="nav-bar">
            <li class="page-link" id="current-page">
              <a href="homepage.php">Home</a>
            </li>
            <li class="page-link">
              <a href="daily_routine.php">Daily Routine</a>
            </li>
            <li class="page-link">
              <a href="workouts.php">Exercises</a>
            </li>
            <li class="page-link">
              <a href="progress.html">Progress</a>
            </li>
            <li class="page-link">
              <a href="contact_us.html">Contact Us</a>
            </li>
          </ul>
        </div>
      </nav>
      <button id="themeToggle" aria-label="Toggle dark and light theme">Switch Theme</button>
    </header>

    <main>
      <section class="body-container">
        <article id="body-description">
          <h3>Unleash Your Inner Strength</h3>
          
          <p>
            Phantom Training isn't just a workout; it’s a systematic approach to
            elite physical performance. Whether you’re training at home or in
            the gym, get the discipline, tracking, and routines you need to
            disappear from your old self and re-emerge stronger.
          </p>

          <ul class="features-list">
            <li>
              <strong>Precision Routine:</strong>
              <p>Expert-crafted daily plans for all fitness levels.</p>
            </li>
            <li>
              <strong>Dynamic Progress Tracking:</strong>
              <p>Visualize your gains with real-time data.</p>
            </li>
            <li>
              <strong>Exercise Library:</strong>
              <p>Comprehensive tutorials to master your form.</p>
            </li>
            <li>
              <strong>Direct Support:</strong>
              <p>Contact our trainers for personalized adjustments.</p>
            </li>
          </ul>

          <section id="auth-ui-container">
            <?php if (!isset($_SESSION['username'])): ?>
            <div id="logged-out-view">
              <div id="body-button-holder">
                <a href="register.html" class="body-button" id="register">Register</a>
                <a href="login.html" class="body-button" id="login">Login</a>
              </div>
            </div>
            <?php else: ?>
            <div id="logged-in-view">
              <h3 id="welcome-message">
                Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
              </h3>
              <div id="body-button-holder">
                <a href="logout.php" class="body-button" id="logout-btn">Logout</a>
              </div>
            </div>
            <?php endif; ?>
          </section>
        </article>

        <figure id="body-img-container">
          <img src="workout.png" alt="Athlete performing a workout" id="body-img" />
        </figure>
      </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Phantom Training. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
  </body>
</html>