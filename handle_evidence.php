<!--
    === assign_cases.php ===
    Author.: Aleron Francois (691807)
    Date...: 10/29/2025 x 10/x/2025
    Info...: Supervisors can assign/unassign evidence to and from cases.
-->

<?php
session_start();
require "includes/dbconn.php";

// Check for session id
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION["id"];

// Get user role
$stmt = $conn->prepare("
    SELECT role FROM User WHERE id = ?
");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

// Allow only supervisors
if ($role !== "supervisor") {
    header("Location: index.php");
    exit;
}

// Handle evidence case assignment updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $evidenceId = $_POST["evidence_id"];
    $caseIds = $_POST["case_ids"] ?? [];

    // Get current assigned cases
    $stmt = $conn->prepare("
        SELECT case_id FROM Case_Evidence WHERE evidence_id = ?
    ");
    $stmt->execute([$evidenceId]);
    $currentCases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $toAdd = array_diff($caseIds, $currentCases);
    $toRemove = array_diff($currentCases, $caseIds);

    // Assign new cases
    foreach ($toAdd as $caseId) {
        $stmt = $conn->prepare("
            INSERT IGNORE INTO Case_Evidence (case_id, evidence_id) VALUES (?, ?)
        ");
        $stmt->execute([$caseId, $evidenceId]);

        $stmt = $conn->prepare("
            INSERT INTO CaseCustodyAction (action, user_id, case_id, evidence_id)
            VALUES ('assign_evidence', ?, ?, ?)
        ");
        $stmt->execute([$userId, $caseId, $evidenceId]);
    }

    // Unassign evidence
    foreach ($toRemove as $caseId) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM Case_Evidence WHERE evidence_id = ?
        ");
        $stmt->execute([$evidenceId]);
        $count = $stmt->fetchColumn();

        if ($count > 1) {
            $stmt = $conn->prepare("
                DELETE FROM Case_Evidence WHERE case_id = ? AND evidence_id = ?
            ");
            $stmt->execute([$caseId, $evidenceId]);

            $stmt = $conn->prepare("
                INSERT INTO CaseCustodyAction (action, user_id, case_id, evidence_id)
                VALUES ('unassign_evidence', ?, ?, ?)
            ");
            $stmt->execute([$userId, $caseId, $evidenceId]);
        }
    }

    $_SESSION["success"] = "Evidence assignments updated.";
    header("Location: assign_cases.php");
    exit;
}

// Load evidence and case data
$evidenceList = $conn->query("
    SELECT id, name, location, approved FROM Evidence ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
$cases = $conn->query("
    SELECT id, name FROM `Case` ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Evidence Transfer</title>
        <link rel="stylesheet" href="styles/bootstrap.min.css">
        <link rel="stylesheet" href="styles/styles.css">
        <script src="scripts/bootstrap.bundle.min.js" defer></script>
        <script src="scripts/scripts.js" defer></script>
    </head>
    <body>
        <?php include "components/navbar.php"; ?>
        <div class="container">
            <h2>Evidence Transfer (Supervisors Only)</h2>

            <?php if (isset($_SESSION["success"])): ?>
                <p class="success"><?= htmlspecialchars($_SESSION["success"]) ?></p>
                <?php unset($_SESSION["success"]); ?>
            <?php endif; ?>

            <?php foreach ($evidenceList as $e): ?>
                <?php
                $stmt = $conn->prepare("
                    SELECT c.id, c.name 
                    FROM Case_Evidence ce 
                    JOIN `Case` c ON ce.case_id = c.id 
                    WHERE ce.evidence_id = ?
                ");
                $stmt->execute([$e["id"]]);
                $assignedCases = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $assignedIds = array_column($assignedCases, "id");
                ?>
                <div class="evidence-block">
                    <h3><?= htmlspecialchars($e["name"]) ?> (ID: <?= $e["id"] ?>)</h3>
                    <p>Location: <?= htmlspecialchars($e["location"]) ?></p>
                    <p>Status: <?= htmlspecialchars($e["approved"]) ?></p>

                    <form method="POST">
                        <input type="hidden" name="evidence_id" value="<?= $e["id"] ?>">
                        <?php foreach ($cases as $case): ?>
                            <label>
                                <input 
                                    class="form-check-input"
                                    type="checkbox" 
                                    name="case_ids[]" 
                                    value="<?= $case["id"] ?>" 
                                    <?= in_array($case["id"], $assignedIds) ? "checked" : "" ?>>
                                <?= htmlspecialchars($case["name"]) ?>
                            </label><br>
                        <?php endforeach; ?>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
</html>
