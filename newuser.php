<?php
session_start();
require_once "/dbconn.php"; 

if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];

  
    $stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
    $stmt->execute([$userId]);
    $role = $stmt->fetchColumn();

    if ($role != 'supervisor') {
        header("Location: index.php");
        exit;
    }
}
?>
<html>

