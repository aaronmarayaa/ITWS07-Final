<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    sendJson(405, ['message' => 'Use POST to log out.']);
}

require __DIR__ . '/session.php';

session_unset();
if (!session_destroy()) {
    sendJson(500, ['message' => 'Could not log out.']);
}

$cookiePath = session_get_cookie_params()['path'];
setcookie(session_name(), '', time() - 3600, $cookiePath);
sendJson(200, ['message' => 'Logged out.']);
