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

// Fetch saved questions with subject name and academic year
$saved_questions = [];
$sql3 = "SELECT qb.id, qb.question, qb.opt_a, qb.opt_b, qb.opt_c, qb.opt_d, qb.correct_ans, s.name AS subject_name, YEAR(ay.sy_start) AS sy_start, YEAR(ay.sy_end) AS sy_end FROM tbl_question_bank qb LEFT JOIN tbl_subjects s ON qb.subjects_id = s.id LEFT JOIN tbl_academicyears ay ON qb.academicyears_id = ay.id ORDER BY qb.id DESC";
$stmt3 = mysqli_prepare($conn, $sql3);
if ($stmt3) {
    mysqli_stmt_execute($stmt3);
    $res3 = mysqli_stmt_get_result($stmt3);
    while ($row = mysqli_fetch_assoc($res3)) {
        $saved_questions[] = $row;
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

        $year = $_POST['year'] ?? '';
        $remarks = $_POST['remarks'] ?? '';

        $res = add_question($conn, $question, $a, $b, $c, $d, $correct, $subject, $year, $remarks);
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
                                        <div class="mb-3">
                                            <label for="examRemarks" class="form-label">Remarks</label>
                                            <textarea id="examRemarks" name="remarks" class="form-control" rows="2" placeholder="Optional remarks"></textarea>
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
                                                <?php if (!empty($saved_questions)): ?>
                                                    <?php foreach ($saved_questions as $q): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($q['question'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($q['opt_a'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($q['opt_b'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($q['opt_c'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($q['opt_d'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($q['correct_ans'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($q['subject_name'] ?? ''); ?></td>
                                                            <td><?php echo !empty($q['sy_start']) ? htmlspecialchars($q['sy_start'] . ' - ' . $q['sy_end']) : ''; ?></td>
                                                            <td>
                                                                <div class="d-flex gap-1">
                                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary view-question-btn" title="View" aria-disabled="true" data-id="<?php echo (int)$q['id']; ?>">
                                                                        <i class="feather-eye"></i>
                                                                    </a>
                                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-warning edit-question-btn" title="Edit" data-id="<?php echo (int)$q['id']; ?>">
                                                                        <i class="feather-edit"></i>
                                                                    </a>
                                                                    <a href="delete_question.php?id=<?php echo (int)$q['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this question?');">
                                                                        <i class="feather-trash-2"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center">No questions found</td>
                                                    </tr>
                                                <?php endif; ?>
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
    <!-- View Question Modal -->
    <div class="modal fade" id="viewQuestionModal" tabindex="-1" aria-labelledby="viewQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewQuestionModalLabel">View Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewQuestionContent">
                        <p><strong>Question:</strong> <span id="v_question"></span></p>
                        <p><strong>A:</strong> <span id="v_a"></span></p>
                        <p><strong>B:</strong> <span id="v_b"></span></p>
                        <p><strong>C:</strong> <span id="v_c"></span></p>
                        <p><strong>D:</strong> <span id="v_d"></span></p>
                        <p><strong>Correct:</strong> <span id="v_correct"></span></p>
                        <p><strong>Subject:</strong> <span id="v_subject"></span></p>
                        <p><strong>Year:</strong> <span id="v_year"></span></p>
                        <p><strong>Remarks:</strong> <span id="v_remarks"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editQuestionForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="e_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <textarea name="question" id="e_question" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Answer A</label>
                                <input name="opt_a" id="e_a" type="text" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Answer B</label>
                                <input name="opt_b" id="e_b" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Answer C</label>
                                <input name="opt_c" id="e_c" type="text" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Answer D</label>
                                <input name="opt_d" id="e_d" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Correct Answer</label>
                                <select name="correct" id="e_correct" class="form-select">
                                    <option value="">-- Select Correct --</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Subject</label>
                                <select name="subject" id="e_subject" class="form-select">
                                    <option value="">-- Select Subject --</option>
                                    <?php if (!empty($subjects)): ?>
                                        <?php foreach ($subjects as $s): ?>
                                            <option value="<?php echo htmlspecialchars($s['name']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Year</label>
                                <select name="year" id="e_year" class="form-select">
                                    <option value="">-- Select Year --</option>
                                    <?php if (!empty($years)): ?>
                                        <?php foreach ($years as $y): ?>
                                            <option value="<?php echo htmlspecialchars($y['id']); ?>"><?php echo htmlspecialchars($y['sy_start'] . ' - ' . $y['sy_end']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="e_remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

        // Attach view button handler (delegated)
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.view-question-btn');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            if (!id) return;
            // Fetch question details
            fetchJson('./functions/get_question.php?id=' + encodeURIComponent(id), {credentials: 'same-origin'})
                .then(function (json) {
                    if (!json || !json.success) {
                        showToast('error', json && json.error ? json.error : 'Failed to load question');
                        return;
                    }
                    var d = json.data || {};
                    document.getElementById('v_question').textContent = d.question || '';
                    document.getElementById('v_a').textContent = d.opt_a || '';
                    document.getElementById('v_b').textContent = d.opt_b || '';
                    document.getElementById('v_c').textContent = d.opt_c || '';
                    document.getElementById('v_d').textContent = d.opt_d || '';
                    document.getElementById('v_correct').textContent = d.correct_ans || '';
                    document.getElementById('v_subject').textContent = d.subject_name || '';
                    document.getElementById('v_year').textContent = d.academicyear || '';
                    document.getElementById('v_remarks').textContent = d.remarks || '';
                    var modalEl = document.getElementById('viewQuestionModal');
                    var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    bsModal.show();
                })
                .catch(function (err) { console.error('View error:', err); showToast('error', (err && (err.message || err.toString())) || 'Network error'); });
        });

                // Utility: fetch JSON and surface non-JSON errors
                function fetchJson(url, opts) {
                    return fetch(url, opts).then(function (res) {
                        var ct = res.headers.get('content-type') || '';
                        if (!res.ok) {
                            return res.text().then(function (t) { throw new Error(t || ('HTTP ' + res.status)); });
                        }
                        if (ct.indexOf('application/json') === -1) {
                            return res.text().then(function (t) { throw new Error(t || 'Unexpected response'); });
                        }
                        return res.json();
                    });
                }

                // Edit button handler
                var currentEditRow = null;
                document.addEventListener('click', function (e) {
                    var btn = e.target.closest && e.target.closest('.edit-question-btn');
                    if (!btn) return;
                    var id = btn.getAttribute('data-id');
                    if (!id) return;
                    // Store row for later update
                    currentEditRow = btn.closest('tr');
                    fetchJson('./functions/get_question.php?id=' + encodeURIComponent(id), {credentials: 'same-origin'})
                        .then(function (json) {
                            if (!json || !json.success) { showToast('error', json && json.error ? json.error : 'Failed to load question'); return; }
                            var d = json.data || {};
                            document.getElementById('e_id').value = d.id || '';
                            document.getElementById('e_question').value = d.question || '';
                            document.getElementById('e_a').value = d.opt_a || '';
                            document.getElementById('e_b').value = d.opt_b || '';
                            document.getElementById('e_c').value = d.opt_c || '';
                            document.getElementById('e_d').value = d.opt_d || '';
                            document.getElementById('e_correct').value = d.correct_ans || '';
                            document.getElementById('e_subject').value = d.subject_name || '';
                            document.getElementById('e_year').value = d.academicyear_id || '';
                            document.getElementById('e_remarks').value = d.remarks || '';
                            var modalEl = document.getElementById('editQuestionModal');
                            var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            bsModal.show();
                        })
                        .catch(function (err) { console.error('Edit fetch error:', err); showToast('error', (err && (err.message || err.toString())) || 'Network error'); });
                });

                // Handle edit form submit (AJAX)
                var editForm = document.getElementById('editQuestionForm');
                if (editForm) {
                    editForm.addEventListener('submit', function (ev) {
                        ev.preventDefault();
                        var formData = new FormData(editForm);
                        fetchJson('./functions/update_question.php', {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: formData
                        }).then(function (json) {
                            if (!json || !json.success) { showToast('error', json && json.error ? json.error : 'Update failed'); return; }
                            // success: update row cells if we have the row
                            if (currentEditRow) {
                                var cells = currentEditRow.querySelectorAll('td');
                                if (cells.length >= 8) {
                                    // columns: question, A, B, C, D, correct, subject, year
                                    cells[0].textContent = formData.get('question') || '';
                                    cells[1].textContent = formData.get('opt_a') || '';
                                    cells[2].textContent = formData.get('opt_b') || '';
                                    cells[3].textContent = formData.get('opt_c') || '';
                                    cells[4].textContent = formData.get('opt_d') || '';
                                    cells[5].textContent = formData.get('correct') || '';
                                    cells[6].textContent = formData.get('subject') || '';
                                    // find selected year text
                                    var ysel = document.getElementById('e_year');
                                    cells[7].textContent = ysel && ysel.options[ysel.selectedIndex] ? ysel.options[ysel.selectedIndex].text : '';
                                }
                            }
                            // close modal
                            var modalEl = document.getElementById('editQuestionModal');
                            var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            bsModal.hide();
                            showToast('success','Question updated');
                        }).catch(function (err) { console.error('Update submit error:', err); showToast('error', (err && (err.message || err.toString())) || 'Network error'); });
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