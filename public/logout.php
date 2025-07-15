<?php
session_start();
session_destroy();

// Redirect straight back to login page
header("Location: /student-system/public/login.php");
exit;
