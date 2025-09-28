<html>
    <head>
        <meta charset="UTF-8">
        <title>Chain of Custody Tracker - Case XXX</title>
        <link rel="stylesheet" href="styles/bootstrap.min.css">
        <link rel="stylesheet" href="styles/styles.css">
        <script src="scripts/bootstrap.bundle.min.js" defer></script>
    </head>
    <body class="background d-flex flex-column vh-100">
        <nav class="navbar navbar-expand-sm accent ">
            <div class="container-fluid ">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active">[Cases]</a>
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
                        <h2>[CASE]</h2>
                        <img src="images/add_icon.svg">
                    </div>
                    <hr>
                    <ol class="list-group flex-grow-1 overflow-auto mb-0">
                        <!-- list of cases -->
                    </ol>
                </div>
            </div>
            
            <div class="col-4 d-flex flex-column">
                <div class="p-3 border foreground shadow rounded-5 h-100 overflow-auto">
                    <h2>Evidence Info</h2>
                    <div>
                    <!-- case info -->
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>