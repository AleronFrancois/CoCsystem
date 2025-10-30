<?php
require '../includes/dbconn.php';
require '../vendor/autoload.php';
require '../includes/evidence_hash_retriever.php';

session_start();

if (!isset($_SESSION['id']) || !isset($_GET['artefactid']) || !isset($_GET['caseid'])) {
    header('Location: /index.php');
    exit;
}

$userId = $_SESSION['id'];
$evidenceId = $_GET['artefactid'];
$caseId = $_GET['caseid'];

try {
    $stmt = $conn->prepare('
        SELECT `Evidence`.location,
        (
            SELECT `EvidenceCustodyAction`.evidence_hash
            FROM EvidenceCustodyAction
            WHERE `EvidenceCustodyAction`.evidence_id = `Evidence`.id
            ORDER BY `EvidenceCustodyAction`.timestamp DESC
            LIMIT 1
        ) AS latest_hash
        FROM Evidence
        JOIN `Case_Evidence` ON `Case_Evidence`.evidence_id = `Evidence`.id
        JOIN `Case_User` ON `Case_User`.case_id = `Case_Evidence`.case_id
        WHERE `Evidence`.id = ? AND `Case_User`.user_id = ?
    ');

    $stmt->execute([$evidenceId, $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        include '../components/access_denied.php';
        exit;
    }

    $location = $result['location'];
    $latestHash = $result['latest_hash'];

    $fileHash = getEvidenceHash($artefactId, $location);


    $stmt = $conn->prepare("INSERT INTO `EvidenceCustodyAction` (`action`, `description`, `evidence_hash`, `user_id`, `evidence_id`) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['rehash', 'User ' . $userId . ' rehashed this piece of evidence', $fileHash, $userId, $evidenceId]);

    $hashesMatch = $fileHash === $latestHash;

} catch (Exception $e) {
    header('Location: /index.php');
    exit;
}

?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chain of Custody Tracker - Rehash Evidence</title>
    <link rel="stylesheet" href="../styles/bootstrap.min.css">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="../scripts/bootstrap.bundle.min.js"></script>
    <script src="../scripts/scripts.js" defer></script>
</head>
<body class="background" onload="redirectAfterDelay()">
    <div class="login-container">
        <h1><?= $hashesMatch ? 'Hashes Match' : 'Hash Mismatch'?></h1>
        <p>
            <?= $hashesMatch ? 
            'The artefact hash has not changed since the last custody action. For a more comprehensive view of whether or not
            the artefact\'s hash has ever changed, download a chain of custody report.' : 
            'The artefact hash has changed since the last custody action. Please download a chain of custody report for more information.'
            ?>
        </p>
        <p>The evidence has been rehashed successfully. This rehash has been added to the chain of custody.</p>
        <a href="/artefacts.php?caseid=<?= $caseId ?>"><button class="btn btn-primary">Go back to case</button></a>
    </div>
</body>
</html>