<?php
/**
 * Module helper functions
 */

/**
 * Add a module into tbl_modules
 * @param mysqli $conn
 * @param string $name
 * @param int|null $section_id
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_module($conn, $file_path, $subjects_id = null)
{
    $file_path = trim($file_path ?? '');
    $subjects_id = $subjects_id !== null ? (int)$subjects_id : null;
    if ($file_path === '') {
        return ['success' => false, 'error' => 'Module file path is required.'];
    }
    // optional uniqueness within same subject (by file_path)
    $chkSql = "SELECT id FROM tbl_modules WHERE file_path = ?";
    if ($subjects_id) $chkSql .= " AND subjects_id = ?";
    $chkSql .= " LIMIT 1";
    $chk = mysqli_prepare($conn, $chkSql);
    if ($chk) {
        if ($subjects_id) mysqli_stmt_bind_param($chk, 'si', $file_path, $subjects_id);
        else mysqli_stmt_bind_param($chk, 's', $file_path);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            return ['success' => false, 'error' => 'A module with the same name already exists.'];
        }
        mysqli_stmt_close($chk);
    }
    $sql = "INSERT INTO tbl_modules (`file_path`" . ($subjects_id ? ", subjects_id" : "") . ") VALUES (?" . ($subjects_id ? ", ?" : "") . ")";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }
    if ($subjects_id) mysqli_stmt_bind_param($stmt, 'si', $file_path, $subjects_id);
    else mysqli_stmt_bind_param($stmt, 's', $file_path);
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }
    return ['success' => false, 'error' => 'Database error (insert failed).'];
}

/**
 * Get all modules
 */
function get_modules_list($conn)
{
    $rows = [];
    $sql = "SELECT m.id, m.file_path, m.subjects_id, s.name AS subject_name FROM tbl_modules m LEFT JOIN tbl_subjects s ON m.subjects_id = s.id ORDER BY m.id DESC";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    return $rows;
}

/**
 * Fetch a single module by id
 */
function get_module($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "SELECT m.id, m.file_path, m.subjects_id, s.name AS subject_name FROM tbl_modules m LEFT JOIN tbl_subjects s ON m.subjects_id = s.id WHERE m.id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) return ['success' => false, 'error' => 'Database error (execute failed).'];
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) return ['success' => false, 'error' => 'Module not found.'];
    return ['success' => true, 'data' => ['id' => (int)$row['id'], 'file_path' => $row['file_path'], 'subjects_id' => isset($row['subjects_id']) ? (int)$row['subjects_id'] : null, 'subject_name' => $row['subject_name']]];
}

/**
 * Update module
 */
function update_module($conn, $id, $file_path, $subjects_id = null)
{
    $id = (int)$id;
    $file_path = trim($file_path ?? '');
    $subjects_id = $subjects_id !== null ? (int)$subjects_id : null;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    if ($file_path === '') return ['success' => false, 'error' => 'Module file path is required.'];

    // uniqueness check
    $chkSql = "SELECT id FROM tbl_modules WHERE file_path = ? AND id <> ? LIMIT 1";
    $chk = mysqli_prepare($conn, $chkSql);
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'si', $file_path, $id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            return ['success' => false, 'error' => 'A module with the same name already exists.'];
        }
        mysqli_stmt_close($chk);
    }
    $sql = "UPDATE tbl_modules SET file_path = ?, subjects_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'sii', $file_path, $subjects_id, $id);
    if (mysqli_stmt_execute($stmt)) return ['success' => true];
    return ['success' => false, 'error' => 'Database error (update failed).'];
}

/**
 * Delete module
 */
function delete_module($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "DELETE FROM tbl_modules WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true];
        return ['success' => false, 'error' => 'Module not found or already deleted'];
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
        $res = get_module($conn, $id);
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
                // handle uploaded file if present
                // subjects_id comes from POST
            $file_path = '';
            if (!empty($_FILES['module_file']) && empty($_FILES['module_file']['error'])) {
                $u = $_FILES['module_file'];
                $uploadDir = __DIR__ . '/../../student/learning_modules';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                $fname = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($u['name']));
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $fname;
                if (@move_uploaded_file($u['tmp_name'], $dest)) {
                    $file_path = 'pages/student/learning_modules/' . $fname;
                }
            }
            // fallback to posted file path
            if ($file_path === '') {
                $file_path = $_POST['file_path'] ?? $_POST['module_name'] ?? '';
            }
            $subjects_id = isset($_POST['subjects_id']) ? (int)$_POST['subjects_id'] : null;
            $res = add_module($conn, $file_path, $subjects_id);
            if (!empty($res['success']) && !empty($res['id'])) {
                echo json_encode(['success' => true, 'id' => (int)$res['id'], 'data' => ['id' => (int)$res['id'], 'file_path' => $file_path, 'subjects_id' => $subjects_id]]);
                exit;
            }
            echo json_encode($res);
            exit;
        }

        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $subjects_id = isset($_POST['subjects_id']) ? (int)$_POST['subjects_id'] : null;
            $file_path = '';
            if (!empty($_FILES['module_file']) && empty($_FILES['module_file']['error'])) {
                $u = $_FILES['module_file'];
                $uploadDir = __DIR__ . '/../../student/learning_modules';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                $fname = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($u['name']));
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $fname;
                if (@move_uploaded_file($u['tmp_name'], $dest)) {
                    $file_path = 'pages/student/learning_modules/' . $fname;
                }
            }
            if ($file_path === '') {
                // keep existing file path if not provided
                $file_path = $_POST['file_path'] ?? '';
                if ($file_path === '') {
                    $existing = get_module($conn, $id);
                    if (!empty($existing['success']) && !empty($existing['data']['file_path'])) $file_path = $existing['data']['file_path'];
                }
            }
            $res = update_module($conn, $id, $file_path, $subjects_id);
            if (!empty($res['success'])) {
                echo json_encode(['success' => true, 'data' => ['file_path' => $file_path]]);
            } else {
                echo json_encode($res);
            }
            exit;
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $res = delete_module($conn, $id);
            echo json_encode($res);
            exit;
        }
    }
}

?>
