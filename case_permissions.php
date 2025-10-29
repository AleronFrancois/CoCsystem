<?php
session_start();
/*
    Case permission management system
    Allows supervisors to add and remove investigators on case
    */
require "includes/dbconn.php"; 

if (!isset($_SESSION['id'])) { //if logged out 
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['id']; 
$caseId = $_GET['case_id']; //current case
$stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

if ($role !== 'supervisor') { //ensures the user is a supervisor
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT `name` FROM `case` WHERE id = ?");
$stmt->execute([$caseId]);
$caseName = $stmt->fetchColumn();
$message = "";

$username = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user_id'])) {  //for adding investigators to a case
    $inputUserId = trim($_POST['add_user_id']);

    if ($inputUserId) {
        $stmt = $conn->prepare("SELECT username FROM User WHERE id = ?");
        $stmt->execute([$inputUserId]);
        $username = $stmt->fetchColumn(); //get username

        if ($username) {
            if (isset($_POST['confirmed']) && $_POST['confirmed'] === 'yes') {  //confirming the double check
                
                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM `Case_User` WHERE case_id = ? AND user_id = ?"); //check to ensure that investigator isnt already on case
                $stmtCheck->execute([$caseId, $inputUserId]);
                $alreadyExists = $stmtCheck->fetchColumn();

                if ($alreadyExists > 0) {
                $message = "$username is already assigned to this case.";
                } 
                else {
                    //add user
                    $stmtInsert = $conn->prepare("INSERT INTO Case_User (case_id, user_id) VALUES (?, ?)");
                    if ($stmtInsert->execute([$caseId, $inputUserId])) {
                        $message = "$username added to the case successfully!";
                    } 
                    else {
                        $message = "Failed to add $username to the case.";
                    }
                }
            }
        } 
        else {
            $message = "User ID $inputUserId does not exist.";
        }
    } else {
        $message = "Please enter a User ID.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user_id'])) { //for removing investigators from a case
    $removeUserId = $_POST['remove_user_id'];

    $stmtDel = $conn->prepare("DELETE FROM Case_User WHERE case_id = ? AND user_id = ?");
    if ($stmtDel->execute([$caseId, $removeUserId])) {
        $message = "Investigator removed successfully.";
    } else {
        $message = "Failed to remove investigator.";
    }
}

$stmt = $conn->prepare(" 
    SELECT U.id, U.username 
    FROM Case_User CU
    JOIN User U ON CU.user_id = U.id
    WHERE CU.case_id = ?
"); //gets investigators already on case
$stmt->execute([$caseId]);
$investigators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Manage Investigators</title>
        <link rel="stylesheet" href="styles/bootstrap.min.css">
        <link rel="stylesheet" href="styles/styles.css">
        <script src="scripts/bootstrap.bundle.min.js" defer></script>
        <script src="scripts/scripts.js" defer></script>
    </head>
    <body>
    <?php include "components/navbar.php"?>
   <div class="p-3 border foreground shadow rounded-5 m-auto mt-5 w-50">
     <h2>Manage Investigators for Case <?php echo htmlspecialchars($caseId . " - " . $caseName); ?></h2>

    <h3>Add Investigator</h3>
    <form id="addForm" method="POST">
        <label for="add_user_id">User ID:</label>
        <input type="text" id="add_user_id" name="add_user_id" class="form-control w-25 mb-2" required>
        <input type="hidden" id="confirmed" name="confirmed" value="">
        <button class="btn btn-primary mb-3" type="submit">Add to Case</button>
        <div id="confirmSpace"></div>
    </form>

    <?php if ($message) echo "<p>$message</p>"; ?>

    <script>
    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault(); // stop default submit

        const form = this;
        const userIdInput = document.getElementById('add_user_id').value.trim();
        if (!userIdInput) return;
        //double check
        document.getElementById('confirmSpace').innerHTML = `
            <p>Are you sure you want to add User ID <b>${userIdInput}</b> to the case?</p>
            <button type="button" id="confirmYes" class="btn btn-success mb-3">Yes</button>
            <button type="button" id="confirmNo" class="btn btn-danger mb-3">No</button>
        `;
        //if yes clicked
        document.getElementById("confirmYes").onclick = () => {
            document.getElementById('confirmed').value = 'yes';
            form.submit();
        };
        //if no clicked
        document.getElementById("confirmNo").onclick = () => {
            document.getElementById('confirmSpace').innerHTML = "";
        };
    });
    </script>
        <h3>Investigators on Case</h3>
        <table>
            <tr>
                <th>Investigator Name</th>
                <th>Investigator ID</th>
                <th>Action</th>
            </tr>
            <!-- creates the table of investigators on case -->
            <?php foreach ($investigators as $inv): ?>
            <tr>
                <td><?php echo htmlspecialchars($inv['username']); ?></td>
                <td><?php echo $inv['id']?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Remove <?php echo ($inv['username']); ?> from this case?');">
                        <input class="form-control" type="hidden" name="remove_user_id" value="<?php echo $inv['id']; ?>">
                        <button class="btn btn-primary" type="submit">Remove Investigator</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    

    </body>
</html>
