<?php
session_start();

$role = $_SESSION['role'];
$username = $_SESSION['username'];
$id = $_SESSION['id'];
$page = basename($_SERVER['PHP_SELF']);

?>
<nav class="navbar navbar-expand accent navbar-dark sticky-top">
    <div class="container-fluid ">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <a class="nav-link <?= $page == 'cases.php' ? 'active' : '' ?>" href="cases.php">Cases</a>
            <?php if($role == 'supervisor'): ?>
            <a class="nav-link <?= $page == 'evidence_approval.php' ? 'active' : '' ?>" href="evidence_approval.php">Review Evidence</a>
            <a class="nav-link <?= $page == 'user_panel.php' ? 'active' : '' ?>" href="user_panel.php">User Panel</a>
            <a class="nav-link <?= $page == 'handle_evidence.php' ? 'active' : '' ?>" href="handle_evidence.php">Assign Evidence</a>
            <?php endif; ?>
            <a class="nav-link <?= $page == 'custodylog.php' ? 'active' : '' ?>" href="custodylog.php">View Custody Logs</a>
            <a class="nav-link <?= $page == 'info.html' ? 'active' : '' ?>" href="info.html">Info</a>
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

