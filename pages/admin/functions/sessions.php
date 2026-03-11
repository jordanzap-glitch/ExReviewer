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

?>
