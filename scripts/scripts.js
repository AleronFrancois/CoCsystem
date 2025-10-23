function redirectAfterDelay() {
    setTimeout(function() {
        window.location.href = "index.php";
    }, 3000);
}

function getSelectedListItem() {
    return document.querySelector('.list-group-item.active');
}

function viewComments() {
    const listItem = getSelectedListItem();
    const artefactInfoPanelTitle = document.getElementById('artefactInfoPanelTitle');
    const commentButton = document.getElementById('viewCommentsButton');
    const detailsButton = document.getElementById('viewDetailsButton');
    const artefactInfoList = document.getElementById('artefactInfoList');
    const urlParams = new URLSearchParams(window.location.search);
    const caseId = urlParams.get('caseid');

    let comments = JSON.parse(listItem.getAttribute('data-comments'));

    commentButton.classList.add('btn-primary');
    commentButton.classList.remove('btn-secondary');
    detailsButton.classList.remove('btn-primary');
    detailsButton.classList.add('btn-secondary');

    artefactInfoPanelTitle.innerHTML = "Comments";


    artefactInfoList.innerHTML = `
        <form method="POST" enctype="multipart/form-data" action="/handlers/handle_comment.php">
            <div class="mb-3">
                <input type="hidden" name="artefactId" id="artefactId" value="${listItem.id.replace('artefact_', '')}">
                <input type="hidden" name="caseId" id="caseId" value="${caseId}">

                <label for="comment" class="form-label">Post a Comment</label>
                <textarea rows="3" type="text" class="form-control mb-3" id="comment" name="comment" placeholder="Type your comment..." required></textarea>
                <button type="submit" class="btn btn-primary" id="postCommentButton">Post Comment</button>
            </div>
        </form>
    `;
    comments.reverse();
    comments.forEach(comment => {
        artefactInfoList.appendChild(document.createElement('div')).innerHTML = `
        <div>
            <hr>
            <p class="text-muted m-0">
                ${comment.commenter_name}
            </p>
            <p class="text-muted m-0">
                ${new Date(comment.timestamp).toLocaleString()}
            </p>
            <p>${comment.content}</p>
        </div>
        `
    });
}
function viewLog(type,id) {
    const url = `components/Log.php?type=${type}&id=${id}`;
    window.open(url, '_blank'); // opens in new tab
}    


function viewDetails() {
    const listItem = getSelectedListItem();
    const artefactInfoPanelTitle = document.getElementById('artefactInfoPanelTitle');
    const commentButton = document.getElementById('viewCommentsButton');
    const detailsButton = document.getElementById('viewDetailsButton');

    commentButton.classList.remove('btn-primary');
    commentButton.classList.add('btn-secondary');
    detailsButton.classList.add('btn-primary');
    detailsButton.classList.remove('btn-secondary');

    artefactInfoPanelTitle.innerHTML = "Details and Metadata";
    listItem.click();
}

/**
 * Makes the artefact list item clickable. Once clicked, the artefact changes colour and provides additional buttons to 
 * download, or rehash the artefact. It also changes the context men to show metadata or allow the user to comment
 */
function handleArtefactClick(event) {
    const listItem = event.currentTarget;
    const artefactId = listItem.id.replace('artefact_', '');
    const artefactInfoPanel = document.getElementById('artefactInfoPanel');
    const artefactInfoList = document.getElementById('artefactInfoList');

    
    let metadata = JSON.parse(listItem.getAttribute('data-metadata'));


    // Deselect the currently selected item
    document.querySelectorAll('.list-group-item').forEach(item => {
        if (item.classList.contains('active')) {
            item.classList.remove('active');
            document.getElementById('buttonGroupArtefact_' + item.id.replace('artefact_', '')).hidden = true;
        }
    });

    listItem.classList.add('active');
    document.getElementById('buttonGroupArtefact_' + artefactId).hidden = false;
    
    // Get the artefact metadata and display it
    artefactInfoList.innerHTML = ''; // Clear previous metadata
    metadata.forEach(entry => {
        artefactInfoList.appendChild(document.createElement('div')).innerHTML = '<p class="text-muted m-0">' + entry.key + '</p><p>' + entry.value + '</p>';
    });

    document.getElementById('evidenceName').innerHTML = listItem.getAttribute('data-name');

    // Unhide the artefact info panel
    artefactInfoPanel.classList.remove('d-none');

}

/**
 * If a piece of evidence is linked in the URL, automatically select it and display either its metadata or comments
 */
window.onload = function() {
    
    const url = window.location.href;
    const urlParams = new URLSearchParams(window.location.search);
    const artefactId = urlParams.get('artefactid');

    console.log(url);
    

    if (url.includes('/artefacts.php') && artefactId != null) {
        console.log("dkajwhd");
        const listItem = document.getElementById('artefact_' + artefactId);
        if (listItem) {
            listItem.click();
            if(urlParams.get('panel') == 'comments') {
                console.log("comments requested");
                
                viewComments(artefactId);
            }
            
        }
    }


}
//For custody log 
function toggleupdate(type) {
    const caseSelector = document.getElementById('case-selector');
    const evidenceSelector = document.getElementById('evidence-selector');

    if (type === 'case') {
        caseSelector.style.display = 'block';
        evidenceSelector.style.display = 'none';
    } else {
        caseSelector.style.display = 'none';
        evidenceSelector.style.display = 'block';
    }
}
//for custody log
function getid() {
    // Check which toggle is selected
    const type = document.querySelector('input[name="type"]:checked')?.value?.toLowerCase();
    let id = '';

    if (type === 'case') {
        // Get the ID from the text box or dropdown
        id = document.getElementById('caseIdInput').value || document.getElementById('caseSelect').value;
    } else if (type === 'evidence') {
        id = document.getElementById('evidenceIdInput').value || document.getElementById('evidenceSelect').value;
    }

    return id;
}
