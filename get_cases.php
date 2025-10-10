<?php
/*
             === get_cases.php ===
    Author.: Aleron Francois (691807)
    Date...: 9/10/2025 - x/x/2025
    Info...: This script queries the database to return all cases
             with their corresponding creator. cases.php calls this 
             file to display all cases in the list UI.
*/
session_start(); // Start session
include 'includes/dbconn.php'; // Connect to database
header('Content-Type: application/json'); // Set content type to JSON

if (isset($_GET['action']) && $_GET['action'] == 'get_cases') {

    // Gets all cases with corresponding creator
    $sql = "
        SELECT 
            C.id,
            C.name, 
            C.description, 
            C.creation_date, 
            U.username AS creator
        FROM `Case` AS C
        JOIN `USER` AS U ON C.creator_id = U.id
        ORDER BY C.creation_date DESC
    ";
    $stmt = $conn->query($sql); // Exec sql query

    // Fetch all results & convert to json
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cases);
} 
else {
    // Handle invalid 'action' in the url
    echo json_encode(["...error" => "No valid action specified"]);
}
?>
