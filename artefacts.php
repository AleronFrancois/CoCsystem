<?php

require "includes/blob_uploader.php";
require "includes/metadata_extractor.php";

session_start();

$caseId = $_GET['caseid'];
$userId = null;
$userRole = null;
$comment = false;
$artefacts = array();

$metadata = array();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['id'];
$userRole = $_SESSION['role'];

require "includes/dbconn.php";

// See if user has access to case
try {
    $stmt = $conn->prepare("SELECT * FROM `Case_User` WHERE `user_id` = ? AND `case_id` = ?");
    $stmt->execute([$userId, $caseId]);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error! ". $e->getMessage();
    exit;
}
if (count($results) == 0) {
    include "components/access_denied.php";
    exit;
}

// Handle evidence upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle errors
    if (!isset($_FILES['artefactFile']) || $_FILES['artefactFile']['error'] !== UPLOAD_ERR_OK) {
        echo $_FILES['artefactFile']['error'];
        echo "Error! File upload failed";
        exit;
    } else if ($_POST['artefactName'] == null || trim($_POST['artefactName']) == "") {
        echo "Error! Artefact name is required";
        exit;
    }

    // Upload file to BLOB storage
    $filePath = upload_file_to_blob($_FILES['artefactFile']);
    if ($filePath == "ERROR") {
        echo "Error! File upload to BLOB failed";
        exit;
    }

    try {

        $conn->beginTransaction();

        // Extract metadata from file
        $metadata = extract_metadata_values($_FILES['artefactFile']);

        // Insert evidence into database
        $evidenceName = $_POST['artefactName'];
        
        if ($userRole == 'supervisor') {
            $stmt = $conn->prepare("INSERT INTO `Evidence` (`name`, `location`, `uploader_id`, `approved`) VALUES (?, ?, ?, 'approved')");
            $stmt->execute([$evidenceName, $filePath, $userId]);
        } else {
            $stmt = $conn->prepare("INSERT INTO `Evidence` (`name`, `location`, `uploader_id`) VALUES (?, ?, ?)");
            $stmt->execute([$evidenceName, $filePath, $userId]);
        }
        
        $evidenceId = $conn->lastInsertId();

        // Insert metadata into the database
        $stmt = $conn->prepare('INSERT INTO `Metadata` (`evidence_id`, `key`, `value`) VALUES (?, ?, ?)');
        
        // Insert manually provided metadata
        $manualMetadataFields = ['artefactCreationTime', 'artefactModificationTime', 'artefactAccessTime'];
        foreach ($manualMetadataFields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] != null && trim($_POST[$field]) != "") {
                $metadata[$field] = $_POST[$field];
            }
        }

        foreach ($metadata as $key => $value) {
            $stmt->execute([$evidenceId, $key, $value]);
        }

        // Insert comment if the user provided one
        if (isset($_POST['artefactComment']) && trim($_POST['artefactComment']) != "") {
            $comment = true;
            $stmt = $conn->prepare('INSERT INTO `Comment` (`content`, `commenter_id`, `case_id`, `evidence_id`) VALUES (?, ?, ?, ?)');
            $stmt->execute([trim($_POST['artefactComment']), $userId, $caseId, $evidenceId]);
        }

        // Link evidence to case
        $stmt = $conn->prepare("INSERT INTO `Case_Evidence` (`case_id`, `evidence_id`) VALUES (?, ?)");
        $stmt->execute([$caseId, $evidenceId]);

        // Add case CoC record
        $stmt = $conn->prepare('INSERT INTO `CaseCustodyAction` (`action`, `user_id`, `case_id`, `evidence_id`) VALUES ("assign_evidence", ?, ?, ?)');
        $stmt->execute([$_SESSION['id'], $caseId, $evidenceId]);

        // Generate hash

        $fileHash = hash_file('sha256', $_FILES['artefactFile']['tmp_name']);

        // Insert custody logs
        $stmt = $conn->prepare("INSERT INTO `EvidenceCustodyAction` (`action`, `description`, `evidence_hash`, `user_id`, `evidence_id`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['upload', 'Upload evidence file', $fileHash, $userId, $evidenceId]);
        if ($comment) {
            $stmt->execute(['comment', "User {$userId} left a comment on this piece of evidence, under case {$caseId}.", $fileHash, $userId, $evidenceId]);
        }
        
        $conn->commit();

        header("Location: artefacts.php?caseid=$caseId");
        exit;
    } catch (Exception $e) {
        echo "Failed: " . $e->getMessage();
        exit;
    }
} else {
    $stmt = $conn->prepare('
        SELECT 
            `Evidence`.id,
            `Evidence`.name,
            `Evidence`.location,
            `Evidence`.locked,
            `Evidence`.uploader_id,
            `Case`.name AS case_name,
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    "key", `Metadata`.key,
                    "value", `Metadata`.value
                )
            ) AS metadata,
            COALESCE((
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        "id", `Comment`.id,
                        "timestamp", `Comment`.timestamp,
                        "content", `Comment`.content,
                        "commenter_id", `Comment`.commenter_id,
                        "commenter_name", `User`.username
                    )
                )
                FROM `Comment`
                JOIN `User` ON `User`.id = `Comment`.commenter_id
                WHERE `Comment`.evidence_id = `Evidence`.id
                AND `Comment`.case_id = ?
            ), JSON_ARRAY()) AS comments
        FROM `Evidence` 
        LEFT JOIN `Case_Evidence` ON `Evidence`.`id` = `Case_Evidence`.`evidence_id` 
        LEFT JOIN `Metadata` ON `Evidence`.`id` = `Metadata`.`evidence_id`
        JOIN `Case` ON `Case`.`id` = `Case_Evidence`.`case_id`
        WHERE `Case_Evidence`.`case_id` = ? AND
        `Evidence`.`approved` = "approved"
        GROUP BY `Evidence`.id
        ORDER BY `Evidence`.id ASC' 
    );
    $stmt->execute([$caseId, $caseId]);
    $artefacts = $stmt->fetchAll(PDO::FETCH_ASSOC);


    foreach ($artefacts as &$artefact) {
        if ($artefact['metadata'] != null) {
            $artefact['metadata'] = json_decode($artefact['metadata'], true);
        } else {
            $artefact['metadata'] = array();
        }

        if ($artefact['comments'] != null) {
            $artefact['comments'] = json_decode($artefact['comments'], true);
        } else {
            $artefact['comments'] = array();
        }
    }
    unset($artefact);

}


?>


<html>
<head>
    <meta charset="UTF-8">
    <title>Artefacts</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
    <script src="scripts/scripts.js" defer></script>
</head>


<body class="background custom-body">
    <!-- Navigation menu -->
    <nav>
        <?php include "components/navbar.php"; ?>
    </nav>

    
    <!-- Artefacts and Info Box -->
    <div class="d-flex flex-grow-1 p-4 overflow-hidden">
        <div class="col-8 d-flex flex-column me-2">
            <div class="p-3 border foreground shadow rounded-5 d-flex flex-column h-100">
                <div class="justify-content-between d-flex">
                    <h2><?= $artefacts[0]['case_name'] ?></h2>
                    <?php if ($userRole === "supervisor"): ?>
                        <a class="button" href="case_permissions.php?case_id=<?php echo urlencode($caseId); ?>">
                        Manage Case Permissions
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-sm py-0 btn-primary" onclick="handleDownload('/handlers/handle_coc_download.php?fileFormat=pdf&caseId=<?= $caseId ?>')">
                        Download PDF CoC
                    </button>
                    <button class="btn btn-sm py-0 btn-primary" onclick="handleDownload('/handlers/handle_coc_download.php?fileFormat=html&caseId=<?= $caseId ?>')">
                        Download HTML CoC
                    </button>
                    <img src="images/add_icon.svg" role="button" data-bs-toggle="modal" data-bs-target="#addArtefactModal">
                </div>
                <hr>
                <!-- list of Artefacts -->
                <ol class="list-group flex-grow-1 overflow-auto mb-0">
                    <?php
                    # Add artefacts
                    foreach ($artefacts as $artefact) {
                        echo '
                            <li class="
                                list-group-item
                                d-flex
                                align-items-center
                                justify-content-between
                            " 
                            id="artefact_' . htmlspecialchars($artefact['id']) . '"
                            onclick="handleArtefactClick(event)"
                            data-metadata="' . htmlspecialchars(json_encode($artefact['metadata']), ENT_QUOTES, 'UTF-8') . '"
                            data-comments="' . htmlspecialchars(json_encode($artefact['comments']), ENT_QUOTES, 'UTF-8') . '"
                            data-name="' . htmlspecialchars($artefact['name']) . '"
                            >
                                <div>
                                    <img src="images/evidence.png" alt="Evidence Artefact" class="me-2" style="width: 24px; height: 24px;">
                                    <span>' . htmlspecialchars($artefact['name']) . '</span>
                                </div>
                                <div id="buttonGroupArtefact_' . $artefact['id'] . '" hidden>
                                    <button class="btn btn-sm py-0" onclick="handleDownload(\'/handlers/handle_artefact_download.php?artefactid=' . $artefact['id'] . '\')">
                                        <img src="images/download_icon.svg" alt="Download Artefact" style="width: 20px; height: 20px;">
                                    </button>
                                    <button class="btn btn-sm p-0" onclick="window.location.href=\'/handlers/handle_rehash.php?artefactid=' . $artefact['id'] . '&caseid=' . $caseId . '\'">
                                        <img src="images/rehash_icon.svg" alt="Rehash Artefact" style="width: 20px; height: 20px;">
                                    </button>
                                </div>
                            </li>';
                    }
                    ?>

                </ol>
                
            </div>
        </div>
        <div class="col-4 d-flex flex-column">
            <div class="p-3 border foreground shadow rounded-5 d-flex flex-column h-100">
                <div id="artefactInfoPanel" class="d-flex flex-column h-100 d-none">
                    <div>
                        <h2 id="evidenceName">EVIDENCE NAME</h2>
                    </div>
                    <hr>
                    <div>
                        <h3 id="artefactInfoPanelTitle">Details and Metadata</h3>
                    </div>
                    <div class="flex-grow-1 overflow-auto my-2">
                        <div class="list-group" id="artefactInfoList">
                            <form method="POST" enctype="multipart/form-data" action="/handlers/handle_comment.php">
                                <div class="mb-3">
                                    <label for="Comment" class="form-label">Post a Comment</label>
                                    <textarea rows="3" type="text" class="form-control" id="Comment" name="Comment" placeholder="Type your comment then press 'Enter' to post"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="mt-auto pt-3 border-top d-flex gap-2">
                        <button class="btn btn-secondary w-50" id="viewCommentsButton" onclick="viewComments()">
                            Comments
                        </button>
                        <button class="btn btn-secondary w-50" id="viewLogButton" onclick="viewLog('evidence',artefactId)">
                            View Custody Log
                        </button>
                        <button class="btn btn-primary w-50" id="viewDetailsButton" onclick="viewEvidenceDetails()">
                            Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Artefact Modal -->
    <div class="modal fade" id="addArtefactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">New Artefact</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        
                            <!-- Artefact Name -->
                            <div class="mb-3">
                                <label for="artefactName" class="form-label">Artefact Name</label>
                                <input type="text" class="form-control" id="artefactName" placeholder="Enter artefact name" name="artefactName" required>
                            </div>

                            <!-- File Upload -->
                            <div class="mb-3">
                                <label for="artefactFile" class="form-label">Upload File</label>
                                <input type="file" class="form-control" id="artefactFile" name="artefactFile" required>
                            </div>

                            <!-- Artefact Creation time -->
                            <div class="mb-3">
                                <label for="artefactCreationTime" class="form-label">Artefact Creation Time</label>
                                <input type="datetime-local" class="form-control" id="artefactCreationTime" name="artefactCreationTime">
                            </div>

                            <!-- Artefact Last Modification time -->
                            <div class="mb-3">
                                <label for="artefactModificationTime" class="form-label">Artefact Last Modification Time</label>
                                <input type="datetime-local" class="form-control" id="artefactModificationTime" name="artefactModificationTime">
                            </div>

                            <!-- Artefact Last Access time -->
                            <div class="mb-3">
                                <label for="artefactAccessTime" class="form-label">Artefact Last Access Time</label>
                                <input type="datetime-local" class="form-control" id="artefactAccessTime" name="artefactAccessTime">
                            </div>

                            <p>Note: Due to security limitations present in most browsers and the HTTP protocol, file modification, access, and creation times cannot be accessed automatically. The above time fields are optional</p>

                            <!-- Comment -->
                            <?= $userRole == 'investigator' ? '
                            <div class="mb-3">
                                <label for="artefactComment" class="form-label">Upload File</label>
                                <textarea rows="3" type="text" class="form-control" id="artefactComment" name="artefactComment" placeholder="Enter a comment for your supervisor"></textarea>
                            </div>
                            ' : '';
                            ?>
                        
                    </div>

                    <!-- Create artefact -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="createArtefactButton">Create Artefact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>