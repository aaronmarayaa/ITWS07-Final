<?php

$photosPath = '/photo-filter-backend/api/photos';
$pattern = '~^' . preg_quote($photosPath, '~') . '(?:/([1-9][0-9]*)(/image)?)?/?$~D';

if (!preg_match($pattern, $path, $matches)) {
    sendJson(404, ['message' => 'Endpoint not found.']);
}

$id = isset($matches[1]) ? filter_var($matches[1], FILTER_VALIDATE_INT) : null;
if ($id === false) {
    sendJson(404, ['message' => 'Photo not found.']);
}

$method = $_SERVER['REQUEST_METHOD'];
$wantsImage = isset($matches[2]);
$allowed = ['GET', 'HEAD'];
if ($id === null) {
    $allowed[] = 'POST';
} elseif (!$wantsImage) {
    $allowed[] = 'DELETE';
}
if (!in_array($method, $allowed, true)) {
    header('Allow: ' . implode(', ', $allowed));
    sendJson(405, ['message' => 'Method not allowed.']);
}

require __DIR__ . '/../auth/guard.php';

if ($method === 'POST') {
    require __DIR__ . '/save-photo.php';
    return;
}

require __DIR__ . '/../lib/connection.php';

if ($method === 'DELETE') {
    require __DIR__ . '/delete-photo.php';
    return;
}

$photoFields = "id, width, height, OCTET_LENGTH(image) AS sizeBytes, DATE_FORMAT(created_at, '%Y-%m-%dT%H:%i:%sZ') AS createdAt";
if ($id === null) {
    $result = $db->execute_query('SELECT ' . $photoFields . ' FROM photos ORDER BY id DESC');
    $photos = $result->fetch_all(MYSQLI_ASSOC);
    sendJson(200, ['photos' => $photos]);
}

$fields = $wantsImage ? 'image' : $photoFields;
$result = $db->execute_query('SELECT ' . $fields . ' FROM photos WHERE id = ?', [$id]);
$photo = $result->fetch_assoc();
if (!$photo) {
    sendJson(404, ['message' => 'Photo not found.']);
}

if (!$wantsImage) {
    sendJson(200, $photo);
}

header('Content-Type: image/png');
header('Content-Length: ' . strlen($photo['image']));
echo $photo['image'];
