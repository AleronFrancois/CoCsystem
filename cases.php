<html>
<head>
    <meta charset="UTF-8">
    <title>Cases</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
</head>

<!--         Handles Adding Case
    Author.: Aleron Francois (691807) & ...
    Date...: 1/10/2025 - x/x/2025
    NOTE...: Does not yet create a case in the database (Only creates case client-side)
    TODO...: 1. Connect to database and upload/store new case.
                2. Confirm case creation.
                3. Log successful case creation.
                4. When loading artefacts.php, parse case name to load specific case the user clicked on
-->
<script defer>
    document.addEventListener("DOMContentLoaded", () => {
        // Profile icon loads profile page
        const profileButton = document.getElementById("profileButton");
        profileButton.addEventListener("click", () => {
            window.location.href = "profile.php";
        });

        // Gets the reference to the necessary elements 
        const createButton = document.getElementById("createCaseButton");
        const caseNameInput = document.getElementById("caseName");
        const caseDescriptionInput = document.getElementById("caseDescription");
        const caseList = document.querySelector("ol.list-group");

        // Handles the case creation on button click
        createButton.addEventListener("click", () => {
            const caseName = caseNameInput.value.trim();

            // Checks if case name is not empty and creates the case 
            if (caseName !== "") {
                const list = document.createElement("li");
                list.className = "list-group-item d-flex align-items-center";

                // Icon details
                const img = document.createElement("img");
                img.src = "images/evidence_folder.png"; 
                img.alt = "Evidence Folder";
                img.className = "me-2";
                img.style.width = "24px";
                img.style.height = "24px";

                // Adds a case icon, name and datetime to the case list
                const span = document.createElement("span");
                span.textContent = caseName;
                const timeSpan = document.createElement("small");
                timeSpan.textContent = new Date().toLocaleString();
                timeSpan.className = "text-muted ms-auto";
                list.appendChild(img);
                list.appendChild(span);
                list.appendChild(timeSpan)
                caseList.appendChild(list);
                
                // Store case description and display case info on click
                list.dataset.description = caseDescriptionInput.value.trim();
                list.addEventListener("click", () => {
                    const infoPanel = document.querySelector(".col-4 div div");
                    infoPanel.innerHTML = `
                        <p><strong>Name:</strong> ${caseName}</p>
                        <p><strong>Description:</strong> ${list.dataset.description}</p>
                        <p><strong>Created:</strong> ${timeSpan.textContent}</p>
                    `;
                });

                // Open case and load evidence page
                list.addEventListener("dblclick", () => {
                    const caseName = caseNameInput.value.trim();
                    window.location.href = 'artefacts.php';
                });

                // Clear text fields and close popup
                caseNameInput.value = "";
                caseDescriptionInput.value = "";
                const modal = bootstrap.Modal.getInstance(document.getElementById("addCaseModal"));
                modal.hide();
            }
        });
    });
</script>

<body class="background custom-body">
    <!-- Navigation menu -->
    <nav class="navbar custom-navbar accent">
        <div class="custom-container">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active">Cases</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">Review Evidence</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="userpanel.php">User Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link">Info</a>
                </li>
            </ul>
            <img src="images/account_icon.svg" role="button" id="profileButton">
        </div>
    </nav>
    
    <!-- Cases and Info Box -->
    <div class="d-flex flex-grow-1 p-4 overflow-hidden">
        <div class="col-8 d-flex flex-column me-2">
            <div class="p-3 border foreground shadow rounded-5 d-flex flex-column h-100">
                <div class="justify-content-between d-flex">
                    <h2>Cases</h2>
                    <img role="button" src="images/add_icon.svg" data-bs-toggle="modal" data-bs-target="#addCaseModal" hover="pointer">
                </div>
                <hr>
                <!-- List of Cases -->
                <ol class="list-group flex-grow-1 overflow-auto mb-0"></ol>
            </div>
        </div>
        <div class="col-4 d-flex flex-column">
            <div class="p-3 border foreground shadow rounded-5 h-100 overflow-auto">
                <!-- Case info -->
                <h2>Case Info</h2>
                <div>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Case Modal -->
    <div class="modal fade" id="addCaseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">New Case</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Case Name -->
                    <div class="mb-3">
                        <label for="caseName" class="form-label">Case Name</label>
                        <input type="text" class="form-control" id="caseName" placeholder="Enter case name">
                    </div>

                    <!-- Case Description -->
                    <div class="mb-3">
                        <label for="caseDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="caseDescription" rows="5" placeholder="Enter case description"></textarea>
                    </div>

                    <!-- Create Case -->
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="createCaseButton">Create Case</button>
                </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>