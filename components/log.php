<!-- Log.php -->
<!-- Used to get the custody logs of evidence and cases -->
<!-- Required inputs: type of evidence, id -->
<?php 
require "../includes/dbconn.php"; 


$type = $_GET['type'];
$id = $_GET['id']; 

$stmt = $conn->prepare("Select name From evidence WHERE id = ?");
$stmt->execute([$id]);
$name = $stmt->fetchColumn();

$type = strtolower($type); // correct lowercase conversion in PHP

if ($type === 'evidence') {
    $stmt = $conn->prepare("
        SELECT 
            e2.timestamp, 
            e2.`action`, 
            e2.evidence_hash AS hash, 
            u.username, 
            e2.evidence_id AS id,
            e2.user_id AS user_id, 
            e.name AS name
        FROM evidencecustodyaction e2 
        JOIN `user` u ON e2.user_id = u.id 
        JOIN evidence e ON e2.evidence_id = e.id   
        WHERE e2.evidence_id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} 
elseif ($type === 'case') {
    $stmt = $conn->prepare("
        SELECT  
            e2.timestamp, 
            e2.`action`, 
            e2.action_hash AS hash, 
            u.username, 
            e2.case_id AS id, 
            c.name AS name
        FROM casecustodyaction e2 
        JOIN `user` u ON e2.user_id = u.id 
        JOIN `case` c ON e2.case_id = c.id   
        WHERE e2.case_id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} 
else {
    die("Invalid type specified. Must be 'evidence' or 'case'.");
}

if (!empty($data)) {
    $type = ucfirst($type);
    echo "<h1>Custody logs for $name</h1>";
    echo "<h2>$type ID: $id</h2>";
    echo "<table border = 1>";
    echo "<tr><th>Timestamp</th><th>Action</th><th>User</th><th>User ID</th><th>Hash</th></tr>";
    foreach ($data as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
        echo "<td>" . htmlspecialchars($row['action']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['hash']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data found for ID $id.";
}
?>
