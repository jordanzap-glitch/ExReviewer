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
 * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
 */
function add_question($conn, $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_name)
{
    $question = trim($question ?? '');
    $opt_a = trim($opt_a ?? '');
    $opt_b = trim($opt_b ?? '');
    $opt_c = trim($opt_c ?? '');
    $opt_d = trim($opt_d ?? '');
    $correct_ans = strtoupper(trim($correct_ans ?? ''));
    $subject_name = trim($subject_name ?? '');

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

    $insertSql = "INSERT INTO tbl_question_bank (question, opt_a, opt_b, opt_c, opt_d, correct_ans, subjects_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $ins = mysqli_prepare($conn, $insertSql);
    if (!$ins) {
        return ['success' => false, 'error' => 'Database error (prepare failed).'];
    }

    mysqli_stmt_bind_param($ins, 'ssssssi', $question, $opt_a, $opt_b, $opt_c, $opt_d, $correct_ans, $subject_id);
    if (mysqli_stmt_execute($ins)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }

    return ['success' => false, 'error' => 'Database error (insert failed).'];
}

?>