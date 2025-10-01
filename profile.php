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

<body class="background d-flex flex-column vh-100">
    <nav class="navbar navbar-expand-sm accent ">
        <div class="container-fluid ">
            <img src="images/back_icon.png" class="back-icon" role="button" id="backButton">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active">Artefacts</a></li>
                <li class="nav-item"><a class="nav-link">Review Evidence</a></li>
                <li class="nav-item"><a class="nav-link">User Panel</a></li>
                <li class="nav-item"><a class="nav-link">Info</a></li>
            </ul>
        </div>
    </nav>
</body>
</html>
