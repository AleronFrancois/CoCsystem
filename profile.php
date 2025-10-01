<?php
include 'includes/dbconn.php'; // Connects to the database
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
                    <a class="nav-link">Review Evidence</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">User Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">Info</a>
                </li>
            </ul>
            <img src="images/account_icon.svg" role="button" id="profileButton">
        </div>
    </nav>
</body>
</html>


