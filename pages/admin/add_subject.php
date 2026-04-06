<?php include "includes/init.php"; ?>


<?php
// DB and functions
require_once __DIR__ . '/../../db/dbcon.php';
require_once __DIR__ . '/functions/subjects.php';

$subject_msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    // CSRF protection: verify token
    if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => 'Invalid CSRF token. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $name = $_POST['subject_name'] ?? '';
    $code = $_POST['subject_code'] ?? '';
    $exam_duration = isset($_POST['exam_duration']) && $_POST['exam_duration'] !== '' ? (int)$_POST['exam_duration'] : null;
    $question_items = isset($_POST['question_items']) && $_POST['question_items'] !== '' ? (int)$_POST['question_items'] : null;
    $res = add_subject($conn, $name, $code, $exam_duration, $question_items);
    if ($res['success']) {
        $_SESSION['subject_msg'] = ['type' => 'success', 'text' => 'Subject added successfully.'];
    } else {
        $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => $res['error'] ?? 'Failed to add subject.'];
    }
    // PRG: redirect to avoid form resubmission on refresh
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// If a message from a previous POST exists, pull it for display (PRG)
$subject_msg = null;
if (!empty($_SESSION['subject_msg'])) {
    $subject_msg = $_SESSION['subject_msg'];
    unset($_SESSION['subject_msg']);
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
    <title>EEReviewer || Subjects</title>
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
                        <h5 class="m-b-10">Subjects</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Subjects</li>
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
                            <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                                <i class="feather-plus me-2"></i>
                                <span>Create Subject</span>
                            </a>
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
                                    <h5 class="card-title mb-0">Subjects</h5>
                                </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="myTable" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Code</th>
                                                <th>Duration (min)</th>
                                                <th>Items</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $subjects = get_subjects($conn);
                                            if (!empty($subjects)) {
                                                foreach ($subjects as $sub) {
                                                    ?>
                                                    <tr>
                                                                <td><?php echo htmlspecialchars($sub['name']); ?></td>
                                                                <td><?php echo htmlspecialchars($sub['code']); ?></td>
                                                                <td><?php echo isset($sub['exam_duration']) ? (int)$sub['exam_duration'] : ''; ?></td>
                                                                <td><?php echo isset($sub['question_items']) ? (int)$sub['question_items'] : ''; ?></td>
                                                                <td>
                                                            <a href="#" class="btn-view-subject text-info me-2 fs-5" data-id="<?php echo (int)$sub['id']; ?>" title="View">
                                                                <i class="feather-eye"></i>
                                                            </a>
                                                            <a href="#" class="btn-edit-subject text-primary me-2 fs-5" data-id="<?php echo (int)$sub['id']; ?>" title="Edit">
                                                                <i class="feather-edit"></i>
                                                            </a>
                                                            <a href="#" class="btn-delete-subject text-danger fs-5" data-id="<?php echo (int)$sub['id']; ?>" title="Delete">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="4">No subjects found.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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
    <?php if (!empty($subject_msg)): ?>
    var _subject_msg = <?php echo json_encode($subject_msg); ?>;
    document.addEventListener('DOMContentLoaded', function () {
        showToast(_subject_msg.type, _subject_msg.text);
    });
    <?php endif; ?>
    </script>
    <!-- View / Edit / Delete Modals -->
    <div class="modal fade" id="viewSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subject Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="viewSubjectName"></span></p>
                    <p><strong>Code:</strong> <span id="viewSubjectCode"></span></p>
                    <p><strong>Duration (min):</strong> <span id="viewSubjectDuration"></span></p>
                    <p><strong>Items:</strong> <span id="viewSubjectItems"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editSubjectForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_subject_name" class="form-label">Subject Name</label>
                        <input type="text" id="edit_subject_name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_code" class="form-label">Subject Code</label>
                        <input type="text" id="edit_subject_code" name="code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_duration" class="form-label">Exam Duration (minutes)</label>
                        <input type="number" id="edit_subject_duration" name="exam_duration" class="form-control" min="0" placeholder="e.g. 30">
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_items" class="form-label">Question Items</label>
                        <input type="number" id="edit_subject_items" name="question_items" class="form-control" min="0" placeholder="e.g. 50">
                    </div>
                    <input type="hidden" id="edit_subject_id" name="id" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this subject?</p>
                    <input type="hidden" id="delete_subject_id" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteSubject" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var baseUrl = 'functions/subjects.php';
        var csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

        function ajaxPost(data) {
            return fetch(baseUrl, { method: 'POST', body: data }).then(function (res) { return res.json(); });
        }

        // View
        document.querySelectorAll('.btn-view-subject').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id;
                fetch(baseUrl + '?action=view&id=' + encodeURIComponent(id))
                    .then(function (r) { return r.json(); })
                    .then(function (resp) {
                        if (resp.success) {
                            document.getElementById('viewSubjectName').textContent = resp.data.name;
                            document.getElementById('viewSubjectCode').textContent = resp.data.code;
                            document.getElementById('viewSubjectDuration').textContent = resp.data.exam_duration !== undefined ? resp.data.exam_duration : '';
                            document.getElementById('viewSubjectItems').textContent = resp.data.question_items !== undefined ? resp.data.question_items : '';
                            var m = new bootstrap.Modal(document.getElementById('viewSubjectModal'));
                            m.show();
                        } else {
                            showToast('danger', resp.error || 'Could not load subject');
                        }
                    }).catch(function () { showToast('danger', 'Request failed'); });
            });
        });

        // Edit - open modal and populate
        document.querySelectorAll('.btn-edit-subject').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id;
                fetch(baseUrl + '?action=view&id=' + encodeURIComponent(id))
                    .then(function (r) { return r.json(); })
                    .then(function (resp) {
                        if (resp.success) {
                            document.getElementById('edit_subject_name').value = resp.data.name;
                            document.getElementById('edit_subject_code').value = resp.data.code;
                            document.getElementById('edit_subject_duration').value = resp.data.exam_duration !== undefined ? resp.data.exam_duration : '';
                            document.getElementById('edit_subject_items').value = resp.data.question_items !== undefined ? resp.data.question_items : '';
                            document.getElementById('edit_subject_id').value = resp.data.id;
                            var m = new bootstrap.Modal(document.getElementById('editSubjectModal'));
                            m.show();
                        } else {
                            showToast('danger', resp.error || 'Could not load subject');
                        }
                    }).catch(function () { showToast('danger', 'Request failed'); });
            });
        });

        // Edit form submit
        document.getElementById('editSubjectForm').addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(this);
            fd.append('action', 'update');
            // ensure csrf present
            if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
            ajaxPost(fd).then(function (resp) {
                if (resp && resp.success) {
                    showToast('success', 'Subject updated');
                    try {
                        var sid = fd.get('id') || document.getElementById('edit_subject_id').value;
                        if (sid) {
                            var rowBtn = document.querySelector('a.btn-edit-subject[data-id="' + sid + '"]');
                            if (!rowBtn) rowBtn = document.querySelector('a.btn-view-subject[data-id="' + sid + '"]');
                            if (rowBtn) {
                                var tr = rowBtn.closest('tr');
                                if (tr) {
                                    var tds = tr.querySelectorAll('td');
                                    if (tds && tds.length >= 4) {
                                        tds[0].textContent = fd.get('name') || fd.get('subject_name') || document.getElementById('edit_subject_name').value;
                                        tds[1].textContent = fd.get('code') || fd.get('subject_code') || document.getElementById('edit_subject_code').value;
                                        // duration may be present as exam_duration
                                        tds[2].textContent = fd.get('exam_duration') || document.getElementById('edit_subject_duration').value || '';
                                        // question items
                                        tds[3].textContent = fd.get('question_items') || document.getElementById('edit_subject_items').value || '';
                                    }
                                }
                            }
                        }
                    } catch (e) { console.error(e); }
                    var modal = bootstrap.Modal.getInstance(document.getElementById('editSubjectModal'));
                    if (modal) modal.hide();
                } else {
                    showToast('danger', resp && resp.error ? resp.error : 'Update failed');
                }
            }).catch(function () { showToast('danger', 'Request failed'); });
        });

        // Delete - open confirm
        document.querySelectorAll('.btn-delete-subject').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id;
                document.getElementById('delete_subject_id').value = id;
                var m = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
                m.show();
            });
        });

        document.getElementById('confirmDeleteSubject').addEventListener('click', function () {
            var id = document.getElementById('delete_subject_id').value;
            var fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fd.append('csrf_token', csrfToken);
            ajaxPost(fd).then(function (resp) {
                if (resp && resp.success) {
                    showToast('success', 'Subject deleted');
                    try {
                        var rowBtn = document.querySelector('a.btn-delete-subject[data-id="' + id + '"]');
                        if (!rowBtn) rowBtn = document.querySelector('a.btn-edit-subject[data-id="' + id + '"]') || document.querySelector('a.btn-view-subject[data-id="' + id + '"]');
                        if (rowBtn) {
                            var tr = rowBtn.closest('tr');
                            if (tr) tr.remove();
                        }
                    } catch (e) { console.error(e); }
                    var modal = bootstrap.Modal.getInstance(document.getElementById('deleteSubjectModal'));
                    if (modal) modal.hide();
                } else {
                    showToast('danger', resp && resp.error ? resp.error : 'Delete failed');
                }
            }).catch(function () { showToast('danger', 'Request failed'); });
        });
    });
    </script>
    <!--! ================================================================ !-->
    <!-- Create Subject Modal (form POST) -->
    <div class="modal fade" id="createSubjectModal" tabindex="-1" aria-labelledby="createSubjectLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" class="modal-content" id="createSubjectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubjectLabel">Create Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subject_name" class="form-label">Subject Name</label>
                        <input type="text" id="subject_name" name="subject_name" class="form-control" placeholder="Subject name" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject_code" class="form-label">Subject Code</label>
                        <input type="text" id="subject_code" name="subject_code" class="form-control" placeholder="Subject code" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject_duration" class="form-label">Exam Duration (minutes)</label>
                        <input type="number" id="subject_duration" name="exam_duration" class="form-control" min="0" placeholder="e.g. 30">
                    </div>
                    <div class="mb-3">
                        <label for="subject_items" class="form-label">Question Items</label>
                        <input type="number" id="subject_items" name="question_items" class="form-control" min="0" placeholder="e.g. 50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('createSubjectForm');
        var baseUrl = 'functions/subjects.php';
        var csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

        function ajaxPost(fd) {
            return fetch(baseUrl, { method: 'POST', body: fd }).then(function (res) {
                var ct = res.headers.get('content-type') || '';
                if (!res.ok) return res.text().then(function(t){ throw new Error(t || ('HTTP ' + res.status)); });
                if (ct.indexOf('application/json') === -1) return res.text().then(function(t){ throw new Error(t || 'Unexpected response'); });
                return res.json();
            });
        }

        function bindRowButtons(tr) {
            if (!tr) return;
            var viewBtn = tr.querySelector('.btn-view-subject');
            if (viewBtn && !viewBtn._bound) {
                viewBtn._bound = true;
                viewBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id;
                    fetch(baseUrl + '?action=view&id=' + encodeURIComponent(id))
                        .then(function (r) { return r.json(); })
                        .then(function (resp) {
                            if (resp && resp.success) {
                                document.getElementById('viewSubjectName').textContent = resp.data.name;
                                document.getElementById('viewSubjectCode').textContent = resp.data.code;
                                    document.getElementById('viewSubjectDuration').textContent = resp.data.exam_duration !== undefined ? resp.data.exam_duration : '';
                                    document.getElementById('viewSubjectItems').textContent = resp.data.question_items !== undefined ? resp.data.question_items : '';
                                var m = new bootstrap.Modal(document.getElementById('viewSubjectModal'));
                                m.show();
                            } else {
                                showToast('danger', resp && resp.error ? resp.error : 'Could not load subject');
                            }
                        }).catch(function () { showToast('danger', 'Request failed'); });
                });
                });
            }
            var editBtn = tr.querySelector('.btn-edit-subject');
            if (editBtn && !editBtn._bound) {
                editBtn._bound = true;
                editBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id;
                    fetch(baseUrl + '?action=view&id=' + encodeURIComponent(id))
                        .then(function (r) { return r.json(); })
                        .then(function (resp) {
                            if (resp && resp.success) {
                                document.getElementById('edit_subject_name').value = resp.data.name;
                                document.getElementById('edit_subject_code').value = resp.data.code;
                                document.getElementById('edit_subject_duration').value = resp.data.exam_duration !== undefined ? resp.data.exam_duration : '';
                                document.getElementById('edit_subject_items').value = resp.data.question_items !== undefined ? resp.data.question_items : '';
                                document.getElementById('edit_subject_id').value = resp.data.id;
                                var m = new bootstrap.Modal(document.getElementById('editSubjectModal'));
                                m.show();
                            } else {
                                showToast('danger', resp && resp.error ? resp.error : 'Could not load subject');
                            }
                        }).catch(function () { showToast('danger', 'Request failed'); });
                });
                });
            }
            var delBtn = tr.querySelector('.btn-delete-subject');
            if (delBtn && !delBtn._bound) {
                delBtn._bound = true;
                delBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id;
                    document.getElementById('delete_subject_id').value = id;
                    var m = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
                    m.show();
                });
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(form);
                fd.append('action', 'add');
                if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                ajaxPost(fd).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Add failed'); return; }
                    showToast('success', 'Subject added');
                    var id = resp.id || (resp.data && resp.data.id) || '';
                    var name = (resp.data && resp.data.name) || fd.get('subject_name');
                    var code = (resp.data && resp.data.code) || fd.get('subject_code');
                    var duration = (resp.data && typeof resp.data.exam_duration !== 'undefined') ? resp.data.exam_duration : (fd.get('exam_duration') || '');
                    var items = (resp.data && typeof resp.data.question_items !== 'undefined') ? resp.data.question_items : (fd.get('question_items') || '');
                    var actionHtml = '<a href="#" class="btn-view-subject text-primary me-2 fs-5" data-id="' + id + '" title="View"><i class="feather-eye"></i></a>' +
                                     '<a href="#" class="btn-edit-subject text-primary me-2 fs-5" data-id="' + id + '" title="Edit"><i class="feather-edit"></i></a>' +
                                     '<a href="#" class="btn-delete-subject text-danger fs-5" data-id="' + id + '" title="Delete"><i class="feather-trash-2"></i></a>';
                    try {
                        if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#myTable')) {
                            var dt = $('#myTable').DataTable();
                            var newRow = dt.row.add([name, code, duration, items, actionHtml]).draw(false).node();
                            bindRowButtons(newRow);
                        } else {
                            var tbody = document.querySelector('#myTable tbody');
                            if (tbody) {
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td>' + (name || '') + '</td><td>' + (code || '') + '</td><td>' + (duration || '') + '</td><td>' + (items || '') + '</td><td>' + actionHtml + '</td>';
                                tbody.appendChild(tr);
                                bindRowButtons(tr);
                            }
                        }
                    } catch (err) { console.error(err); }

                    // hide modal and reset
                    var mEl = document.getElementById('createSubjectModal');
                    if (mEl) {
                        var inst = bootstrap.Modal.getInstance(mEl) || new bootstrap.Modal(mEl);
                        inst.hide();
                    }
                    form.reset();
                }).catch(function () { showToast('danger', 'Request failed'); });
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
    <script>
    // Auto-dismiss alerts with class .auto-dismiss after 3 seconds
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.querySelector('.auto-dismiss');
        if (el) {
            setTimeout(function () { el.remove(); }, 3000);
        }
    });
    </script>
    <!--! END: Theme Customizer !-->
</body>

</html>