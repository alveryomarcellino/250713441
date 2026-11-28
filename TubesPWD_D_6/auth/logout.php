<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // diarahkan ke login page
exit;
