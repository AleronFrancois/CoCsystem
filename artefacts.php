<?php

require "includes/blob_uploader.php";
require "includes/metadata_extractor.php";

session_start();

$caseId = $_GET['caseid'];
$userId = null;
$userRole = null;

$metadata = array();

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $userRole = $_SESSION['role'];

    require "includes/dbconn.php";

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
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            
            $stmt = $conn->prepare("INSERT INTO `Evidence` (`name`, `location`, `uploader_id`) VALUES (?, ?, ?)");
            $stmt->execute([$evidenceName, $filePath, $userId]);
            $evidenceID = $conn->lastInsertId();

            // Insert metadata into the database
            $stmt = $conn->prepare('INSERT INTO `Metadata` (`evidence_id`, `key`, `value`) VALUES (?, ?, ?)');
            
            foreach ($metadata as $key => $value) {
                $stmt->execute([$evidenceID, $key, $value]);
            }

            $conn->commit();

            // Generate hash

            $fileHash = hash_file('sha256', $_FILES['artefactFile']['tmp_name']);

            // Insert custody log 
            $sql = "INSERT INTO `EvidenceCustodyAction` (`action`, `description`, `evidence_hash`, `user_id`, `evidence_id`) VALUES ('upload', 'Upload evidence file', '$fileHash', '$userId', '$evidenceID')";
            $conn->exec($sql);

            header("Location: artefacts.php?caseid=$caseId");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Failed: " . $e->getMessage();
            exit;
        }
    }
} else {
    header("Location: index.php");
    exit;
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

<!--         Handles Uploading Evidence Artefact
    Author.: Aleron Francois (691807) & ...
    Date...: 1/10/2025 - x/x/2025
    NOTE...: Does not yet upload evidence artefacts to the database (Only creates artefacts client-side)
    TODO...: 1. Connect to database and upload/store evidence artefacts.
             2. Confirm artefact creation.
             3. Log successful artefact upload.
             4. Load case name instead of [CASE].
-->
<!-- <script defer>
    document.addEventListener("DOMContentLoaded", () => {
        // Back button loads the previous page
        const backButton = document.getElementById("backButton");
        backButton.addEventListener("click", () => {
            history.back();
        });

        // Profile icon loads profile page
        const profileButton = document.getElementById("profileButton");
        profileButton.addEventListener("click", () => {
            window.location.href = "profile.php";
        });

        // Gets the reference to the necessary elements 
        const createButton = document.getElementById("createArtefactButton");
        const artefactNameInput = document.getElementById("artefactName");
        const artefactDescriptionInput = document.getElementById("artefactDescription");
        const artefactList = document.querySelector("ol.list-group");

        // Handles the artefact creation on button click
        createButton.addEventListener("click", () => {
            const artefactName = artefactNameInput.value.trim();

            // Checks if artefact name is not empty and creates the artefact 
            if (artefactName !== "") {
                const list = document.createElement("li");
                list.className = "list-group-item d-flex align-items-center";

                // Icon details
                const img = document.createElement("img");
                img.src = "images/evidence.png"; 
                img.alt = "Evidence Artefact";
                img.className = "me-2";
                img.style.width = "24px";
                img.style.height = "24px";

                // Adds a artefact icon, name and datetime to the evidence list
                const span = document.createElement("span");
                span.textContent = artefactName;
                const timeSpan = document.createElement("small");
                timeSpan.textContent = new Date().toLocaleString();
                timeSpan.className = "text-muted ms-auto";
                list.appendChild(img);
                list.appendChild(span);
                list.appendChild(timeSpan)
                list.dataset.description = artefactDescriptionInput.value.trim();
                artefactList.appendChild(list);

                // Store artefact description and display artefact info on click
                list.dataset.description = artefactDescriptionInput.value.trim();
                list.addEventListener("click", () => {
                    const infoPanel = document.querySelector(".col-4 div div");
                    infoPanel.innerHTML = `
                        <p><strong>Name:</strong> ${artefactName}</p>
                        <p><strong>Description:</strong> ${list.dataset.description}</p>
                        <p><strong>Created:</strong> ${timeSpan.textContent}</p>
                    `;
                });

                // Clear text fields and close popup
                artefactNameInput.value = "";
                artefactDescriptionInput.value = "";
                const modal = bootstrap.Modal.getInstance(document.getElementById("addArtefactModal"));
                modal.hide();
            }
        });
    });
</script> -->

<body class="background custom-body">
    <!-- Navigation menu -->
    <nav class="navbar custom-navbar accent">
        <div class="custom-container">
            <img src="images/back_icon.png" class="back-icon" role="button" id="backButton">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active">Cases</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">Review Evidence</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">User Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">Info</a>
                </li>
            </ul>
            <img src="images/account_icon.svg" role="button" id="profileButton">
        </div>
    </nav>

    
    <!-- Artefacts and Info Box -->
    <div class="d-flex flex-grow-1 p-4 overflow-hidden">
        <div class="col-8 d-flex flex-column me-2">
            <div class="p-3 border foreground shadow rounded-5 d-flex flex-column h-100">
                <div class="justify-content-between d-flex">
                    <h2>[CASE]</h2>
                    <img src="images/add_icon.svg" role="button" data-bs-toggle="modal" data-bs-target="#addArtefactModal">
                </div>
                <hr>
                <!-- list of Artefacts -->
                <ol class="list-group flex-grow-1 overflow-auto mb-0"></ol>
            </div>
        </div>
        <div class="col-4 d-flex flex-column">
            <div class="p-3 border foreground shadow rounded-5 h-100 overflow-auto">
                <!-- Artefact info -->
                <h2>Artefact Info</h2>
                <div>
                    
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