<?php include "includes/init.php";
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../../db/dbcon.php';
}

$total_attempts = 0;
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id) {
    $sql_attempts = "SELECT COUNT(id) AS cnt FROM tbl_attempts WHERE user_id = " . $user_id;
    if ($res_attempts = mysqli_query($conn, $sql_attempts)) {
        $row_attempts = mysqli_fetch_assoc($res_attempts);
        $total_attempts = isset($row_attempts['cnt']) ? (int)$row_attempts['cnt'] : 0;
    }
}
// Load subjects for the student attempts filter
$subjects = [];
$sql_subjects = "SELECT id, name FROM tbl_subjects ORDER BY name";
if ($rs_sub = mysqli_query($conn, $sql_subjects)) {
    while ($s = mysqli_fetch_assoc($rs_sub)) {
        $subjects[] = $s;
    }
}

// Compute student's rank based on their best attempt percent (score / question_items)
$student_rank = null;
$participants = 0;
if ($user_id) {
    // get current user's best ratio
    $cur_sql = "SELECT MAX(at.score / NULLIF(st.question_items,0)) AS best_ratio FROM tbl_attempts at JOIN tbl_subjects st ON st.id = at.subjects_id WHERE at.user_id = " . $user_id . " AND st.question_items > 0";
    $cur_ratio = null;
    if ($cres = mysqli_query($conn, $cur_sql)) {
        $crow = mysqli_fetch_assoc($cres);
        if (!empty($crow['best_ratio'])) $cur_ratio = (float)$crow['best_ratio'];
    }

    if ($cur_ratio !== null) {
        // count how many users have a better best_ratio
        $count_sql = "SELECT COUNT(*) AS better FROM (SELECT at.user_id, MAX(at.score / NULLIF(st.question_items,0)) AS best_ratio FROM tbl_attempts at JOIN tbl_subjects st ON st.id = at.subjects_id WHERE st.question_items > 0 GROUP BY at.user_id) t WHERE t.best_ratio > " . $cur_ratio;
        if ($cres2 = mysqli_query($conn, $count_sql)) {
            $crow2 = mysqli_fetch_assoc($cres2);
            $better = isset($crow2['better']) ? (int)$crow2['better'] : 0;
            $student_rank = $better + 1;
        }
    }
    // total participants (distinct users with attempts)
    $p_sql = "SELECT COUNT(DISTINCT user_id) AS p FROM tbl_attempts";
    if ($pres = mysqli_query($conn, $p_sql)) {
        $prow = mysqli_fetch_assoc($pres);
        $participants = isset($prow['p']) ? (int)$prow['p'] : 0;
    }
}
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
    <title>Student || Dashboard</title>
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
                        <h5 class="m-b-10">Student Dashboard</h5>
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
                            <div class="dropdown filter-dropdown">
                                <a hidden class="btn btn-md btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
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
                                <div class="d-flex justify-content-between align-items-start">
                                    <i class="feather-users fs-20"></i>
                                    <select id="attempt-subject-select" class="form-select form-select-sm" style="width:110px; font-size:12px; padding:2px 6px;">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $sub): ?>
                                            <option value="<?php echo (int)$sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <h5 class="fs-4 text-reset mt-4 mb-1" id="attempt-count"><?php echo number_format($total_attempts); ?></h5>
                                <div class="fs-12 text-reset fw-normal">Total Attempts</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Students] end -->
                    <!-- [Trivia / Rank] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-info border-soft-info text-info overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Trivia</h6>
                                        <?php if ($student_rank !== null): ?>
                                            <div class="d-flex align-items-baseline gap-2">
                                                <span class="badge bg-info text-white rounded-pill px-3 py-2" style="font-size:1.5rem; line-height:1;">#<?php echo (int)$student_rank; ?></span>
                                                <small class="text-muted align-self-end">/ <?php echo (int)$participants; ?> participants</small>
                                            </div>
                                            <p class="mb-0 mt-2">Did you know you're ranked <strong>#<?php echo (int)$student_rank; ?></strong> based on your best attempt?</p>
                                        <?php else: ?>
                                            <h5 class="mb-1">Unranked</h5>
                                            <p class="mb-0 mt-2">No attempts recorded yet — take an exam to get ranked.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <i class="feather-award fs-28"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [Trivia / Rank] end -->
                    <!-- Total Lessons card removed -->
                    <!-- Projects/Conversion cards removed per request -->
                    <!-- Average Score card removed -->
                    <!-- Student Rankings card removed -->
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
        <br><br><br><br><br><br><br><br><br>
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
    (function(){
        var select = document.getElementById('attempt-subject-select');
        var countEl = document.getElementById('attempt-count');
        if (!select || !countEl) return;

        function updateCount(subjectId){
            var url = 'functions/get_attempts_count.php';
            if (subjectId) url += '?subject_id=' + encodeURIComponent(subjectId);
            fetch(url, { credentials: 'same-origin' })
                .then(function(resp){ if (!resp.ok) throw new Error('Network'); return resp.json(); })
                .then(function(data){ countEl.textContent = data && typeof data.count === 'number' ? data.count.toLocaleString() : '0'; })
                .catch(function(){ /* keep current value on error */ });
        }

        select.addEventListener('change', function(){ updateCount(this.value); });
    })();
    </script>

    <!--! END: Theme Customizer !-->
</body>

</html>