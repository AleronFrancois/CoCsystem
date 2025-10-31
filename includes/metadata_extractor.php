<?php
// Note: While users could potentially hide XSS elements in metadata, applying htmlspecialchars() or trim() to
// pieces of evidence will harm the forensic integrity of the evidence. So these protections cannot be applied here.

use Smalot\PdfParser\Parser;
use getID3;


function extract_metadata_values($file): array {
    $metadata = array();
    $isVideoOrAudio = null;
    $exifTypes = ['image/jpeg', 'image/tiff', 'image/tiff-fx', 'image/png'];
    $id3CompatibleTypes = [
        'audio/mpeg',
        'audio/x-mpeg',
        'audio/wav',
        'audio/x-wav',
        'audio/aac',
        'audio/mp4',
        'audio/flac',
        'audio/ogg',
        'audio/x-ms-wma',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-matroska',
        'video/webm',
        'video/x-ms-wmv'
    ];

    // Get basic file info
    $metadata["mime_type"] = mime_content_type($file['tmp_name']) ?? "unknown";
    $metadata["user_reported_extension"] = pathinfo($file['name'], PATHINFO_EXTENSION) ?? "unknown";
    $metadata["file_size_bytes"] = $file['size'];
    $metadata["original_filename"] = $file['name'] ?? "unknown";

    // Retrieve EXIF data if the file is a supported image
    

    if (in_array($metadata['mime_type'], $exifTypes)) {
        $exifData = @exif_read_data($file['tmp_name'], null, true);

        foreach ($exifData as $exifSection => $data) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(", ", $value);
                }

                $metadata["exif_{$exifSection}_{$key}"] = $value;
            }
        }
    }

    // Retrieve metadata from PDF if the file is a PDF

    if ($metadata['mime_type'] === 'application/pdf') {
        $parser = new Parser();
        try {
            $pdf = $parser->parseFile($file['tmp_name']);
            $pdfMetadata = $pdf->getDetails();

            foreach ($pdfMetadata as $key => $value) {
                if (is_array($value)) {
                    $value = implode(", ", $value);
                }

                $metadata["pdf_{$key}"] = $value;
            }
        } catch (Exception $e) {
            echo '' . $e->getMessage();
            
        }
        
    }

    // Retrieve metadata from Microsoft Office files if the file is a word document, spreadsheet, or powerpoint
    if (str_contains($metadata['mime_type'], 'application/vnd.openxmlformats-officedocument')) {

        if ($metadata['mime_type'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $phpOffice = \PhpOffice\PhpWord\IOFactory::load($file['tmp_name']);
            $documentInfo = $phpOffice->getDocInfo();
        } elseif ($metadata['mime_type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $phpOffice = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $documentInfo = $phpOffice->getProperties();
        } elseif ($metadata['mime_type'] === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
            $phpOffice = \PhpOffice\PhpPresentation\IOFactory::load($file['tmp_name']);
            $documentInfo = $phpOffice->getDocumentProperties();
        }        

        $metadata['office_title'] = $documentInfo->getTitle() ?? 'unknown';
        $metadata['office_author'] = $documentInfo->getCreator() ?? 'unknown';
        $metadata['office_subject'] = $documentInfo->getSubject() ?? 'unknown';
        $metadata['office_keywords'] = $documentInfo->getKeywords() ?? 'unknown';
        $metadata['office_description'] = $documentInfo->getDescription() ?? 'unknown';
        $metadata['office_last_modified_by'] = $documentInfo->getLastModifiedBy() ?? 'unknown';
        $metadata['office_created'] = $documentInfo->getCreated() ?? 'unknown';
        $metadata['office_modified'] = $documentInfo->getModified() ?? 'unknown';
    }

    // Retrieve metadata from video files if the file is a video
    // Retrieve metadata from audio files if the file is an audio file
    if (in_array($metadata['mime_type'], $id3CompatibleTypes)) {
        $id3 = new getID3;
        $fileInfo = $id3->analyze($file['tmp_name']);
        $isVideoOrAudio = explode('/', $metadata['mime_type'])[0];

        $metadata["{$isVideoOrAudio}_duration"] = $fileInfo['playtime_string'] ?? 'unknown';

        if ($isVideoOrAudio === 'video') {
            if (isset($fileInfo['video']['resolution_x']) && isset($fileInfo['video']['resolution_y'])) {
                $metadata['video_resolution'] = $fileInfo['video']['resolution_x'] . ' x ' . $fileInfo['video']['resolution_y'];
            } 

            $metadata['video_fps']  = $fileInfo['video']['frame_rate'] ?? 'unknown';
            $metadata['video_aspectratio'] = $fileInfo['video']['aspect_ratio'] ?? 'unknown';
        }

        if ($metadata['mime_type'] == 'video/quicktime' || $metadata['mime_type'] == 'video/mp4') {
            $metadata['video_creation_date'] = $fileInfo['quicktime']['creation_date'] ?? 'unknown';
            $metadata['video_make'] = $fileInfo['quicktime']['make'] ?? 'unknown';
            $metadata['video_model'] = $fileInfo['quicktime']['model'] ?? 'unknown';
            $metadata['video_software'] = $fileInfo['quicktime']['software'] ?? 'unknown';
        }

        if (isset($fileInfo['quicktime']['gps_latitude']) && isset($fileInfo['quicktime']['gps_longitude'])) {
            $metadata['video_coordinates'] = $fileInfo['quicktime']['gps_latitude'] . ', ' . $fileInfo['quicktime']['gps_longitude'];
        }
    }

    foreach ($metadata as $key => $value) {
        if ($value === 'unknown' || $value === null || $value === '') {
            unset($metadata[$key]);
        }
    }

    return $metadata; 

}
?>