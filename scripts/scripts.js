function redirectAfterDelay() {
    setTimeout(function() {
        window.location.href = "index.php";
    }, 3000);
}

/**
 * Makes the artefact list item clickable. Once clicked, the artefact changes colour and provides additional buttons to 
 * download, or rehash the artefact. It also changes the context men to show metadata or allow the user to comment
 */
function handleArtefactClick(event) {
    const listItem = event.currentTarget;
    const artefactId = listItem.id.replace('artefact_', '');

    // Deselect the currently selected item
    document.querySelectorAll('.list-group-item').forEach(item => {
        if (item.classList.contains('active')) {
            item.classList.remove('active');
            document.getElementById('buttonGroupArtefact_' + item.id.replace('artefact_', '')).hidden = true;
        }
    });

    listItem.classList.add('active');
    document.getElementById('buttonGroupArtefact_' + artefactId).hidden = false;
    
    
}