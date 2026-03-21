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

?>