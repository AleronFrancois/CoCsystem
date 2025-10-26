<?php
session_start();
require "../includes/dbconn.php"; 

if (!isset($_SESSION["id"]) || !isset($_POST["caseId"])) {
    header("Location: /index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM `Case_User` WHERE `user_id` = ? AND `case_id` = ?");
$stmt->execute([$userId, $caseId]);
$results = $stmt->fetchAll();

if (count($results) == 0) {
    header("Location: /index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM `EvidenceCustodyAction` WHERE `case_id` = ?");
$stmt->execute([$userId, $caseId]);
$results = $stmt->fetchAll();

?>