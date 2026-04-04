<?php
header('Content-Type: application/json; charset=UTF-8');
// Return JSON {count: N} for the logged-in user's attempts, optionally filtered by subject_id
include __DIR__ . '/../includes/init.php';
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../../../db/dbcon.php';
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$subject_id = isset($_GET['subject_id']) && $_GET['subject_id'] !== '' ? (int)$_GET['subject_id'] : null;
$result = ['count' => 0];
if ($user_id) {
    $where = "WHERE user_id = " . $user_id;
    if ($subject_id) $where .= " AND subjects_id = " . $subject_id;
    $sql = "SELECT COUNT(id) AS cnt FROM tbl_attempts " . $where;
    if ($res = mysqli_query($conn, $sql)) {
        $r = mysqli_fetch_assoc($res);
        $result['count'] = isset($r['cnt']) ? (int)$r['cnt'] : 0;
    }
}
echo json_encode($result);
exit;

?>
