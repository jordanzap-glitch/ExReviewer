<?php include "includes/init.php"; ?>
<?php
// Ensure DB connection exists; try to include dbcon if not present
if (!isset($conn) && file_exists(__DIR__ . '/../../db/dbcon.php')) {
    require_once __DIR__ . '/../../db/dbcon.php';
}
// Load questions for selected subject
$questions = [];
$subject_name = '';
$subjects_id = isset($_GET['subjects_id']) ? (int)$_GET['subjects_id'] : 0;
if (isset($conn) && $subjects_id > 0) {
    try {
        $rows = [];
        if ($conn instanceof PDO) {
            // detect if exam_duration and question_items columns exist
            $hasDuration = false;
            $hasItems = false;
            try {
                $chk = $conn->query("SHOW COLUMNS FROM tbl_subjects LIKE 'exam_duration'");
                if ($chk && $chk->fetch()) $hasDuration = true;
            } catch (Exception $e) { $hasDuration = false; }
            try {
                $chk2 = $conn->query("SHOW COLUMNS FROM tbl_subjects LIKE 'question_items'");
                if ($chk2 && $chk2->fetch()) $hasItems = true;
            } catch (Exception $e) { $hasItems = false; }

            $fields = ['name'];
            if ($hasDuration) $fields[] = 'exam_duration';
            if ($hasItems) $fields[] = 'question_items';
            $sstmt = $conn->prepare("SELECT " . implode(', ', $fields) . " FROM tbl_subjects WHERE id = :id LIMIT 1");
            $sstmt->execute([':id' => $subjects_id]);
            $s = $sstmt->fetch(PDO::FETCH_ASSOC);
            $subject_name = $s['name'] ?? '';
            $exam_duration = ($hasDuration && isset($s['exam_duration'])) ? (int)$s['exam_duration'] : null;
            $question_items = ($hasItems && isset($s['question_items'])) ? (int)$s['question_items'] : null;

            $qstmt = $conn->prepare("SELECT id, question, opt_a, opt_b, opt_c, opt_d, correct_ans, subjects_id, remarks FROM tbl_question_bank WHERE subjects_id = :id ORDER BY id ASC");
            $qstmt->execute([':id' => $subjects_id]);
            $rows = $qstmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($conn instanceof mysqli) {
            $sid = (int)$subjects_id;
            // detect exam_duration and question_items columns
            $hasDuration = false; $hasItems = false;
            $chk = $conn->query("SHOW COLUMNS FROM `tbl_subjects` LIKE 'exam_duration'");
            if ($chk && $chk->num_rows > 0) $hasDuration = true;
            $chk2 = $conn->query("SHOW COLUMNS FROM `tbl_subjects` LIKE 'question_items'");
            if ($chk2 && $chk2->num_rows > 0) $hasItems = true;
            if ($hasDuration && $hasItems) {
                $sres = $conn->query("SELECT name, exam_duration, question_items FROM tbl_subjects WHERE id = {$sid} LIMIT 1");
                if ($sres) { $r = $sres->fetch_assoc(); $subject_name = $r['name'] ?? ''; $exam_duration = isset($r['exam_duration']) ? (int)$r['exam_duration'] : null; $question_items = isset($r['question_items']) ? (int)$r['question_items'] : null; }
            } elseif ($hasDuration) {
                $sres = $conn->query("SELECT name, exam_duration FROM tbl_subjects WHERE id = {$sid} LIMIT 1");
                if ($sres) { $r = $sres->fetch_assoc(); $subject_name = $r['name'] ?? ''; $exam_duration = isset($r['exam_duration']) ? (int)$r['exam_duration'] : null; }
            } elseif ($hasItems) {
                $sres = $conn->query("SELECT name, question_items FROM tbl_subjects WHERE id = {$sid} LIMIT 1");
                if ($sres) { $r = $sres->fetch_assoc(); $subject_name = $r['name'] ?? ''; $question_items = isset($r['question_items']) ? (int)$r['question_items'] : null; }
            } else {
                $sres = $conn->query("SELECT name FROM tbl_subjects WHERE id = {$sid} LIMIT 1");
                if ($sres) { $r = $sres->fetch_assoc(); $subject_name = $r['name'] ?? ''; }
            }
            $qres = $conn->query("SELECT id, question, opt_a, opt_b, opt_c, opt_d, correct_ans, subjects_id, remarks FROM tbl_question_bank WHERE subjects_id = {$sid} ORDER BY id ASC");
            if ($qres) { while ($row = $qres->fetch_assoc()) $rows[] = $row; }
        } else {
            $sid = (int)$subjects_id;
            $qres = @$conn->query("SELECT id, question, opt_a, opt_b, opt_c, opt_d, correct_ans, subjects_id, remarks FROM tbl_question_bank WHERE subjects_id = {$sid} ORDER BY id ASC");
            if ($qres && is_object($qres)) { while ($row = $qres->fetch_assoc()) $rows[] = $row; }
        }

        foreach ($rows as $r) {
            $questions[] = [
                'id' => (int)$r['id'],
                'question' => $r['question'],
                'answers' => [
                    'A' => $r['opt_a'],
                    'B' => $r['opt_b'],
                    'C' => $r['opt_c'],
                    'D' => $r['opt_d']
                ],
                'correct_ans' => $r['correct_ans'],
                'subjects_id' => (int)$r['subjects_id'],
                'remarks' => $r['remarks']
            ];
        }
    } catch (Exception $e) {
        // ignore DB errors to preserve layout
    }
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
    /* Ensure nav vertical line sits behind the circular nav buttons */
    #questionNav { position: relative; }
    .nav-line { z-index: 1; }
    #questionNav .qn-nav-btn { position: relative; z-index: 2; background: #fff; }
    #questionNav span { position: relative; z-index: 1; }
    </style>
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
                        <h5 class="m-b-10">Take Exam</h5>
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
                                <!-- Export dropdown and Save button removed for student view -->
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
                    <div class="col-lg-9">
                        <div class="card h-100">
                            <div class="card-header bg-soft-info border-soft-info text-info d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Take Exam
                                    <?php if (!empty($subject_name)): ?>
                                        &mdash; <span class="text-primary"><?php echo htmlspecialchars($subject_name); ?></span>
                                    <?php endif; ?>
                                </h5>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="timerDisplay" class="badge bg-primary">00:00</span>
                                    <button id="startExamBtn" class="btn btn-outline-primary btn-sm">Take Exam</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-3">
                                    <div id="examContainer" style="display:none">
                                        <div id="questionNumber" class="mb-2 fw-bold"></div>
                                        <div id="questionText" class="mb-3 fs-5"></div>
                                        <div id="optionsList" class="mb-3"></div>
                                        <div class="d-flex justify-content-between">
                                            <button id="prevBtn" class="btn btn-secondary btn-sm">Previous</button>
                                            <div>
                                                <button id="nextBtn" class="btn btn-primary btn-sm me-2">Next</button>
                                                <button id="submitBtn" class="btn btn-success btn-sm" style="display:none">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3" style="position:relative">
                        <div class="card h-100">
                            <div class="card-header bg-soft-secondary border-soft-secondary text-secondary">
                                <h6 class="card-title mb-0">Question Navigation</h6>
                            </div>
                            <div class="card-body" style="padding:0.75rem; position:relative;">
                                <div class="nav-line" style="position:absolute; left:50%; top:12px; bottom:12px; width:2px; background:#e9ecef; transform:translateX(-50%);"></div>
                                <div id="questionNav" class="d-flex align-items-center flex-column" style="height:100%; overflow-y:auto; gap:6px; padding:6px;"></div>
                            </div>
                        </div>
                            <div id="resultCard" class="card mt-3" style="display:none; position:absolute; left:6px; right:6px; top:auto; z-index:50">
                                <div class="card-header bg-soft-success border-soft-success text-success">
                                    <h6 class="card-title mb-0">Result</h6>
                                </div>
                                <div class="card-body">
                                    <div id="resultSummary" class="mb-3">Score: <span id="resultScore">0</span></div>
                                    <div id="resultChart"></div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <!-- Existing questions table removed for student view -->
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
    // embed server-provided questions and subject for client-side use
    var questions = <?php echo json_encode($questions, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?> || [];
    var examSubject = <?php echo json_encode($subject_name, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?> || '';
    var examDurationMinutes = <?php echo isset($exam_duration) && $exam_duration !== null ? (int)$exam_duration : 15; ?>;
    var examQuestionItems = <?php echo isset($question_items) && $question_items !== null ? (int)$question_items : 'null'; ?>;

    function showToast(type, message, delay) {
        delay = delay || 3000;
        var toastEl = document.getElementById('nxlToast');
        var body = document.getElementById('nxlToastBody');
        if (!toastEl || !body) { alert(message); return; }
        toastEl.classList.remove('bg-success','bg-danger','bg-primary','bg-secondary');
        if (type === 'success') toastEl.classList.add('bg-success');
        else if (type === 'error' || type === 'danger') toastEl.classList.add('bg-danger');
        else toastEl.classList.add('bg-primary');
        body.textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: delay }).show();
    }

    // Fisher-Yates shuffle
    function shuffleArray(a) {
        for (var i = a.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var tmp = a[i]; a[i] = a[j]; a[j] = tmp;
        }
        return a;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var current = 0;
        var answers = {};
        var examDuration = (parseInt(examDurationMinutes, 10) || 15) * 60; // seconds
        var examTimer = null;
        var examRemaining = 0; // seconds left (kept outside timer so submit can stop it)

        var startBtn = document.getElementById('startExamBtn');
        var examContainerEl = document.getElementById('examContainer');
        var timerDisplay = document.getElementById('timerDisplay');
        var questionNumber = document.getElementById('questionNumber');
        var questionText = document.getElementById('questionText');
        var optionsList = document.getElementById('optionsList');
        var prevBtn = document.getElementById('prevBtn');
        var nextBtn = document.getElementById('nextBtn');
        var submitBtn = document.getElementById('submitBtn');
        var questionNav = document.getElementById('questionNav');

        function buildNavButtonLabel(i) {
            return (i + 1).toString();
        }

            function handleNextClick() {
                // if on last question, treat Next as Submit
                if (current === questions.length - 1) {
                    saveCurrentAnswer();
                    submitExam(false);
                    return;
                }
                saveCurrentAnswer();
                if (current < questions.length - 1) { current++; renderQuestion(current); }
            }
            nextBtn.addEventListener('click', handleNextClick);

            function renderNav() {
                if (!questionNav) return;
                questionNav.innerHTML = '';
                for (var i = 0; i < questions.length; i++) {
                    (function(i){
                        var q = questions[i];
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'qn-nav-btn btn btn-sm';
                        btn.setAttribute('data-index', i);
                        btn.style.borderRadius = '50%';
                        btn.style.width = '36px';
                        btn.style.height = '36px';
                        btn.style.padding = '0';
                        btn.style.display = 'flex';
                        btn.style.alignItems = 'center';
                        btn.style.justifyContent = 'center';
                        btn.style.margin = '4px';
                        btn.style.border = '1px solid #ced4da';
                        btn.style.background = '#fff';
                        btn.textContent = buildNavButtonLabel(i);
                        var isVertical = questionNav.classList.contains('flex-column') || questionNav.classList.contains('align-items-center');
                        if (answers[q.id]) {
                            btn.classList.add('btn-success');
                            btn.style.color = '#fff';
                        } else {
                            btn.classList.add('btn-outline-secondary');
                        }
                        if (i === current) {
                            btn.style.boxShadow = '0 0 0 3px rgba(13,110,253,0.12)';
                        }
                        if (isVertical) {
                            btn.style.width = '40px';
                            btn.style.height = '40px';
                            btn.style.margin = '4px 0';
                        } else {
                            btn.style.width = '36px';
                            btn.style.height = '36px';
                            btn.style.margin = '4px';
                        }
                        btn.addEventListener('click', function(){
                            saveCurrentAnswer();
                            current = i;
                            renderQuestion(current);
                        });
                        questionNav.appendChild(btn);
                        if (i < questions.length - 1) {
                            var span = document.createElement('span');
                            if (isVertical) {
                                span.style.display = 'block';
                                span.style.width = '2px';
                                span.style.height = '12px';
                                span.style.background = '#e9ecef';
                                span.style.margin = '4px auto';
                            } else {
                                span.style.display = 'inline-block';
                                span.style.width = '28px';
                                span.style.height = '2px';
                                span.style.background = '#e9ecef';
                                span.style.margin = '0 6px';
                                span.style.alignSelf = 'center';
                            }
                            questionNav.appendChild(span);
                        }
                    })(i);
                }
            }

        function formatTime(seconds) { var m = Math.floor(seconds/60); var s = seconds%60; return String(m).padStart(2,'0')+':'+String(s).padStart(2,'0'); }

        function startTimer() {
            examRemaining = examDuration;
            timerDisplay.textContent = formatTime(examRemaining);
            examTimer = setInterval(function(){
                examRemaining--;
                timerDisplay.textContent = formatTime(examRemaining);
                if (examRemaining <= 0) {
                    clearInterval(examTimer);
                    examTimer = null;
                    showToast('error','Time is up — submitting exam');
                    submitExam(true);
                }
            }, 1000);
        }

        function prepareExam() {
            // shuffle questions
            shuffleArray(questions);
            // limit questions by subject's question_items if provided (shuffle then slice to get random subset)
            try {
                var items = parseInt(examQuestionItems, 10);
                if (!isNaN(items) && items > 0 && items < questions.length) {
                    questions = questions.slice(0, items);
                }
            } catch (e) {}
            // for each question, prepare shuffled answers array
            for (var i = 0; i < questions.length; i++) {
                var q = questions[i];
                var ansObj = q.answers || {};
                var arr = [];
                for (var k in ansObj) { arr.push({ key: k, text: ansObj[k] || '' }); }
                q.shuffledAnswers = shuffleArray(arr);
            }
            // build navigation UI (round buttons + lines)
            try { renderNav(); } catch (e) {}
        }

        function renderQuestion(index) {
            if (!questions || !questions.length) return;
            var q = questions[index];
            questionNumber.textContent = 'Question ' + (index + 1) + ' of ' + questions.length + (examSubject ? ' — ' + examSubject : '');
            questionText.textContent = q.question || '';
            optionsList.innerHTML = '';
            var choices = q.shuffledAnswers || [];
            for (var i = 0; i < choices.length; i++) {
                var item = choices[i];
                var id = 'opt_' + index + '_' + item.key;
                var div = document.createElement('div');
                div.className = 'form-check mb-2';
                div.innerHTML = '<input class="form-check-input" type="radio" name="examOption" id="' + id + '" value="' + item.key + '">'
                    + '<label class="form-check-label ms-2" for="' + id + '"><strong>' + item.key + '.</strong> ' + (item.text || '') + '</label>';
                optionsList.appendChild(div);
            }
            // restore selection if previously answered
            if (answers[q.id]) {
                var sel = document.querySelector('input[name="examOption"][value="' + answers[q.id] + '"]');
                if (sel) sel.checked = true;
            }
            prevBtn.disabled = (index === 0);
            // keep Next enabled even on last question (it becomes Submit)
            nextBtn.disabled = false;
            // If only one question, show Submit and hide Next. Otherwise, on last question make Next behave as Submit.
            try {
                if (questions.length === 1) {
                    if (submitBtn) submitBtn.style.display = '';
                    if (nextBtn) nextBtn.style.display = 'none';
                } else {
                    if (index === questions.length - 1) {
                        if (submitBtn) submitBtn.style.display = 'none';
                        if (nextBtn) { nextBtn.style.display = ''; nextBtn.textContent = 'Submit'; }
                    } else {
                        if (submitBtn) submitBtn.style.display = 'none';
                        if (nextBtn) { nextBtn.style.display = ''; nextBtn.textContent = 'Next'; }
                    }
                }
            } catch (e) {}
            try { renderNav(); } catch (e) {}
        }

        function saveCurrentAnswer() {
            var sel = document.querySelector('input[name="examOption"]:checked');
            if (!sel) return false;
            var qid = questions[current].id;
            answers[qid] = sel.value;
            return true;
        }

        prevBtn.addEventListener('click', function(){ saveCurrentAnswer(); if (current > 0) { current--; renderQuestion(current); } });

        function submitExam(force) {
            force = !!force;
            // Save current answer if possible
            saveCurrentAnswer();

            // If not forcing, ensure all answered
            if (!force) {
                for (var i = 0; i < questions.length; i++) { if (!answers[questions[i].id]) { showToast('error','Please answer all questions'); return; } }
            }

            // compute score (treat unanswered as incorrect)
            var correct = 0;
            for (var i = 0; i < questions.length; i++) {
                var q = questions[i];
                if (!q || typeof q.correct_ans === 'undefined') continue;
                if (answers[q.id] && String(answers[q.id]) === String(q.correct_ans)) correct++;
            }
            var payload = {
                subjects_id: <?php echo (int)$subjects_id; ?>,
                score: correct,
                details: questions.map(function(q){ return { id: q.id, answer: answers[q.id] || null, correct: q.correct_ans }; })
            };

            // stop the timer immediately upon submission
            if (examTimer) { clearInterval(examTimer); examTimer = null; }

            // disable UI to prevent further changes
            try {
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
                // disable all option inputs
                document.querySelectorAll('input[name="examOption"]').forEach(function(i){ i.disabled = true; });
                // disable nav buttons
                document.querySelectorAll('.qn-nav-btn').forEach(function(b){ b.disabled = true; });
            } catch (e) { /* ignore */ }

            fetch('functions/attempts.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(function(res){ return res.json(); }).then(function(json){
                if (json && json.success) {
                    showToast('success','Exam submitted — score: ' + payload.score);
                    try { renderResult(payload.score, questions.length, json.id); } catch (e) { console.error(e); }
                } else {
                    showToast('error',(json && json.error) ? json.error : 'Submission failed');
                }
            }).catch(function(err){ showToast('error', err.message || 'Request failed'); });
        }

        // wire submit button to normal (non-forced) submit
        submitBtn.addEventListener('click', function(){ submitExam(false); });

        // initialize
        if (!questions || !questions.length) {
            showToast('error','No questions available for this subject');
            if (startBtn) startBtn.disabled = true;
        }

        startBtn && startBtn.addEventListener('click', function() {
            if (!questions || !questions.length) return;
            prepareExam();
            current = 0; answers = {};
            if (examContainerEl) examContainerEl.style.display = '';
            startBtn.disabled = true;
            startTimer();
            renderQuestion(current);
        });

        function renderResult(correct, total, attemptId) {
            correct = parseInt(correct,10) || 0;
            total = parseInt(total,10) || 0;
            var incorrect = Math.max(0, total - correct);
            var pct = total > 0 ? Math.round((correct/total)*100) : 0;
            var scoreEl = document.getElementById('resultScore');
            if (scoreEl) scoreEl.textContent = correct + ' / ' + total + ' (' + pct + '%)';
            // show card (absolutely position it below the navigation so it doesn't change left column layout)
            var card = document.getElementById('resultCard');
            if (card) {
                card.style.display = '';
                try {
                    var nav = document.getElementById('questionNav');
                    var parent = card.parentElement; // the col-lg-3 with position:relative
                    if (nav && parent) {
                        var top = nav.offsetTop + nav.offsetHeight + 12; // px inside parent
                        card.style.top = top + 'px';
                    }
                } catch (e) { console.error(e); }
            }
            // render Apex donut/pie
            try {
                var chartEl = document.getElementById('resultChart');
                if (!chartEl) return;
                chartEl.innerHTML = '';
                var options = {
                    chart: { type: 'donut', height: 250 },
                    series: [correct, incorrect],
                    labels: ['Correct', 'Incorrect'],
                    colors: ['#28a745', '#dc3545'],
                    legend: { position: 'bottom' },
                    tooltip: { y: { formatter: function(val){ return val + ' items'; } } }
                };
                var chart = new ApexCharts(chartEl, options);
                chart.render();
            } catch (e) { console.error(e); }
            // scroll to result card
            try { if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
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