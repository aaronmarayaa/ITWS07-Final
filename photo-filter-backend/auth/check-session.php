<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    sendJson(405, ['message' => 'Use GET to check the session.']);
}

require __DIR__ . '/guard.php';
http_response_code(204);
