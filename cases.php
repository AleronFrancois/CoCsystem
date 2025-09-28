<html>
    <head>
        <meta charset="UTF-8">
        <title>Chain of Custody Tracker - Cases</title>
        <link rel="stylesheet" href="styles/bootstrap.min.css">
        <link rel="stylesheet" href="styles/styles.css">
        <script src="scripts/bootstrap.bundle.min.js" defer></script>
    </head>
    <body class="background d-flex flex-column vh-100">
        <nav class="navbar navbar-expand-sm accent ">
            <div class="container-fluid ">
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
                <img src="images/account_icon.svg">
            </div>
        </nav>
        <div class="d-flex flex-grow-1 p-4 overflow-hidden">
            <div class="col-8 d-flex flex-column me-2">
                <div class="p-3 border foreground shadow rounded-5 d-flex flex-column h-100">
                    <div class="justify-content-between d-flex">
                        <h2>Cases</h2>
                        <img role="button" src="images/add_icon.svg" data-bs-toggle="modal" data-bs-target="#addCaseModal" hover="pointer">

                    </div>
                    <hr>
                    <ol class="list-group flex-grow-1 overflow-auto mb-0">
                        <!-- list of cases -->
                    </ol>
                </div>
            </div>
            
            <div class="col-4 d-flex flex-column">
                <div class="p-3 border foreground shadow rounded-5 h-100 overflow-auto">
                    <h2>Case Info</h2>
                    <div>
                    <!-- case info -->
                    </div>
                </div>
            </div>
        </div>
    </body>

    <div class="modal fade" id="addCaseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">New Case</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" placeholder="Case Name" id="caseName">
                    <input type="text" class="form-control mt-3" placeholder="Case Description">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</html>