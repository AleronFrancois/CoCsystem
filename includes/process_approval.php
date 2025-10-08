<!--Evidence Approval Handler -->
<!--Used to manage approved/rejected evidence -->
<?php
session_start();
require "dbconn.php"; 


if (!isset($_SESSION["id"])) exit;
$userId = $_SESSION["id"];

$stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

if ($role !== 'supervisor') exit;


if (empty($_POST["evidence_id"]) || empty($_POST["action"])) exit;

$evidenceId = (int) $_POST["evidence_id"];
$action = strtolower($_POST["action"]);

// ensure the enum works without crashing
$newStatus = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : null);
if (!$newStatus) exit;

$stmt = $conn->prepare("UPDATE evidence SET approved = ? WHERE id = ?");
$stmt->execute([$newStatus, $evidenceId]);

header("Location: ../evidenceApproval.php");
exit;
