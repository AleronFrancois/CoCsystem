<?php
/* 
          === login.php ===
Author..: Aleron Francois (691807)
Date....: 10/01/2025 - x/x/2025
Info....: This script handles the authentication of users and
          takes them to the home page (cases.php).
*/
session_start(); // Start session
include 'includes/dbconn.php'; // Use database credentials

// Checks if the form was submitted using post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Gets username from user
    $password = $_POST['password']; // Gets password from user

    // Prepares sql query and fetches user records
    $stmt = $conn->prepare("SELECT id, username, password, role FROM User WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Checks if user's credentials matches those in the database
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $user['id'];
        $_SESSION["username"] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: cases.php"); // Loads home page
        exit;
    } 
    else {
        $error = "Invalid username or password"; // Displays error message if credentials are invalid
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chain of Custody Tracker - Login</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js"></script>
</head>
<body class="background">
    <div class="login-container">
        <h1>Log In</h1>
        <?php if(!empty($error)) echo "<div class='text-danger mb-3'>$error</div>"; ?>
        <form method="post" class="d-grid gap-3">
            <input type="text" class="form-control" placeholder="Username" name="username" required>
            <input type="password" class="form-control" placeholder="Password" name="password" required>
            <button type="submit" class="btn unselected">Log In</button>
        </form> 
    </div>
</body>
</html>
