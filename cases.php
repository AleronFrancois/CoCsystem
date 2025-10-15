<!--    
             === cases.php ===
    Author.: Aleron Francois (691807) & ...
    Date...: 1/10/2025 - x/x/2025
    Info...: Gets cases from database and displays all cases
             in list. Also displays case info and redirects to 
             the cases evidence page.
-->


<html>
<head>
    <meta charset="UTF-8">
    <title>Cases</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
</head>


<?php
session_start(); // Start session

// Checks if the user is logged in 
if (!isset(
    $_SESSION["loggedin"]) || 
    $_SESSION["loggedin"] !== true
    ) {

    // Redirect to login.php if not logged on
    header("Location: login.php");
    exit;
}
?>


<script defer>
document.addEventListener("DOMContentLoaded", () => {
    const profileButton = document.getElementById("profileButton");          // Profile button
    const createButton = document.getElementById("createCaseButton");        // Create new case button
    const caseNameInput = document.getElementById("caseName");               // Create new case -- input case name
    const caseDescriptionInput = document.getElementById("caseDescription"); // Create new case -- input case description
    const caseList = document.querySelector("ol.list-group");                // List of all cases

    // Profile button
    profileButton.addEventListener("click", () => {
        window.location.href = "profile.php"; // Loads user's profile page
    });

    // Load all cases from the database
    fetch("get_cases.php?action=get_cases")
        .then(res => res.text()) 
        .then(text => {
            const jsonStart = text.indexOf('[');
            if (jsonStart === -1) throw new Error('...no JSON found');
            const cleanText = text.slice(jsonStart);
            const data = JSON.parse(cleanText);
            caseList.innerHTML = "";
            data.forEach(c => addCaseToList(c.name, c.description, c.creation_date, c.id));
        })
        .catch(err => console.error("...error loading cases:", err));

    
    /*
                 addCaseToList()
        Info...: Creates and adds a new case to the list. Each case includes a
                 name, icon, creation date and description.
                 Sets up click event handlers.
                 
        Param..: name, description, createDate, id
        Return.: none
    */
    function addCaseToList(
        name, 
        description, 
        creationDate, 
        id,
        ) {
        const list = document.createElement("li");
        list.className = "list-group-item d-flex align-items-center";
        list.dataset.id = id;
        list.dataset.description = description;

        // Case icon
        const img = document.createElement("img");
        img.src = "images/evidence_folder.png";
        img.alt = "Evidence Folder";
        img.className = "me-2";
        img.style.width = "24px";
        img.style.height = "24px";

        // Name & date of case
        const span = document.createElement("span");
        span.textContent = name;
        const timeSpan = document.createElement("small");
        timeSpan.textContent = new Date(creationDate).toLocaleString();
        timeSpan.className = "text-muted ms-auto";

        // Add case attributes to list
        list.appendChild(img);
        list.appendChild(span);
        list.appendChild(timeSpan);
        caseList.appendChild(list);

        // Display case info on click
        list.addEventListener("click", () => {
            const infoPanel = document.querySelector(".col-4 div div");
            const descriptionText = description ? description : "No description provided atm (TODO: get description)"; // TODO: we gotta get the description later on
            infoPanel.innerHTML = `
                <p><strong>Name:</strong> ${name}</p>
                <p><strong>Description:</strong> ${descriptionText}</p>
                <p><strong>Created:</strong> ${new Date(creationDate).toLocaleString()}</p>
            `;
        });

        // Open case on double click
        list.addEventListener("dblclick", () => {
            window.location.href = `artefacts.php?caseid=${id}`;
        });
    }

    // Handle create a new case
    createButton.addEventListener("click", () => {
        const caseName = caseNameInput.value.trim();
        if (!caseName) return;
        const description = caseDescriptionInput.value.trim();
        const currentDate = new Date().toISOString();
        const tempId = Date.now();

        addCaseToList(
            caseName, 
            description, 
            currentDate, 
            tempId);

        // Clear modal
        caseNameInput.value = "";
        caseDescriptionInput.value = "";
        bootstrap.Modal.getInstance(document.getElementById("addCaseModal")).hide();
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
                    <a class="nav-link" href="evidence_approval.php">Review Evidence</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_panel.php">User Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="info.html">Info</a>
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