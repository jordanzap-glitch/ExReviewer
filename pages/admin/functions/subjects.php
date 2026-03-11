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

?>
