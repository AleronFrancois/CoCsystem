<!--    
             === cases.php ===
    Author.: Aleron Francois (691807) & ...
    Date...: 1/10/2025 - x/x/2025
    Info...: Gets cases from database and displays all cases
             in list. Also displays case info and redirects to 
             the cases evidence page.
-->


<html>
<head>
    <meta charset="UTF-8">
    <title>Cases</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
    <script src="scripts/scripts.js" defer></script>
</head>


<?php
session_start(); // Start session
require "includes/dbconn.php";

$caseName;
$caseDescription;

// Checks if the user is logged in 
if (!isset($_SESSION["id"])) {
    // Redirect to login.php if not logged on
    header("Location: login.php");
    exit;
}

// Handle new case
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only supervisors can create cases
    if ($_SESSION['role'] !== 'supervisor') {
        echo 'You do not have permission to create a new case!';
        exit;
    }

    // Ensure that necessary field is present
    if (!isset($_POST['caseName']) || trim($_POST['caseName']) === '') {
        echo 'You must enter a name for the case!';
        exit;
    }

    $caseName = htmlspecialchars(trim($_POST['caseName']), ENT_QUOTES, 'UTF-8');
    $caseDescription = $_POST['caseDescription'] ?? null;

    if ($caseDescription !== null) {
        $caseDescription = htmlspecialchars(trim($caseDescription));
    }

    try {
        $conn->beginTransaction();
        
        // Insert case
        $stmt = $conn->prepare("INSERT INTO `Case` (`name`, `description`, `creator_id`) VALUES (?, ?, ?)");
        $stmt->execute([$caseName, $caseDescription, $_SESSION['id']]);
        $caseId = $conn->lastInsertId();

        // Insert case CoC records
        $stmt = $conn->prepare('INSERT INTO `CaseCustodyAction` (`action`, `user_id`, `case_id`) VALUES ("create", ?, ?)');
        $stmt->execute([$_SESSION['id'], $caseId]);
        $stmt = $conn->prepare('INSERT INTO `CaseCustodyAction` (`action`, `user_id`, `case_id`) VALUES ("assign_user", ?, ?)');
        $stmt->execute([$_SESSION['id'], $caseId]);

        // Add creator to case
        $stmt = $conn->prepare('INSERT INTO `Case_User` (`case_id`, `user_id`) VALUES (?, ?)');
        $stmt->execute([$caseId, $_SESSION['id']]);

        $conn->commit();

        header('Location: cases.php');
        exit;
    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }
} else {
    $stmt = $conn->prepare('
        SELECT 
            `Case`.id,
            `Case`.name,
            `Case`.creation_date,
            `Case`.description,
            `Case`.creator_id
        FROM `Case`
        ORDER BY `Case`.id DESC
    ');
    $stmt->execute();
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>

<body class="background custom-body">
    <!-- Navigation menu -->
    <?php include "components/navbar.php"; ?>
    
    <!-- Cases and Info Box -->
    <div class="d-flex flex-grow-1 p-4 overflow-hidden">
        <div class="col-8 d-flex flex-column me-2">
            <div class="p-3 border foreground shadow rounded-5 d-flex flex-column h-100">
                <div class="justify-content-between d-flex">
                    <h2>Cases</h2>
                    <img role="button" src="images/add_icon.svg" data-bs-toggle="modal" data-bs-target="#addCaseModal" hover="pointer">
                </div>
                <hr>
                <!-- List of Cases -->
                <ul class="list-group flex-grow-1 overflow-auto mb-0">
                    <?php
                    foreach ($cases as $case) {
                        echo '
                        <li 
                            class="list-group-item d-flex align-items-center" 
                            data-id="' . $case['id'] . '"
                            data-name="' . $case['name'] . '"
                            data-description="' . ($case['description'] ?? 'null') . '"
                            data-creationDate="' . $case['creation_date'] . '"
                            onclick="viewCaseDetails(event)"
                        >
                            <img src="images/evidence_folder.png" alt="Evidence Folder" class="me-2" style="width: 24px; height: 24px;">
                            <span>' . $case['name'] . '</span>
                            <small class="text-muted ms-auto">' . $case['creation_date'] . '</small>
                        </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="col-4 d-flex flex-column">
            <div class="p-3 border foreground shadow rounded-5 h-100">
                <!-- Case info -->
                <h2>Case Info</h2>
                <div id="caseInfoPanel" class="d-flex flex-column ">

                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Case Modal -->
    <div class="modal fade" id="addCaseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">New Case</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Case Name -->
                        <div class="mb-3">
                            <label for="caseName" class="form-label">Case Name</label>
                            <input type="text" class="form-control" id="caseName" name="caseName" placeholder="Enter case name">
                        </div>

                        <!-- Case Description -->
                        <div class="mb-3">
                            <label for="caseDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="caseDescription" name="caseDescription" rows="5" placeholder="Enter case description"></textarea>
                        </div>

                        <!-- Create Case -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="createCaseButton">Create Case</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>