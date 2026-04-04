<?php
header('Content-Type: text/html; charset=UTF-8');
// Minimal endpoint returning table rows for the rankings table.
include __DIR__ . '/../includes/init.php';
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../../../db/dbcon.php';
}

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
$subject_where = '';
if ($subject_id) {
    $subject_where = "AND subjects_id = " . $subject_id;
}

$rank_sql = "SELECT u.id, u.first_name, u.last_name, u.image_path,
        (SELECT at.score FROM tbl_attempts at JOIN tbl_subjects st ON st.id = at.subjects_id WHERE at.user_id = u.id " . $subject_where . " ORDER BY (at.score / NULLIF(st.question_items,0)) DESC LIMIT 1) AS best_score,
        (SELECT st2.question_items FROM tbl_attempts at2 JOIN tbl_subjects st2 ON st2.id = at2.subjects_id WHERE at2.user_id = u.id " . $subject_where . " ORDER BY (at2.score / NULLIF(st2.question_items,0)) DESC LIMIT 1) AS q_items,
        (SELECT at3.date_created FROM tbl_attempts at3 JOIN tbl_subjects st3 ON st3.id = at3.subjects_id WHERE at3.user_id = u.id " . $subject_where . " ORDER BY (at3.score / NULLIF(st3.question_items,0)) DESC LIMIT 1) AS last_exam
        FROM tbl_users u
        WHERE EXISTS (SELECT 1 FROM tbl_attempts a WHERE a.user_id = u.id " . ($subject_id ? "AND subjects_id = " . $subject_id : "") . ")
        ORDER BY ( (SELECT atx.score FROM tbl_attempts atx JOIN tbl_subjects stx ON stx.id = atx.subjects_id WHERE atx.user_id = u.id " . $subject_where . " ORDER BY (atx.score / NULLIF(stx.question_items,0)) DESC LIMIT 1) / NULLIF((SELECT sty.question_items FROM tbl_attempts aty JOIN tbl_subjects sty ON sty.id = aty.subjects_id WHERE aty.user_id = u.id " . $subject_where . " ORDER BY (aty.score / NULLIF(sty.question_items,0)) DESC LIMIT 1),0) ) DESC
        LIMIT 5";

$rank_res = mysqli_query($conn, $rank_sql);
$rank_counter = 0;
$display_rank = 0;
$prev_percent = null;
if ($rank_res && mysqli_num_rows($rank_res) > 0) {
    while ($r = mysqli_fetch_assoc($rank_res)) {
        $rank_counter++;
        $full_name = trim($r['first_name'] . ' ' . $r['last_name']);
        if (!empty($r['image_path'])) {
            $avatar = '../../assets/images/auth/avatar/' . ltrim(basename($r['image_path']), '/');
        } else {
            $avatar = '../../assets/images/avatar/default.png';
        }
        $last_exam = $r['last_exam'] ? date('d M, Y', strtotime($r['last_exam'])) : 'N/A';
        $score = isset($r['best_score']) ? (int)$r['best_score'] : 0;
        $q_items = isset($r['q_items']) ? (int)$r['q_items'] : 0;
        if ($q_items > 0) {
            $percent = (int) round(($score / $q_items) * 100);
        } else {
            $percent = 0;
        }

        if ($prev_percent !== null && $percent === $prev_percent) {
            // keep $display_rank unchanged for ties
        } else {
            $display_rank = $rank_counter;
        }
        $prev_percent = $percent;

        $badge_class = 'bg-soft-primary text-primary';
        if ($display_rank == 1) $badge_class = 'bg-soft-success text-success';
        elseif ($display_rank == 2) $badge_class = 'bg-soft-warning text-warning';
        elseif ($display_rank == 3) $badge_class = 'bg-soft-primary text-primary';
        elseif ($display_rank == 4) $badge_class = 'bg-soft-danger text-danger';

        echo "<tr>\n";
        echo "<td class=\"position-relative\">\n";
        echo "  <div class=\"ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 rounded\" style=\"border-color:transparent\"></div>\n";
        echo "  <a href=\"javascript:void(0);\">" . htmlspecialchars($full_name) . "</a>\n";
        echo "</td>\n";
        echo "<td>\n";
        echo "  <a href=\"javascript:void(0)\" class=\"avatar-image avatar-md\">\n";
        echo "    <img src=\"" . htmlspecialchars($avatar) . "\" alt=\"\" class=\"img-fluid\">\n";
        echo "  </a>\n";
        echo "</td>\n";
        echo "<td>" . htmlspecialchars($last_exam) . "</td>\n";
        echo "<td>\n";
        echo "  <a href=\"javascript:void(0)\" class=\"badge " . $badge_class . "\">" . $display_rank . "</a>\n";
        echo "</td>\n";
        echo "<td><a href=\"javascript:void(0);\">" . htmlspecialchars($percent) . "%</a></td>\n";
        echo "</tr>\n";
    }
} else {
    echo "<tr><td colspan=\"5\">No attempts found.</td></tr>\n";
}

exit;

?>
