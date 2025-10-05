function redirectAfterDelay() {
    setTimeout(function() {
        window.location.href = "index.php";
    }, 3000);
}

/**
 * Handles the submission of a new artefact.
 */
function submitNewArtefact(event) {
    // Gets the reference to the necessary elements 
    const createButton = document.getElementById("createArtefactButton");
    const artefactNameInput = document.getElementById("artefactName");
    const artefactDescriptionInput = document.getElementById("artefactDescription");
    const artefactComment = document.getElementById("artefactComment");

    event.preventDefault();
    console.log("Submit New Artefact button clicked");
    artefactNameInput.value = "";
    artefactDescriptionInput.value = "";
    const modal = bootstrap.Modal.getInstance(document.getElementById("addArtefactModal"));
    modal.hide();
    
}