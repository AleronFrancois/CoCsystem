<?php
// User panel
// Shows all current users
session_start();
require "includes/dbconn.php"; 

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

if ( $_SESSION['role'] !== 'supervisor') {
    include "components/access_denied.php";
    exit;
}




// Get all users
$stmt = $conn->prepare("SELECT username, id FROM User");
$stmt->execute();
$investigators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>User Panel</title>
        <link rel="stylesheet" href="styles/bootstrap.min.css">
        <link rel="stylesheet" href="styles/styles.css">
        <script src="scripts/bootstrap.bundle.min.js" defer></script>
        <script src="scripts/scripts.js" defer></script>
    </head>
    <body> 
        <?php include "components/navbar.php"?>
        <div class="p-3 border foreground shadow rounded-5 h-100 m-5">
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
                echo '<a class="btn btn-primary" href="newuser.php">Add User </a>';
            }
            ?>
        </div>
    </body>
</html>
