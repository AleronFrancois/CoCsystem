<?php
session_start();
require "/dbconn.php"; 

/*if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

if ($role !== 'supervisor') {
    header("Location: index.php");
    exit;
}
    
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['newUsername'])
    $password = $_POST['newPassword']
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT)

    $stmt = $conn->prepare("INSERT INTO User (username,password,role) VALUES (?,?,'investigator')");
    if($stmt->execute([$username,$hashedPassword])){
        echo "User Added succesfully"
    }
    else{
        echo "User not added"
    }
}
*/
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



