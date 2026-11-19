<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    sendJson(405, ['message' => 'Use POST to log in.']);
}

$credentials = json_decode(file_get_contents('php://input'), true);
$username = $credentials['username'] ?? null;
$password = $credentials['password'] ?? '';

if (!is_string($password) || strlen($password) < 8 || strlen($password) > 48) {
    sendJson(422, ['message' => 'Password must be 8-48 characters.']);
}

if (!is_string($username)) {
    $username = null;
}

require __DIR__ . '/../lib/connection.php';

$result = $db->execute_query('SELECT id, username, password_hash FROM users WHERE username = ?', [$username]);
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    sendJson(401, ['message' => 'Username or password did not match.']);
}

require __DIR__ . '/session.php';

if (!session_regenerate_id(true)) {
    sendJson(500, ['message' => 'Could not start the login session.']);
}

$_SESSION['user_id'] = $user['id'];
sendJson(200, ['user' => ['id' => $user['id'], 'username' => $user['username']]]);
