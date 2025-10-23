<!---Custody Log-->
<!---Used to select which case or evidence a user wants to view the logs for --> 
<?php 
require "includes/dbconn.php";
$stmt = $conn->prepare("Select name, id from evidence");
$stmt->execute();
$evidence = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("Select name, id from `case`");
$stmt->execute();
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/scripts.js"></script>
    <title>Chain of custody generator</title>
</head>
<navbar>
    <?php include "components/navbar.php"?>
</navbar>
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
</div>

