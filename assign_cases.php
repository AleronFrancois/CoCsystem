<!--    
             === assign_cases.php ===
    Author.: Aleron Francois (691807)
    Date...: 10/29/2025 - x/x/2025
    Info...: Allows a supervisor to assign investigators to a specific case
-->


<?php
session_start();
require "includes/dbconn.php";

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION["id"];

// Check if user is supervisor
$stmt = $conn->prepare("
    SELECT role FROM User WHERE id = ?
");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

// Handle non-supervisor attempted access
if ($role !== 'supervisor') {
    header("Location: cases.php");
    exit;
}

// Handle form submission for assigning an investigator
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId         = $_POST['case_id'] ?? '';
    $investigatorId = $_POST['investigator_id'] ?? '';

    if ($caseId && $investigatorId) {
        try {
            // Add the assignment into the user case table
            $stmt = $conn->prepare("
                INSERT INTO Case_User (case_id, user_id) VALUES (?, ?)
            ");
            $stmt->execute([$caseId, $investigatorId]);
            $_SESSION['success'] = "Investigator successfully assigned";
            header("Location: assign_case.php");
            exit;
        } 
        catch (PDOException $error) {
            // Handle database errors
            if ($error->getCode() === '23000') {
                $_SESSION['error'] = "This investigator is already assigned to this case";
            } 
            else {
                $_SESSION['error'] = "Error: " . $error->getMessage();
            }
            header("Location: assign_case.php");
            exit;
        }
    } 
    else {
        $_SESSION['error'] = "Please select an investigator to assign to a case";
        header("Location: assign_case.php");
        exit;
    }
}

// Get cases
$cases = $conn->query("
    SELECT id, name FROM `Case` ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get investigators
$investigators = $conn->query("
    SELECT id, username FROM User WHERE role='investigator'
")->fetchAll(PDO::FETCH_ASSOC);

// Get current assignments
$assignmentsRaw = $conn->query("
    SELECT CU.case_id, U.username 
    FROM Case_User CU JOIN User U ON CU.user_id = U.id
")->fetchAll(PDO::FETCH_ASSOC);

// Index assignments into array
$assignments = [];
foreach ($assignmentsRaw as $assignment) {
    $assignments[$assignment['case_id']][] = $assignment['username'];
}

// Get and clear session messages
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Assign Investigator</title>
</head>
<body>
    <h1>Assign Investigator to Case</h1>

    <?php if ($successMessage): ?>
        <p style="color: green;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <p style="color: red;"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Case:</label>
        <select name="case_id" required>
            <option value="">--Select Case--</option>
            <?php foreach ($cases as $case): ?>
                <option value="<?= $case['id'] ?>">
                    <?= htmlspecialchars($case['id'] . " - " . $case['name']) ?>
                    <?php if (!empty($assignments[$case['id']])): ?>
                        (Assigned: <?= implode(", ", $assignments[$case['id']]) ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Investigator:</label>
        <select name="investigator_id" required>
            <option value="">--Select Investigator--</option>
            <?php foreach ($investigators as $inv): ?>
                <option value="<?= $inv['id'] ?>">
                    <?= htmlspecialchars($inv['id'] . " - " . $inv['username']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <button type="submit">Assign</button>
    </form>
    <br>
    <a href="cases.php">Back to Cases</a>
</body>
</html>