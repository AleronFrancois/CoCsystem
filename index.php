<?php
session_start();

if(isset($_SESSION["id"])) {
    include "cases.php";
} else {
    include "login.php";
}
?>
