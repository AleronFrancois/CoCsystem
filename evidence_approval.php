<?php
session_start();
require "includes/dbconn.php"; 

if (!isset($_SESSION["id"])) {
    require "components/access_denied.php";
    exit;
}

$userId = $_SESSION["id"];
$stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

if ($role !== 'supervisor') { // check user is a supervisor
    require "components/access_denied.php";
    exit;
}

// Get pending cases with uploader username
$stmt = $conn->prepare("
    SELECT 
        e.id,
        e.name,
        e.location,
        e.uploader_id,
        u.username AS uploader_name
    FROM evidence e
    JOIN User u ON e.uploader_id = u.id
    WHERE e.approved = 'pending'
");
$stmt->execute();
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Evidence Approval</title>
</head>
    <body>
        <?php include "components/navbar.php"?>
        <header><h1>Evidence Approval</h1></header>

        <?php if (empty($pending)): ?>
            <p>No pending evidence found.</p>
    <?php else: ?>
        <div class="pendingCasesContainer">
            <?php foreach ($pending as $case): ?>
                <div class="pendingCase">
                    <h2><?php echo htmlspecialchars($case['name']); ?></h2>
                    <p>Location: <?php echo htmlspecialchars($case['location']); ?></p>
                    <p>Uploaded by: <?php echo htmlspecialchars($case['uploader_name']); ?></p>
                    <form action="includes/process_approval.php" method="POST">
                        <input type="hidden" name="evidence_id" value="<?php echo $case['id']; ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                        <button type="submit" name="action" value="reject">Reject</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
