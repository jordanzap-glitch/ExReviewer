<?php include "includes/init.php"; ?>
<?php
// Load DB connection and fetch active subjects and academic years
require_once __DIR__ . '/../../db/dbcon.php';
require_once __DIR__ . '/functions/questions.php';

$subjects = [];
$sql = "SELECT `name` FROM tbl_subjects ORDER BY `name` ASC";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $subjects[] = $r;
    }
}

$years = [];
$sql2 = "SELECT id, YEAR(sy_start) AS sy_start, YEAR(sy_end) AS sy_end FROM tbl_academicyears WHERE is_active = 1 ORDER BY sy_start DESC";
$stmt2 = mysqli_prepare($conn, $sql2);
if ($stmt2) {
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    while ($r2 = mysqli_fetch_assoc($res2)) {
        $years[] = $r2;
    }
}

    // If a message from a previous POST exists, pull it for display (PRG)
    $exam_msg = null;
    if (!empty($_SESSION['exam_msg'])) {
        $exam_msg = $_SESSION['exam_msg'];
        unset($_SESSION['exam_msg']);
    }

    // Handle form POST (non-AJAX preferred)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_question') {
        // CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($token)) {
            $_SESSION['exam_msg'] = ['type' => 'danger', 'text' => 'Invalid CSRF token.'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        $question = $_POST['question'] ?? '';
        $a = $_POST['opt_a'] ?? '';
        $b = $_POST['opt_b'] ?? '';
        $c = $_POST['opt_c'] ?? '';
        $d = $_POST['opt_d'] ?? '';
        $correct = $_POST['correct'] ?? '';
        $subject = $_POST['subject'] ?? '';

        $res = add_question($conn, $question, $a, $b, $c, $d, $correct, $subject);
        if ($res['success']) {
            $_SESSION['exam_msg'] = ['type' => 'success', 'text' => 'Question saved successfully.'];
        } else {
            $_SESSION['exam_msg'] = ['type' => 'danger', 'text' => $res['error'] ?? 'Failed to save question.'];
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="theme_ocean">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>EEReviewer || Add Exam</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->-p45edf 78 
    <?php include "includes/css_scripts_head.php"; ?>
    <!-- DataTables CSS (CDN) -->
    <!--! END: Custom CSS-->
    <!-- DataTables CSS -->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
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
                        <h5 class="m-b-10">Add Exam</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Exams</li>
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
                            <div class="dropdown">
                                <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                                    <i class="feather-paperclip"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-pdf me-3"></i>
                                        <span>PDF</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-csv me-3"></i>
                                        <span>CSV</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-xml me-3"></i>
                                        <span>XML</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-txt me-3"></i>
                                        <span>Text</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-filetype-exe me-3"></i>
                                        <span>Excel</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="bi bi-printer me-3"></i>
                                        <span>Print</span>
                                    </a>
                                </div>
                            </div>
                            <button id="saveExamBtn" type="button" class="btn btn-primary">
                                <i class="feather-save me-2"></i>
                                <span>Save</span>
                            </button>
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
                    <div class="col-lg-12">
                        <div class="card stretch stretch-full">
                            <div class="card-header bg-soft-info border-soft-info text-info d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">Questions</h5>
                                </div>
                            <div class="card-body p-0">
                                <div class="p-3">
                                    <form id="examForm" method="post">
                                        <input type="hidden" name="action" value="add_question">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <div class="mb-3">
                                            <label for="examQuestion" class="form-label">Question</label>
                                            <textarea id="examQuestion" name="question" class="form-control" rows="3" placeholder="Enter the question"></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="answerA" class="form-label">Answer A</label>
                                                <input id="answerA" name="opt_a" type="text" class="form-control" placeholder="Option A">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="answerB" class="form-label">Answer B</label>
                                                <input id="answerB" name="opt_b" type="text" class="form-control" placeholder="Option B">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="answerC" class="form-label">Answer C</label>
                                                <input id="answerC" name="opt_c" type="text" class="form-control" placeholder="Option C">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="answerD" class="form-label">Answer D</label>
                                                <input id="answerD" name="opt_d" type="text" class="form-control" placeholder="Option D">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="correctAnswer" class="form-label">Correct Answer</label>
                                                <select id="correctAnswer" name="correct" class="form-select">
                                                    <option value="">-- Select Correct --</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="examSubject" class="form-label">Subject</label>
                                                <select id="examSubject" name="subject" class="form-select">
                                                    <option value="">-- Select Subject --</option>
                                                    <?php if (!empty($subjects)): ?>
                                                        <?php foreach ($subjects as $s): ?>
                                                            <option value="<?php echo htmlspecialchars($s['name']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">No active subjects</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="examYear" class="form-label">Year</label>
                                                <select id="examYear" name="year" class="form-select">
                                                    <option value="">-- Select Year --</option>
                                                    <?php if (!empty($years)): ?>
                                                        <?php foreach ($years as $y): ?>
                                                            <option value="<?php echo htmlspecialchars($y['id']); ?>"><?php echo htmlspecialchars($y['sy_start'] . ' - ' . $y['sy_end']); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">No active academic years</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card stretch stretch-full">
                            <div class="card-header bg-soft-info border-soft-info text-info d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Saved Questions</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-3">
                                    <div class="table-responsive">
                                        <table id="myTable" class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Question</th>
                                                    <th>A</th>
                                                    <th>B</th>
                                                    <th>C</th>
                                                    <th>D</th>
                                                    <th>Correct</th>
                                                    <th>Subject</th>
                                                    <th>Year</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- saved questions will be appended here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <!--! [Start] Search Modal !-->
    <!--! ================================================================ !-->>
    <!--! ================================================================ !-->
    <!--! [End] Search Modal !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Language Select !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [End] Language Select !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Downloading Toast !-->
    <!--! ================================================================ !-->
    <div class="position-fixed" style="right: 5px; bottom: 5px; z-index: 999999">
        <div id="toast" class="toast bg-black hide" data-bs-delay="3000" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header px-3 bg-transparent d-flex align-items-center justify-content-between border-bottom border-light border-opacity-10">
                <div class="text-white mb-0 mr-auto">Downloading...</div>
                <a href="javascript:void(0)" class="ms-2 mb-1 close fw-normal" data-bs-dismiss="toast" aria-label="Close">
                    <span class="text-white">&times;</span>
                </a>
            </div>
            <div class="toast-body p-3 text-white">
                <h6 class="fs-13 text-white">Project.zip</h6>
                <span class="text-light fs-11">4.2mb of 5.5mb</span>
            </div>
            <div class="toast-footer p-3 pt-0 border-top border-light border-opacity-10">
                <div class="progress mt-3" style="height: 5px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated w-75 bg-dark" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! END: Downloading Toast !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->
    <?php include "includes/customizer2.php"; ?>

    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!-- Toast container (used for success / error messages) -->
    <div id="nxlToastContainer" class="position-fixed" style="right:12px; bottom:12px; z-index:999999">
        <div id="nxlToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="nxlToastBody">Action completed</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
    function showToast(type, message, delay) {
        delay = delay || 3000;
        var toastEl = document.getElementById('nxlToast');
        var body = document.getElementById('nxlToastBody');
        if (!toastEl || !body) {
            alert(message);
            return;
        }
        // reset classes
        toastEl.classList.remove('bg-success','bg-danger','bg-primary','bg-secondary');
        if (type === 'success') toastEl.classList.add('bg-success');
        else if (type === 'error' || type === 'danger') toastEl.classList.add('bg-danger');
        else toastEl.classList.add('bg-primary');
        body.textContent = message;
        var bsToast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: delay });
        bsToast.show();
    }
    <?php if (!empty($exam_msg)): ?>
    var _exam_msg = <?php echo json_encode($exam_msg); ?>;
    document.addEventListener('DOMContentLoaded', function () {
        // show server-side message after redirect
        showToast(_exam_msg.type, _exam_msg.text);
    });
    <?php endif; ?>

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('saveExamBtn');
        if (btn) {
            btn.addEventListener('click', function () {
                var question = document.getElementById('examQuestion').value.trim();
                var a = document.getElementById('answerA').value.trim();
                var b = document.getElementById('answerB').value.trim();
                var c = document.getElementById('answerC').value.trim();
                var d = document.getElementById('answerD').value.trim();
                var correct = document.getElementById('correctAnswer').value;
                var subject = document.getElementById('examSubject').value;
                var year = document.getElementById('examYear').value;
                if (!question) { showToast('error','Please enter the question'); return; }
                if (!a || !b || !c || !d) { showToast('error','Please provide all answer options'); return; }
                if (!correct) { showToast('error','Please select the correct answer'); return; }
                if (!subject) { showToast('error','Please select a subject'); return; }

                // Submit the form normally (no AJAX)
                var form = document.getElementById('examForm');
                if (form) form.submit();
            });
        }
    });
    </script>
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
   <?php include "includes/scripts.php"; ?>
        <!-- DataTables JS (CDN) -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables JS -->
    <script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            paging: true,
            searching: true
        });
    });
    </script>
    <!--! END: Theme Customizer !-->
</body>

</html>