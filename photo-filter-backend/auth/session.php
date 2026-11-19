<?php

session_name('PhotoFilterSession');

if (!session_start([
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_path' => '/photo-filter-backend/api',
])) {
    sendJson(500, ['message' => 'Could not start the session.']);
}
