<?php

$db->execute_query('DELETE FROM photos WHERE id = ?', [$id]);

if ($db->affected_rows === 0) {
    sendJson(404, ['message' => 'Photo not found.']);
}

http_response_code(204);
