<?php
/**
 * Normalize various date/year inputs into a Y-m-d date string.
 * Accepts: YYYY, YYYY-MM, YYYY-MM-DD, or any strtotime-parsable value.
 * Returns null on failure.
 */
function normalize_to_date($val)
{
    $v = trim((string)$val);
    if ($v === '') return null;
    if (preg_match('/^\d{4}$/', $v)) return sprintf('%04d-01-01', (int)$v);
    if (preg_match('/^\d{4}-\d{2}$/', $v)) return $v . '-01';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
    $ts = strtotime($v);
    if ($ts === false) return null;
    return date('Y-m-d', $ts);
}


/**
 * Insert a new academic year session into tbl_academicyears
 * @param mysqli $conn
 * @param int|string $sy_start  Year or date-like value (YYYY, YYYY-MM, YYYY-MM-DD)
 * @param int|string $sy_end
 * @param bool|int $is_active
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_session($conn, $sy_start, $sy_end, $is_active = false)
{
    $active = $is_active ? 1 : 0;

    $start_date = normalize_to_date($sy_start);
    $end_date = normalize_to_date($sy_end);
    if (!$start_date || !$end_date) {
        return ['success' => false, 'error' => 'Invalid date values.'];
    }
    if (strtotime($start_date) >= strtotime($end_date)) {
        return ['success' => false, 'error' => 'Start date must be before end date.'];
    }

    // Check for existing identical session (compare by exact date range)
    $checkSql = "SELECT id FROM tbl_academicyears WHERE sy_start = ? AND sy_end = ? LIMIT 1";
    $chk = mysqli_prepare($conn, $checkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'ss', $start_date, $end_date);
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        if ($row = mysqli_fetch_assoc($res)) {
            return ['success' => false, 'error' => 'An academic year with the same range already exists.'];
        }
    }

    // If activating this session, deactivate others
    if ($active) {
        $upd = "UPDATE tbl_academicyears SET is_active = 0 WHERE is_active = 1";
        mysqli_query($conn, $upd);
    }

    $sql = "INSERT INTO tbl_academicyears (sy_start, sy_end, is_active) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }
    mysqli_stmt_bind_param($stmt, 'ssi', $start_date, $end_date, $active);
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }

    return ['success' => false, 'error' => 'Database error (insert failed).'];
}

/**
 * Get all sessions from tbl_academicyears
 * @param mysqli $conn
 * @return array
 */
function get_sessions($conn)
{
    $sql = "SELECT id, sy_start, sy_end, is_active FROM tbl_academicyears ORDER BY sy_start DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
    return $rows;
}


/**
 * Fetch a single session by id
 */
function get_session($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "SELECT id, sy_start, sy_end, is_active FROM tbl_academicyears WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) return ['success' => false, 'error' => 'Database error (execute failed).'];
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) return ['success' => false, 'error' => 'Session not found.'];
    return ['success' => true, 'data' => ['id' => (int)$row['id'], 'sy_start' => $row['sy_start'], 'sy_end' => $row['sy_end'], 'is_active' => (int)$row['is_active']]];
}


/**
 * Update session
 */
function update_session($conn, $id, $sy_start, $sy_end, $is_active)
{
    $id = (int)$id;
    $active = $is_active ? 1 : 0;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];

    $start_date = normalize_to_date($sy_start);
    $end_date = normalize_to_date($sy_end);
    if (!$start_date || !$end_date) return ['success' => false, 'error' => 'Invalid date values.'];
    if (strtotime($start_date) >= strtotime($end_date)) return ['success' => false, 'error' => 'Start date must be before end date.'];

    // If activating this session, deactivate others
    if ($active) {
        $upd = "UPDATE tbl_academicyears SET is_active = 0 WHERE is_active = 1";
        mysqli_query($conn, $upd);
    }

    $sql = "UPDATE tbl_academicyears SET sy_start = ?, sy_end = ?, is_active = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'ssii', $start_date, $end_date, $active, $id);
    if (mysqli_stmt_execute($stmt)) return ['success' => true];
    return ['success' => false, 'error' => 'Database error (update failed).'];
}


/**
 * Delete session
 */
function delete_session($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "DELETE FROM tbl_academicyears WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true];
        return ['success' => false, 'error' => 'Session not found or already deleted'];
    }
    return ['success' => false, 'error' => 'Database error (delete failed).'];
}


// Lightweight JSON endpoints: view (GET), add/update/delete (POST)
if (php_sapi_name() !== 'cli') {
    if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
        if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../../db/dbcon.php'; }
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$_GET['id'];
        if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }
        if (!isset($conn) || !$conn) { echo json_encode(['success' => false, 'error' => 'Database connection not available']); exit; }
        $res = get_session($conn, $id);
        echo json_encode($res);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add','update','delete'], true)) {
        if (!isset($conn) || !$conn) { @include_once __DIR__ . '/../../../db/dbcon.php'; }
        header('Content-Type: application/json; charset=utf-8');
        $token = $_POST['csrf_token'] ?? '';
        if (function_exists('verify_csrf_token') && !verify_csrf_token($token)) { echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']); exit; }
        if (!isset($conn) || !$conn) { echo json_encode(['success' => false, 'error' => 'Database connection not available']); exit; }

        $action = $_POST['action'];
        if ($action === 'add') {
            $sy_start_raw = $_POST['sy_start'] ?? '';
            $sy_end_raw = $_POST['sy_end'] ?? '';
            // Force newly added sessions to be active and deactivate others
            $is_active = true;
            $sy_start_date = normalize_to_date($sy_start_raw);
            $sy_end_date = normalize_to_date($sy_end_raw);
            if (!$sy_start_date || !$sy_end_date) { echo json_encode(['success' => false, 'error' => 'Invalid date input']); exit; }
            $res = add_session($conn, $sy_start_date, $sy_end_date, $is_active);
            if (!empty($res['success']) && !empty($res['id'])) {
                // return created session data with date strings
                echo json_encode(['success' => true, 'id' => (int)$res['id'], 'data' => ['id' => (int)$res['id'], 'sy_start' => $sy_start_date, 'sy_end' => $sy_end_date, 'is_active' => $is_active ? 1 : 0]]);
                exit;
            }
            echo json_encode($res);
            exit;
        }

        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $sy_start_raw = $_POST['sy_start'] ?? '';
            $sy_end_raw = $_POST['sy_end'] ?? '';
            $status = $_POST['session_status'] ?? 'Inactive';
            $is_active = ($status === 'Active') ? true : false;
            $sy_start_date = normalize_to_date($sy_start_raw);
            $sy_end_date = normalize_to_date($sy_end_raw);
            if (!$sy_start_date || !$sy_end_date) { echo json_encode(['success' => false, 'error' => 'Invalid date input']); exit; }
            $res = update_session($conn, $id, $sy_start_date, $sy_end_date, $is_active);
            echo json_encode($res);
            exit;
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $res = delete_session($conn, $id);
            echo json_encode($res);
            exit;
        }
    }
}

?>
