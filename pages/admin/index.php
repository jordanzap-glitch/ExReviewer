<?php
include "includes/init.php";
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../../db/dbcon.php';
}
$total_students = 0;
$sql = "SELECT COUNT(id) AS cnt FROM tbl_users WHERE usertypes_id = 2";
if ($res = mysqli_query($conn, $sql)) {
    $row = mysqli_fetch_assoc($res);
    $total_students = isset($row['cnt']) ? (int)$row['cnt'] : 0;
}
// Total exams from question bank
$total_exams = 0;
$sql_exams = "SELECT COUNT(id) AS cnt FROM tbl_question_bank";
if ($res2 = mysqli_query($conn, $sql_exams)) {
    $row2 = mysqli_fetch_assoc($res2);
    $total_exams = isset($row2['cnt']) ? (int)$row2['cnt'] : 0;
}
// Total subjects from tbl_subjects
$total_subjects = 0;
$sql_subjects = "SELECT COUNT(id) AS cnt FROM tbl_subjects";
if ($res3 = mysqli_query($conn, $sql_subjects)) {
    $row3 = mysqli_fetch_assoc($res3);
    $total_subjects = isset($row3['cnt']) ? (int)$row3['cnt'] : 0;
}
// Load subjects list for dropdown
$subjects = [];
$sql_subjects_list = "SELECT id, name FROM tbl_subjects ORDER BY name";
if ($rs = mysqli_query($conn, $sql_subjects_list)) {
    while ($s = mysqli_fetch_assoc($rs)) {
        $subjects[] = $s;
    }
}

// Helper: get subjects labels and percent of attempts per subject
function get_subjects_attempts_percent($conn) {
    $labels = [];
    $values = [];
    $total = 0;
    $res_total = mysqli_query($conn, "SELECT COUNT(id) AS total FROM tbl_attempts");
    if ($res_total) {
        $r = mysqli_fetch_assoc($res_total);
        $total = isset($r['total']) ? (int)$r['total'] : 0;
    }

    $sql = "SELECT s.id, s.name, COUNT(a.id) AS attempts FROM tbl_subjects s LEFT JOIN tbl_attempts a ON a.subjects_id = s.id GROUP BY s.id ORDER BY s.name";
    if ($rs2 = mysqli_query($conn, $sql)) {
        while ($row = mysqli_fetch_assoc($rs2)) {
            $labels[] = $row['name'];
            $attempts = isset($row['attempts']) ? (int)$row['attempts'] : 0;
            $percent = $total > 0 ? round(($attempts / $total) * 100, 2) : 0;
            $values[] = $percent;
        }
    }
    return [ $labels, $values ];
}

// Helper: compute overall average/highest/lowest percent based on attempts (score / question_items)
function get_overall_score_stats($conn) {
    $avg = 0; $max = 0; $min = null; $count = 0;
    $sql = "SELECT at.score AS score, st.question_items AS items FROM tbl_attempts at JOIN tbl_subjects st ON st.id = at.subjects_id WHERE st.question_items > 0";
    if ($rs3 = mysqli_query($conn, $sql)) {
        while ($r = mysqli_fetch_assoc($rs3)) {
            $items = (int)$r['items'];
            $score = (int)$r['score'];
            if ($items <= 0) continue;
            $percent = ($score / $items) * 100;
            $avg += $percent; $count++;
            if ($percent > $max) $max = $percent;
            if ($min === null || $percent < $min) $min = $percent;
        }
    }
    if ($count > 0) {
        $avg = round($avg / $count);
        $max = (int) round($max);
        $min = (int) round($min);
    } else {
        $avg = $max = $min = 0;
    }
    return [ 'average' => $avg, 'highest' => $max, 'lowest' => $min ];
}

list($chart_labels, $chart_values) = get_subjects_attempts_percent($conn);
$chart_labels_json = json_encode($chart_labels);
$chart_values_json = json_encode($chart_values);
$score_stats = get_overall_score_stats($conn);
$stat_average = $score_stats['average'];
$stat_highest = $score_stats['highest'];
$stat_lowest = $score_stats['lowest'];
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>Admin || Dashboard</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <?php include "includes/css_scripts_head.php"; ?>
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>e
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Manu !-->
    <!--! ================================================================ !-->
   <?php include "includes/sidebar.php"; ?>
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Manu !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <?php include "includes/header.php"; ?>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- [ page-header ] start -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Admin Dashboard</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Dashboard</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="page-header-right-items">
                        <div class="d-flex d-md-none">
                            <a href="javascript:void(0)" class="page-header-right-close-toggle">
                                <i class="feather-arrow-left me-2"></i>
                                <span>Back</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                            <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                                <span class="reportrange-picker-field"></span>
                            </div>
                            <div class="dropdown filter-dropdown">
                                <a class="btn btn-md btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside" hidden>
                                    <i class="feather-filter me-2"></i>
                                    <span>Filter</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Role" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Role">Role</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Team" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Team">Team</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Email" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Email">Email</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Member" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Member">Member</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Recommendation" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Recommendation">Recommendation</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-plus me-3"></i>
                                        <span>Create New</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-filter me-3"></i>
                                        <span>Manage Filter</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-md-none d-flex align-items-center">
                        <a href="javascript:void(0)" class="page-header-right-open-toggle">
                            <i class="feather-align-right fs-20"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- [ page-header ] end -->
            <!-- [ Main Content ] start -->
            <div class="main-content">
                <div class="row">
                    <!-- [Total Students] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-primary border-soft-primary text-primary overflow-hidden">
                            <div class="card-body">
                                <i class="feather-users fs-20"></i>
                                <h5 class="fs-4 text-reset mt-4 mb-1"><?php echo number_format($total_students); ?></h5>
                                <div class="fs-12 text-reset fw-normal">Total Students</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Students] end -->
                    <!-- [Total Exam] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-success border-soft-success text-success overflow-hidden">
                            <div class="card-body">
                                <i class="feather-file-text fs-20"></i>
                                <h5 class="fs-4 text-reset mt-4 mb-1"><?php echo number_format($total_exams); ?></h5>
                                <div class="fs-12 text-reset fw-normal">Total Exam</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Exam] end -->
                    <!-- [Total Lessons] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-warning border-soft-warning text-warning overflow-hidden">
                            <div class="card-body">
                                <i class="feather-book fs-20"></i>
                                <h5 class="fs-4 text-reset mt-4 mb-1"><?php echo number_format($total_subjects); ?></h5>
                                <div class="fs-12 text-reset fw-normal">Total Subjects</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Lessons] end -->
                    <!-- Projects/Conversion cards removed per request -->
                    <!-- [Payment Records] start -->
                    <div class="col-xxl-12">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Average Score (Overall)</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Delete">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                            <div data-bs-toggle="tooltip" title="Options">
                                                <i class="feather-more-vertical"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" hidden>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action p-0">
                                <div id="average-score-area-chart" style="min-height:280px;"></div>
                                <div class="p-3">
                                        <div class="row g-3">
                                        <div class="col-4">
                                            <div class="card bg-soft-primary border-soft-primary text-primary overflow-hidden">
                                                <div class="card-body p-2 d-flex align-items-center gap-3">
                                                    <div class="avatar-text bg-soft-primary text-primary">
                                                        <i class="feather-users"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fs-12 text-muted">Average</div>
                                                        <div class="fs-5 fw-bold"><?php echo htmlspecialchars($stat_average); ?>%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="card bg-soft-success border-soft-success text-success overflow-hidden">
                                                <div class="card-body p-2 d-flex align-items-center gap-3">
                                                    <div class="avatar-text bg-soft-success text-success">
                                                        <i class="feather-award"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fs-12 text-muted">Highest</div>
                                                        <div class="fs-5 fw-bold"><?php echo htmlspecialchars($stat_highest); ?>%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="card bg-soft-danger border-soft-danger text-danger overflow-hidden">
                                                <div class="card-body p-2 d-flex align-items-center gap-3">
                                                    <div class="avatar-text bg-soft-danger text-danger">
                                                        <i class="feather-trending-down"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fs-12 text-muted">Lowest</div>
                                                        <div class="fs-5 fw-bold"><?php echo htmlspecialchars($stat_lowest); ?>%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [Payment Records] end -->
                    <!-- [Leads Status / Student Ranking] start -->
                    <div class="col-xxl-12">
                        <div class="card s  tretch stretch-full">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title text-white mb-0">Student Rankings</h5>
                                    <small class="text-white-50">Leads Status (Top performers)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-primary text-dark">Top 5</span>
                                    <div class="mt-1">
                                        <form id="subjectFilterForm" method="get" class="d-inline">
                                            <select name="subject_id" id="subjectSelect" class="form-select form-select-sm" style="min-width:160px; display:inline-block;">
                                                <option value="">All Subjects</option>
                                                <?php foreach ($subjects as $sub): ?>
                                                    <option value="<?php echo (int)$sub['id']; ?>" <?php echo (isset($_GET['subject_id']) && (int)$_GET['subject_id'] === (int)$sub['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Name</th>
                                                <th scope="col" class="wd-100">Avatar</th>
                                                <th scope="col">Last Exam</th>
                                                <th scope="col">Rank</th>
                                                <th scope="col">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rankings-tbody">
    <?php
    // Student rankings: pick each user's single best attempt (by score/question_items).
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
            // Build avatar path from tbl_users.image_path (filename stored in DB)
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

            // Competition ranking: same rank for equal percent, next rank skips accordingly
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
    ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center">Update: 30 Min Ago</a>
                        </div>
                    </div>
                    <!-- [Leads Status / Student Ranking] end -->
                    <!-- [Mini] start -->
                    <!-- [Leads Overview] end -->
                    <!-- [Latest Leads] start -->
                    <!-- [Latest Leads] end -->
                    <!--! BEGIN: [Upcoming Schedule] !-->
                    
                    <!--! END: [Upcoming Schedule] !-->
                    <!--! BEGIN: [Project Status] !-->
                   
                    <!--! END: [Project Status] !-->
                    <!--! BEGIN: [Team Progress] !-->
                    <!--! END: [Team Progress] !-->
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
        <!-- [ Footer ] start -->
            <?php include "includes/footer.php"; ?>
        <!-- [ Footer ] end -->
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->
    <?php include "includes/customizer.php"; ?>
    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <?php include "includes/scripts.php"; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: { height: 350, type: 'bar', toolbar: { show: false } },
            series: [{ name: 'Attempts (%)', data: <?php echo $chart_values_json; ?> }],
            plotOptions: { bar: { columnWidth: '55%' } },
            colors: ['#3454d1'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: <?php echo $chart_labels_json; ?>,
                labels: { style: { fontSize: '11px', colors: '#64748b' } },
                title: { text: 'Subjects' }
            },
            yaxis: {
                min: 0,
                max: 100,
                labels: {
                    formatter: function (val) { return val + ' %'; },
                    style: { fontSize: '11px', color: '#64748b' }
                },
                title: { text: 'Percent of Attempts (%)' }
            },
            grid: { padding: { left: 0, right: 0 }, strokeDashArray: 3, borderColor: '#ebebf3' },
            legend: { show: false },
            tooltip: { y: { formatter: function (val) { return val + ' %'; } }, style: { fontSize: '11px', fontFamily: 'Inter' } }
        };

        var chart = new ApexCharts(document.querySelector('#average-score-area-chart'), options);
        chart.render();
    });
    </script>

    <script>
    (function(){
        var select = document.getElementById('subjectSelect');
        var tbody = document.getElementById('rankings-tbody');
        if (!select || !tbody) return;

        function fetchRankings(subjectId){
            var url = 'functions/get_rankings.php';
            if (subjectId) url += '?subject_id=' + encodeURIComponent(subjectId);
            // show loading row
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
            fetch(url, { credentials: 'same-origin' })
                .then(function(resp){ if (!resp.ok) throw new Error('Network error'); return resp.text(); })
                .then(function(html){ tbody.innerHTML = html; })
                .catch(function(){ tbody.innerHTML = '<tr><td colspan="5" class="text-center">Failed to load rankings.</td></tr>'; });
        }

        select.addEventListener('change', function(e){
            fetchRankings(this.value);
        });

        // optionally fetch on load if a subject is already selected
        if (select.value) fetchRankings(select.value);
    })();
    </script>

    <!--! END: Theme Customizer !-->
</body>

</html>