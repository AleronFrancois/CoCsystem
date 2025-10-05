<?php
ini_set('openssl.cafile', '');
ini_set('openssl.capath', '');

# NOTE: This file is for uploading BLOBs to Azure BLOB storage. Disregard ths file if your deployment of the CoC system is not
# using BLOB storage.

# Code derived from https://www.azurephp.dev/2022/02/azure-blob-storage-in-php-applications/
require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

function upload_file_to_blob($file):string {
    $randomFileName = bin2hex(random_bytes(32));
    $blobConnectionString = $_ENV['BLOB_CONNECTION_STRING'];

    $blobClient = BlobRestProxy::createBlobService($blobConnectionString);

    $fileContents = fopen($file['tmp_name'], 'r');

    try {
        $blobClient->createBlockBlob('evidence', $randomFileName, $fileContents);
        return $randomFileName;
    } catch (Exception $e) {
        echo ''. $e->getMessage();
        return 'ERROR';
    }

}

?>