<?php
header('Content-Type: application/json; charset=utf-8');
// Ensure the user is authenticated and session is initialized
require_once __DIR__ . '/../includes/init.php';
// Database connection
require_once __DIR__ . '/../../../db/dbcon.php';
// Question helpers
require_once __DIR__ . '/questions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$question = $_POST['question'] ?? '';
$opt_a = $_POST['opt_a'] ?? '';
$opt_b = $_POST['opt_b'] ?? '';
$opt_c = $_POST['opt_c'] ?? '';
$opt_d = $_POST['opt_d'] ?? '';
$correct = $_POST['correct'] ?? '';
$subject = $_POST['subject'] ?? '';
$year = isset($_POST['year']) && $_POST['year'] !== '' ? (int)$_POST['year'] : null;
$remarks = $_POST['remarks'] ?? '';

$res = update_question($conn, $id, $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct, $subject, $year, $remarks);
if ($res['success']) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $res['error'] ?? 'Update failed']);
}

?>