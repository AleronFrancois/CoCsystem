<?php
session_start();
require "../includes/dbconn.php"; 

if (!isset($_SESSION["id"]) || !isset($_POST["artefactId"]) || !isset($_POST["caseId"]) || !isset($_POST["comment"])) {
    header("Location: /index.php");
    exit;
}
$comment = htmlspecialchars($_POST["comment"]);
$userId = htmlspecialchars($_SESSION["id"]);
$artefactId = htmlspecialchars($_POST["artefactId"]);
$caseId = htmlspecialchars($_POST["caseId"]);

$stmt = $conn->prepare("SELECT * FROM `Case_User` WHERE `user_id` = ? AND `case_id` = ?");
$stmt->execute([$userId, $caseId]);
$results = $stmt->fetchAll();

if (count($results) == 0) {
    header("Location: /index.php");
    exit;
}

$stmt = $conn->prepare("INSERT INTO `Comment` (content, commenter_id, evidence_id, case_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$comment, $userId, $artefactId, $caseId]);

header("Location: /artefacts.php?caseid={$caseId}&artefactid={$artefactId}&panel=comments");

?>