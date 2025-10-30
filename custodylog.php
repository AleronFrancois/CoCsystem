<!---Custody Log-->
<!---Used to select which case or evidence a user wants to view the logs for --> 
<?php
session_start(); 
require "includes/dbconn.php";

if (!isset($_SESSION["id"])) {
    // Redirect to login.php if not logged on
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        name, 
        id 
    FROM `evidence` 
    INNER JOIN `Case_Evidence` ON `Case_Evidence`.evidence_id = `Evidence`.id
    INNER JOIN `Case_User` ON `Case_User`.case_id = `Case_Evidence`.case_id
    WHERE `Case_User`.user_id = ?");
$stmt->execute([$_SESSION['id']]);
$evidence = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("
    SELECT 
        name, 
        id 
    FROM `case` 
    INNER JOIN `Case_User` ON `Case`.id = `Case_User`.case_id
    WHERE `Case_User`.user_id = ?");
$stmt->execute([$_SESSION['id']]);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<head>
    <meta charset="UTF-8">
    <title>Chain of custody generator</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/bootstrap.bundle.min.js" defer></script>
    <script src="scripts/scripts.js" defer></script>
   
</head>
<?php include "components/navbar.php"; ?>
<div class ="clog-container">
    <div class="toggle-group">
        <input type="radio" id="case" name="type" value="Case" onchange="toggleupdate('case')">
        <label for="case">Case</label>

        <input type="radio" id="evidence" name="type" value="Evidence" onchange="toggleupdate('evidence')">
        <label for="evidence">Evidence</label>
    </div>  


    <!-- Case Selector -->
    <div id="case-selector" style="display: none;">
        <label>Enter Case ID</label>
        <input type="text" id="caseIdInput" class="form-control mb-2" placeholder="Enter Case ID...">

        <label>Or Select Case:</label>
        <select id="caseSelect" class="form-select">
            <option value="">Choose case...</option>
            <?php foreach ($cases as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['id'] . ': ' . $c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" onclick="viewLog('case',getid())">Submit</button>
    </div>

    <!-- Evidence Selector -->
    <div id="evidence-selector" style="display: none;">
        <label>Enter Evidence ID</label>
        <input type="text" id="evidenceIdInput" class="form-control mb-2" placeholder="Enter Evidence ID...">

        <label>Or Select Evidence:</label>
        <select id="evidenceSelect" class="form-select">
            <option value="">Choose evidence...</option>
            <?php foreach ($evidence as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['id'] . ': ' . $e['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" onclick="viewLog('evidence',getid())">Submit</button>
    </div>
    <p class="text-muted fs-6">For a full case custody log, access the case first via the 'cases' page.</p>
</div>

