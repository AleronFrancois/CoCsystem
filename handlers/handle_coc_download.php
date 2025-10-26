<?php
session_start();
require "../includes/dbconn.php"; 

if (!isset($_SESSION["id"]) || !isset($_GET["caseId"])) {
    header("Location: /index.php");
    exit;
}

$caseId= $_GET["caseId"];

// Get case information
$stmt = $conn->prepare('
    SELECT 
        `Case`.`id`,
        `Case`.`name`,
        `Case`.`creation_date`,
        `Case`.`description`,
        `Case`.`creator_id`,
        `User`.`username`
        FROM `Case`
        JOIN `User` ON `Case`.`creator_id` = `User`.`id` 
        WHERE `Case`.`id` = ?');
$stmt->execute([$caseId]);

$caseInfo = $stmt->fetch();

// Get all case custody actions
$stmt = $conn->prepare('
    SELECT
        `CaseCustodyAction`.`id`,
        `CaseCustodyAction`.`timestamp`,
        `CaseCustodyAction`.`action`,
        `CaseCustodyAction`.`user_id`,
        `CaseCustodyAction`.`evidence_id`,
        `User`.`username`,
        `Evidence`.`name` AS evidence_name
        FROM `CaseCustodyAction` 
        JOIN `User` ON `CaseCustodyAction`.`user_id` = `User`.`id`
        LEFT JOIN `Evidence` ON `CaseCustodyAction`.`evidence_id` = `Evidence`.`id`
        WHERE `case_id` = ? ORDER BY `timestamp` ASC
    ');
$stmt->execute([$caseId]);
$caseActions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$evidenceTimePeriods = array();

// Get times when the pieces of evidence were assigned to the case. CoC should show all actions that happened while the evidence was assigned
foreach ($caseActions as $caseAction) {
    if ($caseAction['evidence_id'] !== null) {
        $currentEvidenceId = $caseAction['evidence_id'];

        // Create evidence record in array
        if (!isset($evidenceTimePeriods[$currentEvidenceId])) {
            $evidenceTimePeriods[$currentEvidenceId] = array();
        }

        if ($caseAction['action'] == 'assign_evidence') {
            array_push($evidenceTimePeriods[$currentEvidenceId], ['assigned' => $caseAction['timestamp']]);
        } else if ($caseAction['action'] == 'unassign_evidence') {
            $evidenceTimePeriods[$currentEvidenceId][count($evidenceTimePeriods[$currentEvidenceId]) - 1]['unassigned'] = $caseAction['timestamp'];
        }
    }
}

if (!empty($evidenceTimePeriods)) {
    $stmt = $conn->prepare('
    SELECT 
        `EvidenceCustodyAction`.`id`, 
        `EvidenceCustodyAction`.`timestamp`, 
        `EvidenceCustodyAction`.`action`, 
        `EvidenceCustodyAction`.`description`, 
        `EvidenceCustodyAction`.`evidence_hash`, 
        `EvidenceCustodyAction`.`user_id`, 
        `EvidenceCustodyAction`.`evidence_id`,
        `User`.`username`,
        `Evidence`.`name` AS evidence_name
    FROM `EvidenceCustodyAction`
    JOIN `User` ON `EvidenceCustodyAction`.`user_id` = `User`.`id`
    JOIN `Evidence` ON `EvidenceCustodyAction`.`evidence_id` = `Evidence`.`id`
    WHERE `EvidenceCustodyAction`.`evidence_id` IN (' . implode(',', array_keys($evidenceTimePeriods)) . ')
    ORDER BY `EvidenceCustodyAction`.`timestamp`;');
    $stmt->execute();
    $evidenceActions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$chainOfCustody = array();

// Remove evidence actions that are not relevant to the case
foreach ($evidenceActions as $action) {
    foreach ($evidenceTimePeriods[$action['evidence_id']] as $times) {
        $assignedTime = strtotime($times['assigned']); 
        $unassignedTime = isset($times['unassigned']) ? strtotime($times['unassigned']) : PHP_INT_MAX;
        $actionTime = strtotime($action['timestamp']);

        if ($assignedTime <= $actionTime && $actionTime <= $unassignedTime) {
            array_push($chainOfCustody, $action);
        }
    }
}


array_push($chainOfCustody, ...$caseActions);

usort($chainOfCustody, function ($a, $b) {
    return strtotime($a['timestamp']) <=> strtotime($b['timestamp']);
});

// Format the CoC
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="Chain_Of_Custody_' . $caseId . '.html"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

echo '
<html>
    <head>
        <meta charset="UTF-8">
        <title>Chain of Custody Report: Case ' . $caseId . ' - ' . $caseInfo['name'] . '</title>
        <style>
            body {
                font-family: Arial;
                overflow-x: hidden
            }

            table {
                border-collapse: collapse;
                width: 100%;
                margin-top: 20px;
                background: #fff;
                overflow-x: scroll
            }

            th, td {
                border: 1px solid #ccc;
                padding: 8px 12px;
                text-align: left;
            }

            th {
                background: #efefef;
            }

            #tableContainer {
                overflow-x: scroll
            }

            tr:nth-child(even) {
                background: #f7f7f7;
            }
        </style>
    </head>
    <body>
        <h1>Chain of custody report for: ' . $caseInfo['name'] .'</h1>
        <p>Case opened: ' . date('Y-m-d g:i:s A', strtotime($caseInfo['creation_date'])) . '</p>
        <p>Case description: ' . $caseInfo['description'] . '</p>
        <p>Case creator: ' . $caseInfo['username'] . '</p>
        <div id="tableContainer">
            <table>
                <tr>
                    <th>Action target</th>
                    <th>Action type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>User</th>
                    <th>Evidence ID</th>
                    <th>Evidence Name</th>
                    <th>Evidence Hash</th>
                    <th>Hash Match?</th>
                </tr>
                ';
                $currentEvidenceHashes = array();
                foreach ($chainOfCustody as $action) {
                    $isEvidenceAction = isset($action['evidence_hash']);
                    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s.u', $action['timestamp']);
                    $hashesMatch = null;
                    if (isset($action['evidence_hash'])) {
                        if (isset($currentEvidenceHashes[$action['evidence_id']])) {
                            $hashesMatch = $currentEvidenceHashes[$action['evidence_id']] == $action['evidence_hash'];
                            $currentEvidenceHashes[$action['evidence_id']] = $action['evidence_hash'];
                        } else {
                            $hashesMatch = true;
                            $currentEvidenceHashes[$action['evidence_id']] = $action['evidence_hash'];
                        }
                    } 
                    echo '
                    <tr>
                        <td>' . ($isEvidenceAction ? 'Evidence' : 'Case') . '</td>
                        <td>' . $action['action'] . '</td>
                        <td>' . $dateTime->format('Y-m-d') . '</td>
                        <td>' . $dateTime->format('g:i:s.v A') . '</td>
                        <td>' . $action['user_id'] . ' - ' . $action['username'] . '</td>
                        <td>' . ($action['evidence_id'] ?? 'N/A') . '</td>
                        <td>' . ($action['evidence_name'] ?? 'N/A') . '</td>
                        <td>' . ($action['evidence_hash'] ?? 'N/A') . '</td>
                        <td style="background-color: ' . ($hashesMatch !== false ? '#81b85fff' : '#d35f5fff') . '">' . ($action['evidence_hash'] ?? 'N/A') . '</td>
                    </tr>
                    ';
                }
                echo '
            </table>
        </div>
    </body>
</html>
'


?>