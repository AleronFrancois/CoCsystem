<html>
<head>
    <meta charset="UTF-8">
    <title>Artefacts</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
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
<script defer>
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
</script>

<!-- Navigation menu -->
<body class="background d-flex flex-column vh-100">
    <nav class="navbar navbar-expand-sm accent ">
        <div class="container-fluid ">
            <img src="images/back_icon.png" class="back-icon" role="button" id="backButton">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active">Artefacts</a>
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
                <div class="modal-header">
                    <h1 class="modal-title fs-5">New Artefact</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Artefact Name -->
                    <div class="mb-3">
                        <label for="artefactName" class="form-label">Artefact Name</label>
                        <input type="text" class="form-control" id="artefactName" placeholder="Enter artefact name">
                    </div>

                    <!-- Artefact Description -->
                    <div class="mb-3">
                        <label for="artefactDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="artefactDescription" rows="5" placeholder="Enter artefact description"></textarea>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="artefactFile" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="artefactFile">
                    </div>
                </div>

                <!-- Create artefact -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="createArtefactButton">Create Artefact</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>