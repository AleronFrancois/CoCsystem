<?php
session_start();

if(isset($_SESSION["user_id"])) {
    include "cases.php";
} else {
    include "login.php";
}
?>
