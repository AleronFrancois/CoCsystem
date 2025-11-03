<?php
require 'dbconn.php';
require '../vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

// Stop users form accessing this file
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('Location: /index.php');
    exit;
}

function getEvidenceHash($evidenceId, $location = null) {
    global $conn;

    if ($location == null) {
        $stmt = $conn->prepare('
            SELECT `Evidence`.location
            FROM Evidence
            WHERE `Evidence`.id = ?
        ');

        $stmt->execute([$evidenceId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $location = $result['location'];
    }
    

    $blobClient = BlobRestProxy::createBlobService($_ENV['BLOB_CONNECTION_STRING']);
    $evidenceBlob = $blobClient->getBlob('evidence', $location);

    return hash('sha256', stream_get_contents($evidenceBlob->getContentStream(), -1, 0));
}
?>