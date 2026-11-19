<?php

$maxPhotoBytes = 10 * 1024 * 1024;
$contentType = explode(';', $_SERVER['CONTENT_TYPE'] ?? '', 2)[0];

if (strtolower(trim($contentType)) === 'multipart/form-data') {
    $upload = $_FILES['photo'] ?? null;
    if (!is_array($upload) || !is_int($upload['error'] ?? null)) {
        sendJson(400, ['message' => 'The request is missing a photo upload.']);
    }
    if (in_array($upload['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        sendJson(413, ['message' => 'Photos must be 10 MiB or smaller.']);
    }
    if ($upload['error'] !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null)
        || !is_uploaded_file($upload['tmp_name'])) {
        sendJson(400, ['message' => 'The photo upload could not be received. Try again.']);
    }
    $image = file_get_contents($upload['tmp_name'], false, null, 0, $maxPhotoBytes + 1);
} elseif (strtolower(trim($contentType)) === 'image/png') {
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $maxPhotoBytes) {
        sendJson(413, ['message' => 'Photos must be 10 MiB or smaller.']);
    }
    $image = file_get_contents('php://input', false, null, 0, $maxPhotoBytes + 1);
} else {
    sendJson(415, ['message' => 'Send a PNG image as a photo upload or with Content-Type: image/png.']);
}

if ($image === false || $image === '') {
    sendJson(400, ['message' => 'The request body is missing a photo.']);
}
$sizeBytes = strlen($image);
if ($sizeBytes > $maxPhotoBytes) {
    sendJson(413, ['message' => 'Photos must be 10 MiB or smaller.']);
}

$info = @getimagesizefromstring($image);
if ($info === false || $info[2] !== IMAGETYPE_PNG || $info[0] < 1 || $info[1] < 1) {
    sendJson(422, ['message' => 'The request body is not a PNG image.']);
}

require __DIR__ . '/../lib/connection.php';

[$width, $height] = $info;
$now = time();
$createdAt = gmdate('Y-m-d H:i:s', $now);
$db->execute_query(
    'INSERT INTO photos (image, width, height, created_at) VALUES (?, ?, ?, ?)',
    [$image, $width, $height, $createdAt]
);

$id = $db->insert_id;
header('Location: ' . $photosPath . '/' . $id);
sendJson(201, [
    'id' => $id,
    'width' => $width,
    'height' => $height,
    'sizeBytes' => $sizeBytes,
    'createdAt' => gmdate('Y-m-d\TH:i:s\Z', $now),
]);
