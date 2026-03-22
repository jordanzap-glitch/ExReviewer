<?php
/**
 * Add a question to tbl_question_bank
 *
 * @param mysqli $conn
 * @param string $question
 * @param string $opt_a
 * @param string $opt_b
 * @param string $opt_c
 * @param string $opt_d
 * @param string $correct_ans  One of 'A','B','C','D'
 * @param string $subject_name  Subject name (will be resolved to subject_id)
 * @param int|null $academicyear_id  Academic year id (optional)
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_question($conn, $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_name, $academicyear_id = null, $remarks = '')
{
    $question = trim($question ?? '');
    $opt_a = trim($opt_a ?? '');
    $opt_b = trim($opt_b ?? '');
    $opt_c = trim($opt_c ?? '');
    $opt_d = trim($opt_d ?? '');
    $correct_ans = strtoupper(trim($correct_ans ?? ''));
    $subject_name = trim($subject_name ?? '');
    $remarks = trim($remarks ?? '');
    $academicyear_id = $academicyear_id !== null ? (int)$academicyear_id : null;

    if ($question === '' || $opt_a === '' || $opt_b === '' || $opt_c === '' || $opt_d === '') {
        return ['success' => false, 'error' => 'Question and all options are required.'];
    }
    if (!in_array($correct_ans, ['A','B','C','D'], true)) {
        return ['success' => false, 'error' => 'Correct answer must be A, B, C or D.'];
    }
    if ($subject_name === '') {
        return ['success' => false, 'error' => 'Subject name is required.'];
    }

    // Resolve subject name to id
    $subject_id = null;
    $sql = "SELECT id FROM tbl_subjects WHERE `name` = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $subject_name);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            $subject_id = (int)$row['id'];
        }
    }

    if (empty($subject_id)) {
        return ['success' => false, 'error' => 'Subject not found: ' . $subject_name];
    }

    $insertSql = "INSERT INTO tbl_question_bank (question, opt_a, opt_b, opt_c, opt_d, correct_ans, subjects_id, academicyears_id, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $ins = mysqli_prepare($conn, $insertSql);
    if (!$ins) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }

    mysqli_stmt_bind_param($ins, 'ssssssiis', $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_id, $academicyear_id, $remarks);
    if (mysqli_stmt_execute($ins)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }

    return ['success' => false, 'error' => 'Database error (insert failed).'];
}

/**
 * Fetch a question with related subject name and academic year by id
 *
 * @param mysqli $conn
 * @param int $id
 * @return array ['success'=>bool, 'data'=>array|null, 'error'=>string|null]
 */
function get_question($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) {
        return ['success' => false, 'error' => 'Invalid question id.'];
    }

    $sql = "SELECT qb.id, qb.question, qb.opt_a, qb.opt_b, qb.opt_c, qb.opt_d, qb.correct_ans, qb.remarks, qb.subjects_id, qb.academicyears_id, s.name AS subject_name, ay.sy_start, ay.sy_end FROM tbl_question_bank qb LEFT JOIN tbl_subjects s ON qb.subjects_id = s.id LEFT JOIN tbl_academicyears ay ON qb.academicyears_id = ay.id WHERE qb.id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        return ['success' => false, 'error' => 'Database error (execute failed).'];
    }
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        return ['success' => false, 'error' => 'Question not found.'];
    }

    // Normalize year display
    $year = '';
    if (!empty($row['sy_start']) || !empty($row['sy_end'])) {
        $year = trim(($row['sy_start'] ?? '') . ' - ' . ($row['sy_end'] ?? ''));
    }

    $data = [
        'id' => (int)$row['id'],
        'question' => $row['question'] ?? '',
        'opt_a' => $row['opt_a'] ?? '',
        'opt_b' => $row['opt_b'] ?? '',
        'opt_c' => $row['opt_c'] ?? '',
        'opt_d' => $row['opt_d'] ?? '',
        'correct_ans' => $row['correct_ans'] ?? '',
        'remarks' => $row['remarks'] ?? '',
        'subject_name' => $row['subject_name'] ?? '',
        'academicyear' => $year,
        'academicyear_id' => isset($row['academicyears_id']) ? (int)$row['academicyears_id'] : null,
    ];

    return ['success' => true, 'data' => $data];
}


/**
 * Return question data as a structured array (wrapper around get_question())
 *
 * @param mysqli $conn
 * @param int $id
 * @return array JSON-serializable response with keys: success, data, error
 */
function view_question_json($conn, $id)
{
    $id = (int)$id;
    return get_question($conn, $id);
}

// Lightweight JSON endpoint: call this file directly with ?action=view&id=NN
if (php_sapi_name() !== 'cli' && isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    // Ensure DB connection exists; try to include if missing (works when file is requested directly)
    if (!isset($conn) || !$conn) {
        @include_once __DIR__ . '/../../../db/dbcon.php';
    }

    header('Content-Type: application/json; charset=utf-8');
    $id = (int)$_GET['id'];
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Invalid or missing id']);
        exit;
    }

    if (!isset($conn) || !$conn) {
        echo json_encode(['success' => false, 'error' => 'Database connection not available']);
        exit;
    }

    $res = view_question_json($conn, $id);
    echo json_encode($res);
    exit;
}

/**
 * Update an existing question by id
 *
 * @param mysqli $conn
 * @param int $id
 * @param string $question
 * @param string $opt_a
 * @param string $opt_b
 * @param string $opt_c
 * @param string $opt_d
 * @param string $correct_ans
 * @param string $subject_name
 * @param int|null $academicyear_id
 * @param string $remarks
 * @return array
 */
function update_question($conn, $id, $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_name, $academicyear_id = null, $remarks = '')
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];

    $question = trim($question ?? '');
    $opt_a = trim($opt_a ?? '');
    $opt_b = trim($opt_b ?? '');
    $opt_c = trim($opt_c ?? '');
    $opt_d = trim($opt_d ?? '');
    $correct_ans = strtoupper(trim($correct_ans ?? ''));
    $subject_name = trim($subject_name ?? '');
    $remarks = trim($remarks ?? '');
    $academicyear_id = $academicyear_id !== null ? (int)$academicyear_id : null;

    if ($question === '' || $opt_a === '' || $opt_b === '' || $opt_c === '' || $opt_d === '') {
        return ['success' => false, 'error' => 'Question and all options are required.'];
    }
    if (!in_array($correct_ans, ['A','B','C','D'], true)) {
        return ['success' => false, 'error' => 'Correct answer must be A, B, C or D.'];
    }
    if ($subject_name === '') {
        return ['success' => false, 'error' => 'Subject name is required.'];
    }

    // Resolve subject id
    $subject_id = null;
    $sql = "SELECT id FROM tbl_subjects WHERE `name` = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $subject_name);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            $subject_id = (int)$row['id'];
        }
    }
    if (empty($subject_id)) {
        return ['success' => false, 'error' => 'Subject not found: ' . $subject_name];
    }

    $sql = "UPDATE tbl_question_bank SET question = ?, opt_a = ?, opt_b = ?, opt_c = ?, opt_d = ?, correct_ans = ?, subjects_id = ?, academicyears_id = ?, remarks = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];

    // bind: 6 strings, subject id int, academicyear int, remarks string, id int
    mysqli_stmt_bind_param($stmt, 'ssssssiisi', $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_id, $academicyear_id, $remarks, $id);
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => 'Database error (update failed).'];
}

// JSON POST endpoint to update question: POST action=update
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!isset($conn) || !$conn) {
        @include_once __DIR__ . '/../../../db/dbcon.php';
    }
    // Try to include init for CSRF helper if available
    if (!function_exists('verify_csrf_token')) {
        @include_once __DIR__ . '/../includes/init.php';
    }

    header('Content-Type: application/json; charset=utf-8');

    $token = $_POST['csrf_token'] ?? '';
    if (function_exists('verify_csrf_token') && !verify_csrf_token($token)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $question = $_POST['question'] ?? '';
    $a = $_POST['opt_a'] ?? '';
    $b = $_POST['opt_b'] ?? '';
    $c = $_POST['opt_c'] ?? '';
    $d = $_POST['opt_d'] ?? '';
    $correct = $_POST['correct'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $year = $_POST['year'] ?? null;
    $remarks = $_POST['remarks'] ?? '';

    if (!isset($conn) || !$conn) {
        echo json_encode(['success' => false, 'error' => 'Database connection not available']);
        exit;
    }

    $res = update_question($conn, $id, $question, $a, $b, $c, $d, $correct, $subject, $year, $remarks);
    echo json_encode($res);
    exit;
}


/**
 * Delete a question by id
 *
 * @param mysqli $conn
 * @param int $id
 * @return array
 */
function delete_question($conn, $id)
{
    $id = (int)$id;
    if ($id <= 0) return ['success' => false, 'error' => 'Invalid id'];

    $sql = "DELETE FROM tbl_question_bank WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true];
        return ['success' => false, 'error' => 'Question not found or already deleted'];
    }
    return ['success' => false, 'error' => 'Database error (delete failed).'];
}

// JSON POST endpoint to delete question: POST action=delete
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!isset($conn) || !$conn) {
        @include_once __DIR__ . '/../../../db/dbcon.php';
    }
    header('Content-Type: application/json; charset=utf-8');

    // CSRF check if available
    $token = $_POST['csrf_token'] ?? '';
    if (function_exists('verify_csrf_token') && !verify_csrf_token($token)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid id']);
        exit;
    }
    if (!isset($conn) || !$conn) {
        echo json_encode(['success' => false, 'error' => 'Database connection not available']);
        exit;
    }

    $res = delete_question($conn, $id);
    echo json_encode($res);
    exit;
}


?>