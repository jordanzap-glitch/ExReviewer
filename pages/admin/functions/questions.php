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
 * Update a question in tbl_question_bank
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
 * @return array ['success'=>bool, 'error'=>string|null]
 */
function update_question($conn, $id, $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_name, $academicyear_id = null, $remarks = '')
{
    $id = (int)$id;
    if ($id <= 0) {
        return ['success' => false, 'error' => 'Invalid question id.'];
    }

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

    // Build update SQL, handle nullable academicyear
    if ($academicyear_id === null) {
        $sql = "UPDATE tbl_question_bank SET question = ?, opt_a = ?, opt_b = ?, opt_c = ?, opt_d = ?, correct_ans = ?, subjects_id = ?, academicyears_id = NULL, remarks = ? WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
        // types: question(s), opt_a(s), opt_b(s), opt_c(s), opt_d(s), correct_ans(s), subject_id(i), remarks(s), id(i)
        mysqli_stmt_bind_param($stmt, 'ssssssisi', $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_id, $remarks, $id);
    } else {
        $sql = "UPDATE tbl_question_bank SET question = ?, opt_a = ?, opt_b = ?, opt_c = ?, opt_d = ?, correct_ans = ?, subjects_id = ?, academicyears_id = ?, remarks = ? WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return ['success' => false, 'error' => 'Database error (prepare failed).'];
        // types: question(s), opt_a(s), opt_b(s), opt_c(s), opt_d(s), correct_ans(s), subject_id(i), academicyear_id(i), remarks(s), id(i)
        mysqli_stmt_bind_param($stmt, 'ssssssiisi', $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_id, $academicyear_id, $remarks, $id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        return ['success' => false, 'error' => 'Database error (execute failed).'];
    }

    return ['success' => true];
}

?>
?>