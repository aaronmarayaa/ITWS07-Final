<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    sendJson(405, ['message' => 'Use POST to create an account.']);
}

$credentials = json_decode(file_get_contents('php://input'), true);
$username = $credentials['username'] ?? null;
$password = $credentials['password'] ?? '';

if (!is_string($username) || $username === '') {
    sendJson(400, ['message' => 'Enter a username.']);
}

if (!is_string($password) || strlen($password) < 8 || strlen($password) > 48) {
    sendJson(422, ['message' => 'Password must be 8-48 characters.']);
}

require __DIR__ . '/../lib/connection.php';

$passwordHash = password_hash($password, PASSWORD_ARGON2ID);

try {
    $db->execute_query('INSERT INTO users (username, password_hash) VALUES (?, ?)', [$username, $passwordHash]);
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() === 1062) {
        sendJson(409, ['message' => 'That username is already in use.']);
    }

    throw $error;
}

sendJson(201, ['user' => ['id' => $db->insert_id, 'username' => $username]]);
