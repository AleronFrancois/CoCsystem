<?php
session_start();
session_unset();  
session_destroy(); 
header("Location: ../index.php"); // redirect to login page
exit;
?>