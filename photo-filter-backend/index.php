<?php

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function sendJson($status, $body)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}

$routes = [
    '/photo-filter-backend/api/login' => 'auth/login.php',
    '/photo-filter-backend/api/logout' => 'auth/logout.php',
    '/photo-filter-backend/api/session' => 'auth/check-session.php',
    '/photo-filter-backend/api/users' => 'auth/create-user.php',
];

try {
    $path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $handler = $routes[rtrim($path, '/')] ?? 'api/photos.php';
    require __DIR__ . '/' . $handler;
} catch (Throwable $error) {
    error_log($error->getMessage());
    sendJson(500, ['message' => 'The request could not be completed.']);
}
