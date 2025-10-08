<?php
// User panel
// Shows all current users
session_start();
require "includes/dbconn.php"; 

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION["id"];
$stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();


// Get all users
$stmt = $conn->prepare("SELECT username, id FROM User");
$stmt->execute();
$investigators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/styles.css">
    <title>User Panel</title>
</head>
<body> 
    <?php include "components/navbar.php"?>
    <h1>User Panel</h1>
    <table>
        <tr>
            <th>Investigator ID</th>
            <th>Investigator Name</th>
        </tr>
        <?php foreach ($investigators as $inv): ?>
            <tr>
                <td><?php echo htmlspecialchars($inv['id']); ?></td>
                <td><?php echo htmlspecialchars($inv['username']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <!-- Allows Supervisors to Add users -->
    <?php 
    if ($role == "supervisor") {
        echo '<a class="btn" href="newuser.php">Add User </a>';
    }
    ?>
</body>
</html>
