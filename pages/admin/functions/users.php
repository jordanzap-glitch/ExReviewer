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

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // determine default user type id (Student) if available
    $usertype_id = null;
    $ut = mysqli_prepare($conn, "SELECT id FROM tbl_usertypes WHERE name = 'Student' LIMIT 1");
    if ($ut) {
        mysqli_stmt_execute($ut);
        mysqli_stmt_bind_result($ut, $utid);
        if (mysqli_stmt_fetch($ut)) $usertype_id = (int)$utid;
        mysqli_stmt_close($ut);
    }

    // prepare insert including usertypes_id
    $ins = mysqli_prepare($conn, "INSERT INTO tbl_users (last_name, first_name, middle_name, email, password, year_level, sections_id, academicyears_id, usertypes_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$ins) return ['success' => false, 'error' => 'Failed to prepare insert statement'];
    mysqli_stmt_bind_param($ins, 'ssssssiii', $last, $first, $middle, $email, $hashed, $year_level, $section_id, $academicyears_id, $usertype_id);
    if (mysqli_stmt_execute($ins)) {
        mysqli_stmt_close($ins);
        return ['success' => true];
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

?>