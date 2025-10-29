<?php
session_start();

$role = $_SESSION['role'];
$username = $_SESSION['username'];
$id = $_SESSION['id'];
?>
<nav class="navbar navbar-expand accent navbar-dark">
    <div class="container-fluid ">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <a class="nav-link" href="cases.php">Cases</a>
            <a class="nav-link" href="evidence_approval.php">Review Evidence</a>
            <a class="nav-link" href="user_panel.php">User Panel</a>
            <a class="nav-link" href="custodylog.php">View Custody Logs</a>
            <a class="nav-link" href="info.html">Info</a>
        </ul>
        <div class="dropdown">
            <a 
                href="#" 
                class="d-flex align-items-center text-decoration-none"
                id="profileMenu"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <img src="images/account_icon.svg" role="button" id="profileButton">
            </a>

            <ul class="dropdown-menu dropdown-menu-end mt-3 fs-5 p-3 w-100" aria-labelledby="profileMenu">
                <li class="fw-bold"><?= $username ?></li>
                <li><hr class="dropdown-divider"></li>
                <li>Role: <?= $role ?></li>
                <li><hr class="dropdown-divider"></li>
                <li>ID: <?= $id ?></li>
                <li><hr class="dropdown-divider"></li>
                <li><a href="/components/logout.php">Log out</a></li>
            </ul>
        </div>
        
    </div>
</nav>

<div>

</div>

