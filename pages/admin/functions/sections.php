<?php
/**
 * Add a section into tbl_sections
 * @param mysqli $conn
 * @param string $name
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_section($conn, $name)
{
    $name = trim($name ?? '');
    if ($name === '') {
        return ['success' => false, 'error' => 'Section name is required.'];
    }

    // check uniqueness
    $chkSql = "SELECT id FROM tbl_sections WHERE name = ? LIMIT 1";
    $chk = mysqli_prepare($conn, $chkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 's', $name);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            return ['success' => false, 'error' => 'A section with the same name already exists.'];
        }
        mysqli_stmt_close($chk);
    }

    $sql = "INSERT INTO tbl_sections (`name`) VALUES (?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }
    mysqli_stmt_bind_param($stmt, 's', $name);
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }
    return ['success' => false, 'error' => 'Database error (insert failed).'];
}

/**
 * Get all sections
 */
function get_sections_list($conn)
{
    $rows = [];
    $res = mysqli_query($conn, "SELECT id, name FROM tbl_sections ORDER BY name");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    return $rows;
}


/**
 * Fetch a single section by id
 */
function get_section($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "SELECT id, name FROM tbl_sections WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) return ['success' => false, 'error' => 'Database error (execute failed).'];
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) return ['success' => false, 'error' => 'Section not found.'];
    return ['success' => true, 'data' => ['id' => (int)$row['id'], 'name' => $row['name']]];
}


/**
 * Update section
 */
function update_section($conn, $id, $name)
{
    $id = (int)$id;
    $name = trim($name ?? '');
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    if ($name === '') return ['success' => false, 'error' => 'Section name is required.'];

    // uniqueness check
    $chkSql = "SELECT id FROM tbl_sections WHERE name = ? AND id <> ? LIMIT 1";
    $chk = mysqli_prepare($conn, $chkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'si', $name, $id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            return ['success' => false, 'error' => 'A section with the same name already exists.'];
        }
        mysqli_stmt_close($chk);
    }

    $sql = "UPDATE tbl_sections SET name = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'si', $name, $id);
    if (mysqli_stmt_execute($stmt)) return ['success' => true];
    return ['success' => false, 'error' => 'Database error (update failed).'];
}


/**
 * Delete section
 */
function delete_section($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "DELETE FROM tbl_sections WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true];
        return ['success' => false, 'error' => 'Section not found or already deleted'];
    }
    return ['success' => false, 'error' => 'Database error (delete failed).'];
}


// Lightweight JSON endpoints: view (GET), add/update/delete (POST)
if (php_sapi_name() !== 'cli') {
    if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
        if (!isset($conn) || !$conn) {
            @include_once __DIR__ . '/../../../db/dbcon.php';
        }
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$_GET['id'];
        if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }
        if (!isset($conn) || !$conn) { echo json_encode(['success' => false, 'error' => 'Database connection not available']); exit; }
        $res = get_section($conn, $id);
        echo json_encode($res);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add','update','delete'], true)) {
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
        if ($action === 'add') {
            $name = $_POST['section_name'] ?? ($_POST['name'] ?? '');
            $res = add_section($conn, $name);
            if (!empty($res['success']) && !empty($res['id'])) {
                echo json_encode(['success' => true, 'id' => (int)$res['id'], 'data' => ['id' => (int)$res['id'], 'name' => $name]]);
                exit;
            }
            echo json_encode($res);
            exit;
        }

        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = $_POST['name'] ?? '';
            $res = update_section($conn, $id, $name);
            echo json_encode($res);
            exit;
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $res = delete_section($conn, $id);
            echo json_encode($res);
            exit;
        }
    }
}

?>