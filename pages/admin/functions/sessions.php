<?php
/**
 * Insert a new academic year session into tbl_academicyears
 * @param mysqli $conn
 * @param int|string $sy_start
 * @param int|string $sy_end
 * @param bool|int $is_active
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_session($conn, $sy_start, $sy_end, $is_active = false)
{
    // Normalize and validate
    $start = (int)$sy_start;
    $end = (int)$sy_end;
    $active = $is_active ? 1 : 0;

    if ($start <= 0 || $end <= 0) {
        return ['success' => false, 'error' => 'Invalid year values.'];
    }
    if ($start >= $end) {
        return ['success' => false, 'error' => 'Start year must be less than end year.'];
    }

    // Build DATE strings for DB (use first day of year) because columns are DATE
    $start_date = sprintf('%04d-01-01', $start);
    $end_date = sprintf('%04d-01-01', $end);

    // Check for existing identical session (compare by year start/end)
    $checkSql = "SELECT id FROM tbl_academicyears WHERE YEAR(sy_start) = ? AND YEAR(sy_end) = ? LIMIT 1";
    $chk = mysqli_prepare($conn, $checkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'ii', $start, $end);
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

?>

<?php
/**
 * Get all sessions from tbl_academicyears
 * @param mysqli $conn
 * @return array
 */
function get_sessions($conn)
{
    $sql = "SELECT id, YEAR(sy_start) AS sy_start, YEAR(sy_end) AS sy_end, is_active FROM tbl_academicyears ORDER BY sy_start DESC";
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
    $sql = "SELECT id, YEAR(sy_start) AS sy_start, YEAR(sy_end) AS sy_end, is_active FROM tbl_academicyears WHERE id = ? LIMIT 1";
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
    $start = (int)$sy_start;
    $end = (int)$sy_end;
    $active = $is_active ? 1 : 0;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    if ($start <= 0 || $end <= 0) return ['success' => false, 'error' => 'Invalid year values.'];
    if ($start >= $end) return ['success' => false, 'error' => 'Start year must be less than end year.'];

    // If activating this session, deactivate others
    if ($active) {
        $upd = "UPDATE tbl_academicyears SET is_active = 0 WHERE is_active = 1";
        mysqli_query($conn, $upd);
    }

    $start_date = sprintf('%04d-01-01', $start);
    $end_date = sprintf('%04d-01-01', $end);
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
            $sy_start = intval(substr($sy_start_raw, 0, 4));
            $sy_end = intval(substr($sy_end_raw, 0, 4));
            $status = $_POST['session_status'] ?? 'Inactive';
            $is_active = ($status === 'Active') ? true : false;
            $res = add_session($conn, $sy_start, $sy_end, $is_active);
            if (!empty($res['success']) && !empty($res['id'])) {
                // return created session data
                echo json_encode(['success' => true, 'id' => (int)$res['id'], 'data' => ['id' => (int)$res['id'], 'sy_start' => $sy_start, 'sy_end' => $sy_end, 'is_active' => $is_active ? 1 : 0]]);
                exit;
            }
            echo json_encode($res);
            exit;
        }

        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $sy_start = isset($_POST['sy_start']) ? intval(substr($_POST['sy_start'],0,4)) : 0;
            $sy_end = isset($_POST['sy_end']) ? intval(substr($_POST['sy_end'],0,4)) : 0;
            $status = $_POST['session_status'] ?? 'Inactive';
            $is_active = ($status === 'Active') ? true : false;
            $res = update_session($conn, $id, $sy_start, $sy_end, $is_active);
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
