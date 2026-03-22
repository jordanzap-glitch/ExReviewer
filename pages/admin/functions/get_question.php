<?php
header('Content-Type: application/json; charset=utf-8');
// Ensure the user is authenticated and session is initialized
require_once __DIR__ . '/../includes/init.php';
// Database connection
require_once __DIR__ . '/../../../db/dbcon.php';
// Question helpers
require_once __DIR__ . '/questions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid id']);
    exit;
}

$res = get_question($conn, $id);
if ($res['success']) {
    echo json_encode(['success' => true, 'data' => $res['data']]);
} else {
    echo json_encode(['success' => false, 'error' => $res['error'] ?? 'Not found']);
}

?>
