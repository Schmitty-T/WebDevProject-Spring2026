<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

// enable foreign keys for sqlite
$pdo->exec("PRAGMA foreign_keys = ON");

$stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = :u");
$stmt->execute([':u' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// bad session username
if (!$user) {
    session_destroy();
    header("Location: login.html");
    exit();
}

// main table for messages
$pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    subject TEXT NOT NULL,
    message TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username)
)");

// bridge table linking users to their messages

$pdo->exec("CREATE TABLE IF NOT EXISTS user_contacts (
    user_id TEXT NOT NULL,
    message_id INTEGER NOT NULL,
    PRIMARY KEY (user_id, message_id),
    FOREIGN KEY (user_id) REFERENCES users(username),
    FOREIGN KEY (message_id) REFERENCES contact_messages(id)
)");

$err = '';

// generate csrf token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // csrf check
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $err = 'Invalid request.';
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $msg = trim($_POST['message'] ?? '');

        if (!$subject || !$msg) {
            $err = 'Please fill out all fields.';
        } elseif (strlen($msg) < 10) {
            $err = 'Message is too short.';
        } else {
            $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
            $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');

            try {
                $pdo->beginTransaction();

                $ins = $pdo->prepare("INSERT INTO contact_messages (username, subject, message)
                                       VALUES (:u, :s, :m)");
                $ins->execute([
                    ':u' => $user['username'],
                    ':s' => $subject,
                    ':m' => $msg
                ]);

                $msgId = $pdo->lastInsertId();

                // insert into bridge table
                $bridge = $pdo->prepare("INSERT INTO user_contacts (user_id, message_id)
                                          VALUES (:uid, :mid)");
                $bridge->execute([
                    ':uid' => $user['username'],
                    ':mid' => $msgId
                ]);

                $pdo->commit();

                // refresh csrf after submit
                $_SESSION['csrf'] = bin2hex(random_bytes(32));

                header("Location: contact_us.php?sent=1");
                exit();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $err = 'Something went wrong, please try again.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">

  <head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Contact Us</title>
    <meta name="author" content="Saurya Singh" />

    <link rel="stylesheet" href="homepage.css" />
    <link rel="stylesheet" href="contact_us.css" />
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
          <img src="logo.jpeg" alt="Phantom Training logo" id="logo" />
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
            <li class="page-link">
              <a href="progress.php">Progress</a>
            </li>
            <li class="page-link" id="current-page">
              <a href="contact_us.php">Contact Us</a>
            </li>
          </ul>
        </div>
      </nav>
    </header>
    <button id="themeToggle" aria-label="Toggle dark and light theme" style="position:static;display:block;margin:10px 0 0 10px;">Switch Theme</button>

    <main>
      <div id="page-wrap">
        <div id="info-side">
          <h3>Get in Touch</h3>
          <br />

          <p>Got questions about your training? Not sure where to start?
          Drop us a message and we'll help you out.</p>
          <br />

          <ul>
            <li><h4>Training Questions: </h4><p>Ask about routines or getting started.</p></li>
            <li><h4>Progress Support: </h4><p>Struggling to hit goals? Lets talk.</p></li>
            <li><h4>Site Issues: </h4><p>Something broken? Tell us.</p></li>
            <li><h4>Feedback: </h4><p>We actually read it all.</p></li>
          </ul>

        </div>

        <div id="form-side">

          <?php if (isset($_GET['sent'])): ?>
            <p id="php-success">Message sent! We'll get back to you.</p>
          <?php endif; ?>

          <?php if ($err): ?>
            <p id="php-err"><?php echo $err; ?></p>
          <?php endif; ?>

          <form id="cform" method="POST" action="contact_us.php" novalidate>

            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" />

            <div class="frow">
              <input id="uname" type="text" placeholder="Full Name"
                value="<?php echo htmlspecialchars($user['username']); ?>"
                maxlength="80" readonly />
            </div>

            <div class="frow">
              <input type="email" placeholder="Email Address" id="uemail"
                value="<?php echo htmlspecialchars($user['email']); ?>"
                readonly />
            </div>

            <div class="frow">
              <input type="tel" id="uphone" maxlength="15" placeholder="Phone (optional)" />
              <span class="ferr" id="uphone-err">Numbers only, 7-15 digits.</span>
            </div>

            <div class="frow">
              <select id="utopic" name="subject">
                <option value="">Subject</option>
                <option value="training">Training Question</option>
                <option value="progress">Progress Help</option>
                <option value="bug">Site Issue</option>
                <option value="other">Other</option>
              </select>
              <span class="ferr" id="utopic-err">Pick a subject.</span>
            </div>

            <div class="frow">
              <textarea id="umsg" name="message" maxlength="1000" placeholder="Your message here..."></textarea>
              <span id="ccount">0 / 1000</span>
              <span class="ferr" id="umsg-err">To short, need at least 10 characters.</span>
            </div>

            <button type="submit" id="sbtn">Send Message</button>
          </form>
        </div>

      </div>
    </main>

    <script src="contact_us.js"></script>

  </body>

</html>
