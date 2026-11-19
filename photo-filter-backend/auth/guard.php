<?php

require __DIR__ . '/session.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, ['message' => 'Log in first.']);
}

session_write_close();
