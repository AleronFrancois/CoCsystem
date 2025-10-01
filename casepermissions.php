<?php
session_start();
require "dbconn.php"; 

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION["user_id"];
$caseId = $_SESSION["case_id"]; // Current case
$stmt = $conn->prepare("SELECT role FROM User WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

if ($role !== 'supervisor') {
    header("Location: index.php");
    exit;
}

$message = "";

$confirmUsername = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user_id'])) {
    $inputUserId = trim($_POST['add_user_id']);

    if ($inputUserId) {
        $stmt = $conn->prepare("SELECT username FROM User WHERE id = ?");
        $stmt->execute([$inputUserId]);
        $username = $stmt->fetchColumn();

        if ($username) {
            $confirmUsername = $username;

            if (isset($_POST['confirmed']) && $_POST['confirmed'] === 'yes') {
                $stmtInsert = $conn->prepare("INSERT INTO Case_User (case_id, user_id) VALUES (?, ?)");
                if ($stmtInsert->execute([$caseId, $inputUserId])) {
                    $message = "$username added to the case successfully!";
                } else {
                    $message = "Failed to add $username to the case.";
                }
            }
        } else {
            $message = "User ID $inputUserId does not exist.";
        }
    } else {
        $message = "Please enter a User ID.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user_id'])) {
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
");
$stmt->execute([$caseId]);
$investigators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Investigators</title>
</head>
<body>

<h2>Manage Investigators for Case</h2>

<?php if ($message) echo "<p>$message</p>"; ?>

<h3>Add Investigator</h3>
<form id="addForm" method="POST">
    <label for="add_user_id">User ID:</label>
    <input type="text" id="add_user_id" name="add_user_id" required>
    <input type="hidden" id="confirmed" name="confirmed" value="">
    <button type="submit">Add to Case</button>
</form>

<script>
const confirmUsername = "<?php echo $confirmUsername; ?>";

document.getElementById('addForm').addEventListener('submit', function(e) {
    const userIdInput = document.getElementById('add_user_id').value.trim();
    if (!userIdInput) return;

    if (confirmUsername) {
        const confirmed = confirm(`Do you want to add "${confirmUsername}" to the case?`);
        if (!confirmed) {
            e.preventDefault();
        } else {
            document.getElementById('confirmed').value = 'yes';
        }
    }
});
</script>

<h3>Investigators on Case</h3>
<table>
    <tr>
        <th>Investigator Name</th>
        <th>Action</th>
    </tr>
    <?php foreach ($investigators as $inv): ?>
    <tr>
        <td><?php echo htmlspecialchars($inv['username']); ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Remove <?php echo ($inv['username']); ?> from this case?');">
                <input type="hidden" name="remove_user_id" value="<?php echo $inv['id']; ?>">
                <button type="submit">Remove Investigator</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
