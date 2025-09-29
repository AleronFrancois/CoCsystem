<?php
session_start();
require_once "/dbconn.php"; // include your db connection

if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];

    // Query for the user's role
    $stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
    $stmt->execute([$userId]);
    $role = $stmt->fetchColumn();

    // Check role
    if ($role != 'supervisor') {
        header("Location: index.php");
        exit;
    }
}
?>
<html>
    
