<!--Custody Logger -->
<!--Used for saving data on user activites with evidence, necassary for evidence preservation -->
<!--Required inputs; type:Case or Evidence, id, action, hash for evidence-->
<!--Assumes any evidence action is inputted with the hash -->
<?php
session_start();
require "includes/dbconn.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $userId = $_SESSION["id"];
    $id = $_POST["id"];
    $action = strtolower($_POST["action"]);

    if($_POST["type"]=="Case"){
        $stmt = $conn->prepare("INSERT INTO casecustodyaction (action,user_id,case_id,timestamp) VALUES (?,?,?,NOW())");
        $stmt->execute([$action,$userId,$id]);
    }
    else{
        $hash=$_POST["hash"];
        $stmt = $conn->prepare("INSERT INTO evidencecustodyaction (action,user_id,evidence_id,hash,timestamp) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$action,$userId,$id,$hash]);
    }

    
}
?>
