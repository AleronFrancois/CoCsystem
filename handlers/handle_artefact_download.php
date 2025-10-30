<?php
require '../includes/dbconn.php';
require '../vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

session_start();

if (!isset($_SESSION['id']) || !isset($_GET['artefactid'])) {
    header('Location: /index.php');
    exit;
}

$userId = $_SESSION['id'];
$evidenceId = $_GET['artefactid'];
$mimes = new \Mimey\MimeTypes;

try {
    $stmt = $conn->prepare('
        SELECT `Evidence`.location, `Evidence`.name, `Metadata`.value AS extension
        FROM Evidence
        JOIN `Case_Evidence` ON `Case_Evidence`.evidence_id = `Evidence`.id
        JOIN `Case_User` ON `Case_User`.case_id = `Case_Evidence`.case_id
        JOIN `Metadata` ON `Metadata`.evidence_id = `Evidence`.id AND `Metadata`.key = "mime_type"
        WHERE `Evidence`.id = ? AND `Case_User`.user_id = ?
    ');

    $stmt->execute([$evidenceId, $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        include '../components/access_denied.php';
        exit;
    }

    $location = $result['location'];
    $fileName = $result['name'];
    $extension = $mimes->getExtension($result['extension']);

    $blobClient = BlobRestProxy::createBlobService($_ENV['BLOB_CONNECTION_STRING']);
    $evidenceBlob = $blobClient->getBlob('evidence', $location);

    // Using the helper function here just increases overheads, so it is not necessary here.
    $fileHash = hash('sha256', stream_get_contents($evidenceBlob->getContentStream(), -1, 0));

    $stmt = $conn->prepare('INSERT INTO `EvidenceCustodyAction` (`action`, `description`, `evidence_hash`, `user_id`, `evidence_id`) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['download', "User {$userId} downloaded this piece of evidence", $fileHash, $userId, $evidenceId]);

    $evidenceBlob = $blobClient->getBlob('evidence', $location);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '.' . $extension . '"');
    header('Content-Length: ' . $evidenceBlob->getProperties()->getContentLength());

    fpassthru($evidenceBlob->getContentStream());

} catch (Exception $e) {
    header('Location: /index.php');
    exit;
}

?>