<?php
include 'includes/dbconn.php'; // Connects to the database
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION["id"];
$stmt = $conn->prepare("SELECT `role`, username FROM User WHERE id = ?");
$stmt->execute([$userId]);
$userinfo = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
</head>

<script defer>
    document.addEventListener("DOMContentLoaded", () => {
        const backButton = document.getElementById("backButton");
        backButton.addEventListener("click", () => {
            history.back();
        });
    });
</script>

<body class="background custom-body">
    <!-- Navigation menu -->
    <nav class="navbar custom-navbar accent">
        <div class="custom-container">
            <img src="images/back_icon.png" class="back-icon" role="button" id="backButton">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active">Cases</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="evidence_approval.php">Review Evidence</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_panel.php">User Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="info.html">Info</a>
                </li>
            </ul>
            <img src="images/account_icon.svg" role="button" id="profileButton">
        </div>
    </nav>
    <main>
        <?php echo "<h1>" . $userinfo['username'] . "</h1>" ?>
        <?php echo "<p> ID:" . $userId . " Role: " . $userinfo['role'] . " </p>" ?>
        <a value="reject" href="components/logout.php">Log Out</button>
</body>
</html>



