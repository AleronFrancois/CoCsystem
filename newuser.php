<?php
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

if ($role !== 'supervisor') { //checks user is a supervisor
    header("Location: cases.php");
    exit;
}
  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['newUsername']));
    $password = $_POST['newPassword'];
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO User (username,password,role) VALUES (?,?,'investigator')"); // Inserts new investigator into db
    if($stmt->execute([$username,$hashedPassword])){
        echo "User Added succesfully";
    }
    else{
        echo "User not added";
    }
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Investigator</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
    <script src="scripts/scripts.js" defer></script>
</head>
<body>
    <?php include "components/navbar.php"; ?>
    <div class="p-3 border foreground shadow rounded-5 m-auto mt-5 w-50">
        <h1>Add a New Investigator</h1>

        <form name="addUserForm" id="newUserForm" method="Post" novalidate>
            <label for="newUsername">Username:</label>
            <input type="text" id="newUsername" name="newUsername" class="form-control" value = "" required>

            <label for="newPassword">Password:</label>
            <input type="password" id="newPassword" name="newPassword" class="form-control" value = "" required>

            <button class="btn btn-primary mt-3" type="submit">Submit</button>
        </form>
    </div>
</body>
</html>



