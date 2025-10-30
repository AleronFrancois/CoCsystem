<?php
session_start();
require "../includes/dbconn.php"; 
require '../vendor/autoload.php';
require '../includes/evidence_hash_retriever.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

// Check for required params
if (!isset($_SESSION["id"]) || !isset($_POST["artefactId"]) || !isset($_POST["caseId"]) || !isset($_POST["comment"])) {
    header("Location: /index.php");
    exit;
}

$comment = htmlspecialchars($_POST["comment"]);
$userId = htmlspecialchars($_SESSION["id"]);
$artefactId = htmlspecialchars($_POST["artefactId"]);
$caseId = htmlspecialchars($_POST["caseId"]);

// See if user has permission to comment on evidence
$stmt = $conn->prepare("SELECT * FROM `Case_User` WHERE `user_id` = ? AND `case_id` = ?");
$stmt->execute([$userId, $caseId]);
$results = $stmt->fetchAll();

if (count($results) == 0) {
    header("Location: /index.php");
    exit;
}

// Calculate file hash
$fileHash = getEvidenceHash($artefactId);

// Insert comment
$stmt = $conn->prepare("INSERT INTO `Comment` (content, commenter_id, evidence_id, case_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$comment, $userId, $artefactId, $caseId]);

// Add CoC record
$stmt = $conn->prepare("
    INSERT INTO `EvidenceCustodyAction` (`action`, `description`, `evidence_hash`, `user_id`, `evidence_id`) 
    VALUES ('comment', 'User {$userId} left a comment on this piece of evidence, under case {$caseId}.', ?, ?, ?)");
$stmt->execute([$fileHash, $userId, $artefactId]);


header("Location: /artefacts.php?caseid={$caseId}&artefactid={$artefactId}&panel=comments");
?>