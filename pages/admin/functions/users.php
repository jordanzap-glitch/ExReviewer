<?php
/**
 * Admin users helper functions
 */

function get_sections($conn) {
    $rows = [];
    $res = mysqli_query($conn, "SELECT id, name FROM tbl_sections ORDER BY name");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    return $rows;
}

function get_active_academicyears($conn) {
    $rows = [];
    $res = mysqli_query($conn, "SELECT id, sy_start, sy_end FROM tbl_academicyears WHERE is_active = 1 ORDER BY sy_start DESC");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    return $rows;
}

/**
 * Get all usertypes
 */
function get_usertypes($conn) {
    $rows = [];
    $res = mysqli_query($conn, "SELECT id, name FROM tbl_usertypes ORDER BY name");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    return $rows;
}

/**
 * Add a user to tbl_users.
 * Expects $data array with keys: last_name, first_name, middle_name, email, password, year_level, section_id, academicyears_id
 */
function add_user($conn, array $data) {
    $required = ['last_name','first_name','email','password','year_level','section_id','academicyears_id'];
    foreach ($required as $k) {
        if (empty($data[$k]) && $data[$k] !== '0') {
            return ['success' => false, 'error' => 'Missing required field: ' . $k];
        }
    }

    // normalize
    $last = trim($data['last_name']);
    $first = trim($data['first_name']);
    $middle = trim($data['middle_name'] ?? '');
    $email = trim($data['email']);
    $password = $data['password'];
    $year_level = $data['year_level'];
    $section_id = (int)$data['section_id'];
    $academicyears_id = (int)$data['academicyears_id'];

    // simple email check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email address'];
    }

    // check existing email
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE email = ? LIMIT 1");
    if (!$stmt) return ['success' => false, 'error' => 'Database error'];
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return ['success' => false, 'error' => 'Email already registered'];
    }
    mysqli_stmt_close($stmt);

    // store password as provided (no hashing)
    $hashed = $password;
    // determine user type id: prefer provided value, fallback to 'Student' default
    $usertype_id = 0;
    if (isset($data['usertypes_id']) && (int)$data['usertypes_id'] > 0) {
        $usertype_id = (int)$data['usertypes_id'];
    } else {
        $ut = mysqli_prepare($conn, "SELECT id FROM tbl_usertypes WHERE name = 'Student' LIMIT 1");
        if ($ut) {
            mysqli_stmt_execute($ut);
            mysqli_stmt_bind_result($ut, $utid);
            if (mysqli_stmt_fetch($ut)) $usertype_id = (int)$utid;
            mysqli_stmt_close($ut);
        }
    }

    // prepare insert including usertypes_id
    $ins = mysqli_prepare($conn, "INSERT INTO tbl_users (last_name, first_name, middle_name, email, password, year_level, sections_id, academicyears_id, usertypes_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$ins) return ['success' => false, 'error' => 'Failed to prepare insert statement'];
    mysqli_stmt_bind_param($ins, 'ssssssiii', $last, $first, $middle, $email, $hashed, $year_level, $section_id, $academicyears_id, $usertype_id);
    if (mysqli_stmt_execute($ins)) {
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($ins);
        $full_name = trim($first . ' ' . ($middle !== '' ? ($middle . ' ') : '') . $last);
        $role = null;
        if (!empty($usertype_id)) {
            $r = mysqli_prepare($conn, "SELECT name FROM tbl_usertypes WHERE id = ? LIMIT 1");
            if ($r) {
                mysqli_stmt_bind_param($r, 'i', $usertype_id);
                mysqli_stmt_execute($r);
                $resr = mysqli_stmt_get_result($r);
                if ($rowr = mysqli_fetch_assoc($resr)) $role = $rowr['name'];
                mysqli_stmt_close($r);
            }
        }
        return ['success' => true, 'id' => (int)$new_id, 'full_name' => $full_name, 'email' => $email, 'role' => $role];
    }
    $err = mysqli_error($conn);
    mysqli_stmt_close($ins);
    return ['success' => false, 'error' => $err ?: 'Insert failed'];
}

/**
 * Get users for admin table
 * Returns array of rows with keys: id, full_name, email, role
 */
function get_users($conn) {
    $rows = [];
    $sql = "SELECT u.id, TRIM(CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name)) AS full_name, u.email, ut.name AS role FROM tbl_users u LEFT JOIN tbl_usertypes ut ON u.usertypes_id = ut.id ORDER BY u.last_name, u.first_name";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    return $rows;
}


/**
 * Fetch a single user by id
 * @param mysqli $conn
 * @param int $id
 * @return array ['success'=>bool, 'data'=>array|null, 'error'=>string|null]
 */
function get_user($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "SELECT u.id, u.last_name, u.first_name, u.middle_name, u.email, u.year_level, u.sections_id, u.academicyears_id, u.usertypes_id,
                   s.name AS section_name, ay.sy_start, ay.sy_end
            FROM tbl_users u
            LEFT JOIN tbl_sections s ON u.sections_id = s.id
            LEFT JOIN tbl_academicyears ay ON u.academicyears_id = ay.id
            WHERE u.id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) return ['success' => false, 'error' => 'Database error (execute failed).'];
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) return ['success' => false, 'error' => 'User not found.'];
    return ['success' => true, 'data' => $row];
}


/**
 * Update an existing user
 * @param mysqli $conn
 * @param int $id
 * @param array $data (keys: last_name, first_name, middle_name, email, password(optional), year_level, section_id, academicyears_id)
 * @return array ['success'=>bool, 'error'=>string|null]
 */
function update_user($conn, $id, array $data)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];

    $last = trim($data['last_name'] ?? '');
    $first = trim($data['first_name'] ?? '');
    $middle = trim($data['middle_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $year_level = $data['year_level'] ?? '';
    $section_id = isset($data['section_id']) ? (int)$data['section_id'] : 0;
    $academicyears_id = isset($data['academicyears_id']) ? (int)$data['academicyears_id'] : 0;
    $usertypes_id = isset($data['usertypes_id']) ? (int)$data['usertypes_id'] : 0;

    if ($last === '' || $first === '' || $email === '') return ['success' => false, 'error' => 'Required fields missing.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['success' => false, 'error' => 'Invalid email address'];

    // check uniqueness of email excluding current id
    $chk = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE email = ? AND id <> ? LIMIT 1");
    if ($chk) {
        mysqli_stmt_bind_param($chk, 'si', $email, $id);
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        if ($row = mysqli_fetch_assoc($res)) {
            return ['success' => false, 'error' => 'Email already in use by another user.'];
        }
    }

    // build update SQL; include password only when provided
    $password = $data['password'] ?? '';
    if ($password !== '') {
        // store password as provided (no hashing)
        $hashed = $password;
        $sql = "UPDATE tbl_users SET last_name = ?, first_name = ?, middle_name = ?, email = ?, password = ?, year_level = ?, sections_id = ?, academicyears_id = ?, usertypes_id = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
        mysqli_stmt_bind_param($stmt, 'ssssssiiii', $last, $first, $middle, $email, $hashed, $year_level, $section_id, $academicyears_id, $usertypes_id, $id);
    } else {
        $sql = "UPDATE tbl_users SET last_name = ?, first_name = ?, middle_name = ?, email = ?, year_level = ?, sections_id = ?, academicyears_id = ?, usertypes_id = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
        mysqli_stmt_bind_param($stmt, 'sssssiiii', $last, $first, $middle, $email, $year_level, $section_id, $academicyears_id, $usertypes_id, $id);
    }

    if (mysqli_stmt_execute($stmt)) return ['success' => true];
    return ['success' => false, 'error' => 'Database error (update failed).'];
}


/**
 * Delete a user by id
 * @param mysqli $conn
 * @param int $id
 * @return array ['success'=>bool, 'error'=>string|null]
 */
function delete_user($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];
    $sql = "DELETE FROM tbl_users WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true];
        return ['success' => false, 'error' => 'User not found or already deleted'];
    }
    return ['success' => false, 'error' => 'Database error (delete failed).'];
}


// Lightweight JSON endpoints: view (GET), update/delete (POST)
if (php_sapi_name() !== 'cli') {
    if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
        if (!isset($conn) || !$conn) {
            @include_once __DIR__ . '/../../../db/dbcon.php';
        }
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$_GET['id'];
        if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }
        if (!isset($conn) || !$conn) { echo json_encode(['success' => false, 'error' => 'Database connection not available']); exit; }
        $res = get_user($conn, $id);
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
            // resolve section id if name posted
            $posted_section = trim($_POST['section_id'] ?? '');
            $resolved_section_id = 0;
            if ($posted_section !== '') {
                if (is_numeric($posted_section)) {
                    $resolved_section_id = (int)$posted_section;
                } else {
                    $chk = mysqli_prepare($conn, "SELECT id FROM tbl_sections WHERE name = ? LIMIT 1");
                    if ($chk) {
                        mysqli_stmt_bind_param($chk, 's', $posted_section);
                        mysqli_stmt_execute($chk);
                        $res_chk = mysqli_stmt_get_result($chk);
                        if ($row_chk = mysqli_fetch_assoc($res_chk)) {
                            $resolved_section_id = (int)$row_chk['id'];
                        }
                        mysqli_stmt_close($chk);
                    }
                }
            }
            $data = [
                'last_name' => $_POST['lastname'] ?? '',
                'first_name' => $_POST['firstname'] ?? '',
                'middle_name' => $_POST['middlename'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'year_level' => $_POST['year_level'] ?? '',
                'section_id' => $resolved_section_id,
                'academicyears_id' => isset($_POST['academicyears_id']) ? (int)$_POST['academicyears_id'] : 0,
                'usertypes_id' => isset($_POST['usertypes_id']) ? (int)$_POST['usertypes_id'] : 0,
            ];
            $res = add_user($conn, $data);
            echo json_encode($res);
            exit;
        }

        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $data = [
                'last_name' => $_POST['last_name'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'year_level' => $_POST['year_level'] ?? '',
                'section_id' => isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0,
                'academicyears_id' => isset($_POST['academicyears_id']) ? (int)$_POST['academicyears_id'] : 0,
                'usertypes_id' => isset($_POST['usertypes_id']) ? (int)$_POST['usertypes_id'] : 0,
            ];
            $res = update_user($conn, $id, $data);
            echo json_encode($res);
            exit;
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $res = delete_user($conn, $id);
            echo json_encode($res);
            exit;
        }
    }
}

?>