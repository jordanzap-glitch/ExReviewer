<?php
/**
 * Insert a new subject into tbl_subjects
 * @param mysqli $conn
 * @param string $name
 * @param string $code
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_subject($conn, $name, $code)
{
    $name = trim($name ?? '');
    $code = trim($code ?? '');
    if ($name === '' || $code === '') {
        return ['success' => false, 'error' => 'Name and Code are required.'];
    }

    // Check uniqueness (optional)
    $checkSql = "SELECT id FROM tbl_subjects WHERE code = ? OR name = ? LIMIT 1";
    $chk = mysqli_prepare($conn, $checkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'ss', $code, $name);
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        if ($row = mysqli_fetch_assoc($res)) {
            return ['success' => false, 'error' => 'A subject with the same name or code already exists.'];
        }
    }

    $sql = "INSERT INTO tbl_subjects (`name`, `code`) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }
    mysqli_stmt_bind_param($stmt, 'ss', $name, $code);
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }
    return ['success' => false, 'error' => 'Database error (insert failed).'];
}

?>

<?php
/**
 * Get all subjects from tbl_subjects
 * @param mysqli $conn
 * @return array List of subjects as associative arrays
 */
function get_subjects($conn)
{
    $sql = "SELECT id, `name`, `code` FROM tbl_subjects ORDER BY `name` ASC";
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
 * Fetch a single subject by id
 *
 * @param mysqli $conn
 * @param int $id
 * @return array ['success'=>bool, 'data'=>array|null, 'error'=>string|null]
 */
function get_subject($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];

    $sql = "SELECT id, `name`, `code` FROM tbl_subjects WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) return ['success' => false, 'error' => 'Database error (execute failed).'];
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) return ['success' => false, 'error' => 'Subject not found.'];
    return ['success' => true, 'data' => ['id' => (int)$row['id'], 'name' => $row['name'], 'code' => $row['code']]];
}


/**
 * Update an existing subject
 *
 * @param mysqli $conn
 * @param int $id
 * @param string $name
 * @param string $code
 * @return array ['success'=>bool, 'error'=>string|null]
 */
function update_subject($conn, $id, $name, $code)
{
    $id = (int)$id;
    $name = trim($name ?? '');
    $code = trim($code ?? '');
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    if ($name === '' || $code === '') return ['success' => false, 'error' => 'Name and Code are required.'];

    // check uniqueness excluding current id
    $checkSql = "SELECT id FROM tbl_subjects WHERE (code = ? OR name = ?) AND id <> ? LIMIT 1";
    $chk = mysqli_prepare($conn, $checkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'ssi', $code, $name, $id);
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        if ($row = mysqli_fetch_assoc($res)) {
            return ['success' => false, 'error' => 'A subject with the same name or code already exists.'];
        }
    }

    $sql = "UPDATE tbl_subjects SET `name` = ?, `code` = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $code, $id);
    if (mysqli_stmt_execute($stmt)) return ['success' => true];
    return ['success' => false, 'error' => 'Database error (update failed).'];
}


/**
 * Delete a subject by id
 *
 * @param mysqli $conn
 * @param int $id
 * @return array
 */
function delete_subject($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "DELETE FROM tbl_subjects WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true];
        return ['success' => false, 'error' => 'Subject not found or already deleted'];
    }
    return ['success' => false, 'error' => 'Database error (delete failed).'];
}


// Lightweight JSON endpoints: view (GET), update/delete (POST)
if (php_sapi_name() !== 'cli') {
    // view via GET ?action=view&id=NN
    if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
        if (!isset($conn) || !$conn) {
            @include_once __DIR__ . '/../../../db/dbcon.php';
        }
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$_GET['id'];
        if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }
        if (!isset($conn) || !$conn) { echo json_encode(['success' => false, 'error' => 'Database connection not available']); exit; }
        $res = get_subject($conn, $id);
        echo json_encode($res);
        exit;
    }

    // POST handlers for update and delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['update','delete'], true)) {
        if (!isset($conn) || !$conn) {
            @include_once __DIR__ . '/../../../db/dbcon.php';
        }
        header('Content-Type: application/json; charset=utf-8');

        // CSRF check if helper exists
        $token = $_POST['csrf_token'] ?? '';
        if (function_exists('verify_csrf_token') && !verify_csrf_token($token)) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        if (!isset($conn) || !$conn) { echo json_encode(['success' => false, 'error' => 'Database connection not available']); exit; }

        $action = $_POST['action'];
        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $res = update_subject($conn, $id, $name, $code);
            echo json_encode($res);
            exit;
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $res = delete_subject($conn, $id);
            echo json_encode($res);
            exit;
        }
    }
}

?>
