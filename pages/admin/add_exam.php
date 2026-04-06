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
    <!--! BEGIN: Favicon-->
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
                                                                <a href="javascript:void(0);" class="text-info me-2 fs-5 view-question-btn" title="View" data-id="<?php echo (int)$q['id']; ?>">
                                                                    <i class="feather-eye"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" class="text-primary me-2 fs-5 edit-question-btn" title="Edit" data-id="<?php echo (int)$q['id']; ?>">
                                                                    <i class="feather-edit"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" class="text-danger fs-5 delete-question-btn" title="Delete" data-id="<?php echo (int)$q['id']; ?>">
                                                                    <i class="feather-trash-2"></i>
                                                                </a>
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
    <!-- View modal removed per request: markup deleted to disable view functionality -->

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

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="deleteConfirmText">Are you sure you want to delete this question?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="deleteConfirmBtn" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- View Question Modal (read-only, no textboxes) -->
                        <div class="modal fade" id="viewQuestionModal" tabindex="-1" aria-labelledby="viewQuestionModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewQuestionModalLabel">View Question</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Question</label>
                                            <p id="v_question" class="form-control-plaintext"></p>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Answer A</label>
                                                <p id="v_a" class="form-control-plaintext"></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Answer B</label>
                                                <p id="v_b" class="form-control-plaintext"></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Answer C</label>
                                                <p id="v_c" class="form-control-plaintext"></p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Answer D</label>
                                                <p id="v_d" class="form-control-plaintext"></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Correct Answer</label>
                                                <p id="v_correct" class="form-control-plaintext"></p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Subject</label>
                                                <p id="v_subject" class="form-control-plaintext"></p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Year</label>
                                                <p id="v_year" class="form-control-plaintext"></p>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <p id="v_remarks" class="form-control-plaintext"></p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
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
                var remarks = document.getElementById('examRemarks').value || '';
                if (!question) { showToast('error','Please enter the question'); return; }
                if (!a || !b || !c || !d) { showToast('error','Please provide all answer options'); return; }
                if (!correct) { showToast('error','Please select the correct answer'); return; }
                if (!subject) { showToast('error','Please select a subject'); return; }

                // Submit via AJAX to functions/questions.php?action=add
                var form = document.getElementById('examForm');
                if (!form) return;
                var fd = new FormData(form);
                fd.append('action', 'add');
                fd.append('remarks', remarks);

                fetch('functions/questions.php', { method: 'POST', body: fd }).then(function(resp){
                    var ct = resp.headers.get('content-type') || '';
                    if (!resp.ok) return resp.text().then(function(t){ throw new Error(t || ('HTTP ' + resp.status)); });
                    if (ct.indexOf('application/json') === -1) return resp.text().then(function(t){ throw new Error(t || 'Unexpected response'); });
                    return resp.json();
                }).then(function (data) {
                    if (!data || !data.success) { showToast('error', data && data.error ? data.error : 'Save failed'); return; }
                    showToast('success', 'Question saved');
                    var id = data.id || (data.data && data.data.id) || '';
                    var q = (data.data && data.data.question) || fd.get('question');
                    var optA = (data.data && data.data.opt_a) || fd.get('opt_a');
                    var optB = (data.data && data.data.opt_b) || fd.get('opt_b');
                    var optC = (data.data && data.data.opt_c) || fd.get('opt_c');
                    var optD = (data.data && data.data.opt_d) || fd.get('opt_d');
                    var correctAns = (data.data && data.data.correct_ans) || fd.get('correct');
                    var subjectName = (data.data && data.data.subject_name) || fd.get('subject');
                    var yearText = (data.data && data.data.academicyear) || (fd.get('year') ? fd.get('year') : '');

                    var actionHtml = '<a href="javascript:void(0);" class="text-secondary me-2 fs-5 view-question-btn" title="View" data-id="' + id + '"><i class="feather-eye"></i></a>' +
                                     '<a href="javascript:void(0);" class="text-primary me-2 fs-5 edit-question-btn" title="Edit" data-id="' + id + '"><i class="feather-edit"></i></a>' +
                                     '<a href="javascript:void(0);" class="text-danger fs-5 delete-question-btn" title="Delete" data-id="' + id + '"><i class="feather-trash-2"></i></a>';

                    try {
                        if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#myTable')) {
                            var dt = $('#myTable').DataTable();
                            var newRow = dt.row.add([q, optA, optB, optC, optD, correctAns, subjectName, yearText, actionHtml]).draw(false).node();
                            bindQuestionRow(newRow);
                        } else {
                            var tbody = document.querySelector('#myTable tbody');
                            if (tbody) {
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td>' + escapeHtml(q) + '</td><td>' + escapeHtml(optA) + '</td><td>' + escapeHtml(optB) + '</td><td>' + escapeHtml(optC) + '</td><td>' + escapeHtml(optD) + '</td><td>' + escapeHtml(correctAns) + '</td><td>' + escapeHtml(subjectName) + '</td><td>' + escapeHtml(yearText) + '</td><td>' + actionHtml + '</td>';
                                tbody.insertBefore(tr, tbody.firstChild);
                                bindQuestionRow(tr);
                            }
                        }
                    } catch (e) { console.error(e); }

                    // reset form
                    form.reset();
                }).catch(function (err) {
                    showToast('error', err.message || 'Error saving question');
                });
            });
        }

        // NOTE: View button handler removed per request — button remains in HTML but no JS action.

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

                // View button: fetch question JSON and populate the read-only view modal (no textboxes)
                function enableViewButtons() {
                    var buttons = document.querySelectorAll('.view-question-btn');
                    buttons.forEach(function (btn) {
                        if (btn._bound) return; btn._bound = true;
                        btn.addEventListener('click', function (e) {
                            var id = btn.getAttribute('data-id');
                            if (!id) return;
                            var url = 'functions/questions.php?action=view&id=' + encodeURIComponent(id);
                            fetchJson(url, { method: 'GET' }).then(function (res) {
                                if (!res || !res.success) {
                                    showToast('error', res && res.error ? res.error : 'Failed to load question');
                                    return;
                                }
                                var d = res.data || {};
                                // populate read-only elements
                                var setText = function(id, val){ var el = document.getElementById(id); if (!el) return; el.textContent = val === null || typeof val === 'undefined' ? '' : val; };
                                setText('v_question', d.question || '');
                                setText('v_a', d.opt_a || '');
                                setText('v_b', d.opt_b || '');
                                setText('v_c', d.opt_c || '');
                                setText('v_d', d.opt_d || '');
                                setText('v_correct', d.correct_ans || '');
                                setText('v_subject', d.subject_name || '');
                                // prefer human-readable year if available
                                var yearText = '';
                                if (d.sy_start && d.sy_end) yearText = d.sy_start + ' - ' + d.sy_end;
                                else if (d.academicyear_id) yearText = d.academicyear_id;
                                setText('v_year', yearText);
                                setText('v_remarks', d.remarks || '');

                                // show view modal
                                var modalEl = document.getElementById('viewQuestionModal');
                                var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                                bsModal.show();
                            }).catch(function (err) {
                                showToast('error', err.message || 'Error fetching question');
                            });
                        });
                    });
                }

                // Initialize view buttons
                enableViewButtons();

                // Edit button: fetch question JSON, populate modal in editable mode and attach submit handler
                function enableEditButtons() {
                    var buttons = document.querySelectorAll('.edit-question-btn');
                    buttons.forEach(function (btn) {
                        if (btn._bound) return; btn._bound = true;
                        btn.addEventListener('click', function (e) {
                            var id = btn.getAttribute('data-id');
                            if (!id) return;
                            var url = 'functions/questions.php?action=view&id=' + encodeURIComponent(id);
                            fetchJson(url, { method: 'GET' }).then(function (res) {
                                if (!res || !res.success) {
                                    showToast('error', res && res.error ? res.error : 'Failed to load question');
                                    return;
                                }
                                var d = res.data || {};
                                var setVal = function(id, val){ var el = document.getElementById(id); if (!el) return; el.value = val === null || typeof val === 'undefined' ? '' : val; };
                                setVal('e_id', d.id || '');
                                setVal('e_question', d.question || '');
                                setVal('e_a', d.opt_a || '');
                                setVal('e_b', d.opt_b || '');
                                setVal('e_c', d.opt_c || '');
                                setVal('e_d', d.opt_d || '');
                                setVal('e_correct', d.correct_ans || '');
                                setVal('e_subject', d.subject_name || '');
                                setVal('e_year', d.academicyear_id || '');
                                setVal('e_remarks', d.remarks || '');

                                // make sure fields editable
                                ['e_question','e_a','e_b','e_c','e_d','e_remarks'].forEach(function(i){ var el=document.getElementById(i); if(el) el.readOnly = false; });
                                var selects = ['e_correct','e_subject','e_year'];
                                selects.forEach(function(i){ var el=document.getElementById(i); if(el) el.disabled = false; });

                                // show save button
                                var submitBtn = document.querySelector('#editQuestionForm button[type="submit"]');
                                if (submitBtn) submitBtn.style.display = '';

                                // set modal title
                                var title = document.getElementById('editQuestionModalLabel');
                                if (title) title.textContent = 'Edit Question';

                                // show modal
                                var modalEl = document.getElementById('editQuestionModal');
                                var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                                bsModal.show();

                                // attach submit handler (idempotent)
                                var form = document.getElementById('editQuestionForm');
                                if (form && !form._hasSubmitHandler) {
                                    form.addEventListener('submit', function (ev) {
                                        ev.preventDefault();
                                        var fd = new FormData(form);
                                        fd.append('action', 'update');
                                        // POST via fetch
                                        fetch('functions/questions.php', { method: 'POST', body: fd }).then(function (resp) {
                                            var ct = resp.headers.get('content-type') || '';
                                            if (!resp.ok) return resp.text().then(function(t){ throw new Error(t || ('HTTP ' + resp.status)); });
                                            if (ct.indexOf('application/json') === -1) return resp.text().then(function(t){ throw new Error(t || 'Unexpected response'); });
                                            return resp.json();
                                        }).then(function (data) {
                                            if (!data || !data.success) {
                                                showToast('error', data && data.error ? data.error : 'Update failed');
                                                return;
                                            }
                                            showToast('success', 'Question updated');
                                            // close modal
                                            var bs = bootstrap.Modal.getInstance(modalEl);
                                            if (bs) bs.hide();

                                            // update table row inline (find row by data-id)
                                            try {
                                                var qid = fd.get('id');
                                                if (qid) {
                                                    var rowBtn = document.querySelector('a.edit-question-btn[data-id="' + qid + '"]');
                                                    if (!rowBtn) rowBtn = document.querySelector('a.view-question-btn[data-id="' + qid + '"]');
                                                    if (rowBtn) {
                                                        var tr = rowBtn.closest('tr');
                                                        if (tr) {
                                                            // cells: 0=question,1=A,2=B,3=C,4=D,5=Correct,6=Subject,7=Year
                                                            var tds = tr.querySelectorAll('td');
                                                            if (tds && tds.length >= 8) {
                                                                tds[0].textContent = fd.get('question') || '';
                                                                tds[1].textContent = fd.get('opt_a') || '';
                                                                tds[2].textContent = fd.get('opt_b') || '';
                                                                tds[3].textContent = fd.get('opt_c') || '';
                                                                tds[4].textContent = fd.get('opt_d') || '';
                                                                tds[5].textContent = fd.get('correct') || '';
                                                                tds[6].textContent = fd.get('subject') || '';
                                                                // year display may be id; try to get selected option text
                                                                var y = fd.get('year') || '';
                                                                var ytext = '';
                                                                var ysel = document.getElementById('e_year');
                                                                if (ysel) {
                                                                    var opt = ysel.querySelector('option[value="' + y + '"]');
                                                                    if (opt) ytext = opt.textContent;
                                                                }
                                                                tds[7].textContent = ytext || y;
                                                            }
                                                        }
                                                    }
                                                }
                                            } catch (e) {
                                                console.error(e);
                                            }
                                        }).catch(function (err) {
                                            showToast('error', err.message || 'Error updating question');
                                        });
                                    });
                                    form._hasSubmitHandler = true;
                                }
                            }).catch(function (err) {
                                showToast('error', err.message || 'Error fetching question');
                            });
                        });
                    });
                }

                // Initialize edit buttons
                enableEditButtons();

                // Delete button: confirm and delete via AJAX, then remove row inline
                function enableDeleteButtons() {
                    var buttons = document.querySelectorAll('.delete-question-btn');
                    buttons.forEach(function (btn) {
                        if (btn._bound) return; btn._bound = true;
                        btn.addEventListener('click', function (e) {
                            var id = btn.getAttribute('data-id');
                            if (!id) return;
                            // show confirmation modal
                            var modalEl = document.getElementById('deleteConfirmModal');
                            if (!modalEl) {
                                // fallback to native confirm
                                if (!confirm('Delete this question?')) return;
                                performDelete(id);
                                return;
                            }
                            // set message with question snippet if available
                            var tr = btn.closest('tr');
                            var qtext = '';
                            if (tr) {
                                var td = tr.querySelector('td');
                                if (td) qtext = td.textContent.trim();
                            }
                            var msg = 'Are you sure you want to delete this question?';
                            if (qtext) msg = 'Delete question: "' + qtext + '"?';
                            var textEl = document.getElementById('deleteConfirmText');
                            if (textEl) textEl.textContent = msg;

                            // store id on modal element
                            modalEl.dataset.deleteId = id;
                            var bs = bootstrap.Modal.getOrCreateInstance(modalEl);
                            bs.show();
                        });
                    });

                    // Confirm button handler (attach once)
                    var confirmBtn = document.getElementById('deleteConfirmBtn');
                    var modalEl = document.getElementById('deleteConfirmModal');
                    if (confirmBtn && modalEl && !confirmBtn._attached) {
                        confirmBtn.addEventListener('click', function () {
                            var id = modalEl.dataset.deleteId;
                            if (!id) return;
                            performDelete(id, function (success) {
                                if (success) {
                                    var bs = bootstrap.Modal.getInstance(modalEl);
                                    if (bs) bs.hide();
                                }
                            });
                        });
                        confirmBtn._attached = true;
                    }
                }

                // Bind handlers for a single newly-inserted row
                function bindQuestionRow(row) {
                    if (!row) return;
                    var view = row.querySelector('.view-question-btn'); if (view && !view._bound) { view._bound = true; view.addEventListener('click', function () { var id = this.dataset.id; if (!id) return; var url = 'functions/questions.php?action=view&id=' + encodeURIComponent(id); fetchJson(url, { method: 'GET' }).then(function(res){ if (!res || !res.success) { showToast('error', res && res.error ? res.error : 'Failed'); return; } var d = res.data || {}; var setText = function(id, val){ var el = document.getElementById(id); if (!el) return; el.textContent = val === null || typeof val === 'undefined' ? '' : val; }; setText('v_question', d.question || ''); setText('v_a', d.opt_a || ''); setText('v_b', d.opt_b || ''); setText('v_c', d.opt_c || ''); setText('v_d', d.opt_d || ''); setText('v_correct', d.correct_ans || ''); setText('v_subject', d.subject_name || ''); var ayText = ''; if (d.sy_start && d.sy_end) ayText = d.sy_start + ' - ' + d.sy_end; setText('v_year', ayText); var bsModal = new bootstrap.Modal(document.getElementById('viewQuestionModal')); bsModal.show(); }).catch(function(err){ showToast('error', err.message || 'Error fetching question'); }); } ); }
                    var edit = row.querySelector('.edit-question-btn'); if (edit && !edit._bound) { edit._bound = true; edit.addEventListener('click', function(){ var id = this.dataset.id; if (!id) return; var url = 'functions/questions.php?action=view&id=' + encodeURIComponent(id); fetchJson(url, { method: 'GET' }).then(function(res){ if (!res || !res.success) { showToast('error', res && res.error ? res.error : 'Failed'); return; } var d = res.data || {}; document.getElementById('e_id').value = d.id || ''; document.getElementById('e_question').value = d.question || ''; document.getElementById('e_a').value = d.opt_a || ''; document.getElementById('e_b').value = d.opt_b || ''; document.getElementById('e_c').value = d.opt_c || ''; document.getElementById('e_d').value = d.opt_d || ''; document.getElementById('e_correct').value = d.correct_ans || ''; document.getElementById('e_subject').value = d.subject_name || ''; document.getElementById('e_remarks').value = d.remarks || ''; var m = new bootstrap.Modal(document.getElementById('editQuestionModal')); m.show(); }).catch(function(err){ showToast('error', err.message || 'Error fetching question'); }); }); }
                    var del = row.querySelector('.delete-question-btn'); if (del && !del._bound) { del._bound = true; del.addEventListener('click', function(){ var id = this.dataset.id; if (!id) return; var modalEl = document.getElementById('deleteConfirmModal'); if (!modalEl) return; var tr = this.closest('tr'); var qtext = tr ? (tr.querySelector('td') ? tr.querySelector('td').textContent.trim() : '') : ''; var msg = qtext ? 'Delete question: "' + qtext + '"?' : 'Are you sure you want to delete this question?'; var textEl = document.getElementById('deleteConfirmText'); if (textEl) textEl.textContent = msg; modalEl.dataset.deleteId = id; var bs = bootstrap.Modal.getOrCreateInstance(modalEl); bs.show(); }); }
                }

                // simple HTML escape for insertion
                function escapeHtml(s) { if (s === null || typeof s === 'undefined') return ''; return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

                // Perform delete AJAX and call callback(success)
                function performDelete(id, cb) {
                    var form = new FormData();
                    form.append('action', 'delete');
                    form.append('id', id);
                    var csrf = document.querySelector('input[name="csrf_token"]');
                    if (csrf) form.append('csrf_token', csrf.value);

                    fetch('functions/questions.php', { method: 'POST', body: form }).then(function (resp) {
                        var ct = resp.headers.get('content-type') || '';
                        if (!resp.ok) return resp.text().then(function(t){ throw new Error(t || ('HTTP ' + resp.status)); });
                        if (ct.indexOf('application/json') === -1) return resp.text().then(function(t){ throw new Error(t || 'Unexpected response'); });
                        return resp.json();
                    }).then(function (data) {
                        if (!data || !data.success) {
                            showToast('error', data && data.error ? data.error : 'Delete failed');
                            if (cb) cb(false);
                            return;
                        }
                        // remove row
                        var rowBtn = document.querySelector('.delete-question-btn[data-id="' + id + '"]');
                        if (!rowBtn) rowBtn = document.querySelector('a.edit-question-btn[data-id="' + id + '"]') || document.querySelector('a.view-question-btn[data-id="' + id + '"]');
                        if (rowBtn) {
                            var tr = rowBtn.closest('tr');
                            if (tr) tr.remove();
                        }
                        showToast('success', 'Question deleted');
                        if (cb) cb(true);
                    }).catch(function (err) {
                        showToast('error', err.message || 'Error deleting question');
                        if (cb) cb(false);
                    });
                }

                // Initialize delete buttons
                enableDeleteButtons();

                // NOTE: Edit/update handlers removed per request — buttons and modal remain in HTML but no JS action.
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