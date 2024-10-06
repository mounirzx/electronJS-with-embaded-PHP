<?php
$uploadErrors = array(
    UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
    UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
    UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
    UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
    UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
    UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
    UPLOAD_ERR_EXTENSION  => "File upload stopped by extension.",
);

$errorCode = $_FILES["myUpload"]["error"];

if ($errorCode !== UPLOAD_ERR_OK) {
    if (isset($uploadErrors[$errorCode])) {
        throw new Exception($uploadErrors[$errorCode]);
    } else {
        throw new Exception("Unknown error uploading file.");
    }
}
?>