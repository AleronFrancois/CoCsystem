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
    $username = trim($_POST['newUsername']);
    $password = $_POST['newPassword'];
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO User (username,password,role) VALUES (?,?,'investigator')"); //inserts new supervisor into db
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
    <link rel="stylesheet" href="styles/styles.css">
    <title>Add Investigator</title>
</head>
<body>
    <h1>Add a new Investigator</h1>

    <form name="addUserForm" id="newUserForm" method="Post" novalidate>
        <label for="newUsername">Username:</label>
        <input type="text" id="newUsername" name="newUsername" value = "" required>

        <label for="newPassword">Password:</label>
        <input type="password" id="newPassword" name="newPassword" value = "" required>

        <button type="submit">Submit</button>
    </form>
</body>
</html>



