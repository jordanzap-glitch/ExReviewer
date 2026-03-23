<?php include "includes/init.php"; ?>
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
                                    <h5 class="card-title mb-0">Take Exam</h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <span id="timerDisplay" class="badge bg-primary">15:00</span>
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
                                                <button id="submitBtn" class="btn btn-success btn-sm">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    function showToast(type, message, delay) {
        delay = delay || 3000;
        var toastEl = document.getElementById('nxlToast');
        var body = document.getElementById('nxlToastBody');
        if (!toastEl || !body) {
            alert(message);
            return;
        }
        toastEl.classList.remove('bg-success','bg-danger','bg-primary','bg-secondary');
        if (type === 'success') toastEl.classList.add('bg-success');
        else if (type === 'error' || type === 'danger') toastEl.classList.add('bg-danger');
        else toastEl.classList.add('bg-primary');
        body.textContent = message;
        var bsToast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: delay });
        bsToast.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Sample questions for the student to take — replace with server-provided data
        var questions = [
            {
                id: 1,
                question: 'What is the derivative of x^2?',
                answers: { A: '2x', B: 'x', C: '1', D: '0' },
                subject: 'Calculus I',
                year: '2024'
            },
            {
                id: 2,
                question: 'Which language uses printf?',
                answers: { A: 'Python', B: 'JavaScript', C: 'C', D: 'Ruby' },
                subject: 'Intro to Programming',
                year: '2024'
            }
        ];

        var current = 0;
        var answers = {};

        var examDuration = 15 * 60; // seconds (15 minutes)
        var examTimer = null;

        var startBtn = document.getElementById('startExamBtn');
        var examContainerEl = document.getElementById('examContainer');
        var timerDisplay = document.getElementById('timerDisplay');

        function formatTime(seconds) {
            var m = Math.floor(seconds / 60);
            var s = seconds % 60;
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }

        function startTimer() {
            var remaining = examDuration;
            timerDisplay.textContent = formatTime(remaining);
            examTimer = setInterval(function() {
                remaining--;
                timerDisplay.textContent = formatTime(remaining);
                if (remaining <= 0) {
                    clearInterval(examTimer);
                    showToast('error','Time is up — submitting exam');
                    submitBtn.click();
                }
            }, 1000);
        }

        startBtn && startBtn.addEventListener('click', function() {
            // show exam UI and start timer
            if (examContainerEl) examContainerEl.style.display = '';
            startBtn.disabled = true;
            startTimer();
        });

        var questionNumber = document.getElementById('questionNumber');
        var questionText = document.getElementById('questionText');
        var optionsList = document.getElementById('optionsList');
        var prevBtn = document.getElementById('prevBtn');
        var nextBtn = document.getElementById('nextBtn');
        var submitBtn = document.getElementById('submitBtn');

        function renderQuestion(index) {
            var q = questions[index];
            questionNumber.textContent = 'Question ' + (index + 1) + ' of ' + questions.length + ' — ' + q.subject + ' (' + q.year + ')';
            questionText.textContent = q.question;
            optionsList.innerHTML = '';
            for (var key in q.answers) {
                var id = 'opt_' + key;
                var div = document.createElement('div');
                div.className = 'form-check mb-2';
                div.innerHTML = '<input class="form-check-input" type="radio" name="examOption" id="' + id + '" value="' + key + '">'
                    + '<label class="form-check-label ms-2" for="' + id + '"><strong>' + key + '.</strong> ' + q.answers[key] + '</label>';
                optionsList.appendChild(div);
            }
            // restore selection if previously answered
            if (answers[q.id]) {
                var sel = document.querySelector('input[name="examOption"][value="' + answers[q.id] + '"]');
                if (sel) sel.checked = true;
            }
            prevBtn.disabled = (index === 0);
            nextBtn.disabled = (index === questions.length - 1);
        }

        prevBtn.addEventListener('click', function () {
            saveCurrentAnswer();
            if (current > 0) {
                current--;
                renderQuestion(current);
            }
        });

        nextBtn.addEventListener('click', function () {
            if (!saveCurrentAnswer()) { showToast('error','Please select an answer before continuing'); return; }
            if (current < questions.length - 1) {
                current++;
                renderQuestion(current);
            }
        });

        submitBtn.addEventListener('click', function () {
            if (!saveCurrentAnswer()) { showToast('error','Please select an answer before submitting'); return; }
            // Check all answered
            for (var i = 0; i < questions.length; i++) {
                if (!answers[questions[i].id]) { showToast('error','Please answer all questions'); return; }
            }
            // Submit payload (replace with AJAX call)
            var payload = questions.map(function(q) { return { id: q.id, answer: answers[q.id] }; });
            console.log('Student answers:', payload);
            showToast('success','Exam submitted — good luck!');
            // Optionally disable controls
            prevBtn.disabled = true; nextBtn.disabled = true; submitBtn.disabled = true;
        });

        function saveCurrentAnswer() {
            var sel = document.querySelector('input[name="examOption"]:checked');
            if (!sel) return false;
            var qid = questions[current].id;
            answers[qid] = sel.value;
            return true;
        }

        // initial render
        renderQuestion(current);
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