<?php
session_start();
session_destroy(); // Clears the server memory for this user
header("Location: homepage.php"); // Send them back home
exit();
?>