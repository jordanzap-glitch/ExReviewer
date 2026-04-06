<?php include "includes/init.php"; ?>


<?php
// DB and functions
require_once __DIR__ . '/../../db/dbcon.php';
require_once __DIR__ . '/functions/subjects.php';
require_once __DIR__ . '/functions/modules.php';

$module_msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    // CSRF protection
    if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['module_msg'] = ['type' => 'danger', 'text' => 'Invalid CSRF token. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $subjects_id = isset($_POST['subjects_id']) ? (int)$_POST['subjects_id'] : null;
    $file_path = '';
    if (!empty($_FILES['module_file']) && empty($_FILES['module_file']['error'])) {
        $u = $_FILES['module_file'];
        $uploadDir = __DIR__ . '/../../student/learning_modules';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $fname = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($u['name']));
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $fname;
        if (@move_uploaded_file($u['tmp_name'], $dest)) {
            $file_path = 'pages/student/learning_modules/' . $fname;
        }
    }
    if ($file_path === '') {
        $file_path = $_POST['file_path'] ?? '';
    }
    $res = add_module($conn, $file_path, $subjects_id);
    if (!empty($res['success'])) {
        $_SESSION['module_msg'] = ['type' => 'success', 'text' => 'Module added successfully.'];
    } else {
        $_SESSION['module_msg'] = ['type' => 'danger', 'text' => $res['error'] ?? 'Failed to add module.'];
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// If a message from a previous POST exists, pull it for display (PRG)
$module_msg = null;
    if (!empty($_SESSION['module_msg'])) {
    $module_msg = $_SESSION['module_msg'];
    unset($_SESSION['module_msg']);
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
    <title>EEReviewer || Modules</title>
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
                        <h5 class="m-b-10">Modules</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Modules</li>
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
                            <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModuleModal">
                                <i class="feather-plus me-2"></i>
                                <span>Create Module</span>
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
                                    <h5 class="card-title mb-0">Modules</h5>
                                </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="myTable" class="table table-hover mb-0">
                                        <thead>
                                                    <tr>
                                                        <th>Module</th>
                                                                                <th>Subject</th>
                                                        <th>Action</th>
                                                    </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $modules = get_modules_list($conn);
                                            if (!empty($modules)) {
                                                foreach ($modules as $m) {
                                                    ?>
                                                    <tr>
                                                        <td><?php $fp = $m['file_path'] ?? ''; $fn = $fp ? basename($fp) : ''; echo $fp ? '<a href="' . htmlspecialchars($fp) . '" target="_blank">' . htmlspecialchars($fn) . '</a>' : ''; ?></td>
                                                        <td><?php echo htmlspecialchars($m['subject_name'] ?? ''); ?></td>
                                                        <td>
                                                            <a href="#" class="btn-view-module text-info me-2 fs-5" data-id="<?php echo (int)$m['id']; ?>" title="View">
                                                                <i class="feather-eye"></i>
                                                            </a>
                                                            <a href="#" class="btn-edit-module text-primary me-2 fs-5" data-id="<?php echo (int)$m['id']; ?>" title="Edit">
                                                                <i class="feather-edit"></i>
                                                            </a>
                                                            <a href="#" class="btn-delete-module text-danger fs-5" data-id="<?php echo (int)$m['id']; ?>" title="Delete">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="3">No modules found.</td></tr>';
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
    <?php if (!empty($section_msg)): ?>
    var _section_msg = <?php echo json_encode($section_msg); ?>;
    document.addEventListener('DOMContentLoaded', function () {
        showToast(_section_msg.type, _section_msg.text);
    });
    <?php endif; ?>
    </script>
    <!--! ================================================================ !-->
    <!-- Create Section Modal (form POST) -->
    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" class="modal-content" id="createSectionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSectionLabel">Create Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="section_name" class="form-label">Section Name</label>
                        <input type="text" id="section_name" name="section_name" class="form-control" placeholder="Section name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" name="add_section" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
        <!-- Create Module Modal (form POST) -->
        <div class="modal fade" id="createModuleModal" tabindex="-1" aria-labelledby="createModuleLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" class="modal-content" id="createModuleForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Module</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <div class="mb-3">
                        <label for="module_file" class="form-label">Module File</label>
                        <input type="file" id="module_file" name="module_file" class="form-control" required>
                    </div>
                        <div class="mb-3">
                            <label for="module_subject" class="form-label">Subject</label>
                            <select id="module_subject" name="subjects_id" class="form-select">
                                <option value="">-- Select subject --</option>
                                <?php
                                $subjects = get_subjects($conn);
                                if (!empty($subjects)) {
                                    foreach ($subjects as $s) {
                                        echo '<option value="' . (int)$s['id'] . '">' . htmlspecialchars($s['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_module" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Module Modal -->
        <div class="modal fade" id="viewModuleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Module Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Name:</strong> <span id="v_module_name"></span></p>
                        <p><strong>Subject:</strong> <span id="v_module_section"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Module Modal -->
        <div class="modal fade" id="editModuleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="editModuleForm" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Module</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="e_module_id" name="id" value="">
                    <div class="mb-3">
                        <label class="form-label">Current File</label>
                        <div id="e_module_current" class="form-control-plaintext mb-2"></div>
                        <label for="e_module_file" class="form-label">Replace File (optional)</label>
                        <input type="file" id="e_module_file" name="module_file" class="form-control">
                    </div>
                        <div class="mb-3">
                            <label for="e_module_section" class="form-label">Subject</label>
                            <select id="e_module_section" name="subjects_id" class="form-select">
                                <option value="">-- Select subject --</option>
                                <?php
                                if (!empty($subjects)) {
                                    foreach ($subjects as $s) {
                                        echo '<option value="' . (int)$s['id'] . '">' . htmlspecialchars($s['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Module Modal -->
        <div class="modal fade" id="deleteModuleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this module?</p>
                        <input type="hidden" id="delete_module_id" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmDeleteModule" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var baseUrl = 'functions/modules.php';
        var csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

        function fetchJson(url, opts) {
            return fetch(url, opts).then(function (res) {
                var ct = res.headers.get('content-type') || '';
                if (!res.ok) return res.text().then(function(t){ throw new Error(t || ('HTTP ' + res.status)); });
                if (ct.indexOf('application/json') === -1) return res.text().then(function(t){ throw new Error(t || 'Unexpected response'); });
                return res.json();
            });
        }

        function bindRowButtons(tr) {
            if (!tr) return;
            var view = tr.querySelector('.btn-view-module');
            if (view && !view._bound) {
                view._bound = true;
                view.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id; if (!id) return;
                    fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' })
                    .then(function(resp){
                        if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed'); return; }
                        var d = resp.data || {};
                        var fp = d.file_path || '';
                        var fn = fp ? fp.split('/').pop() : '';
                        document.getElementById('v_module_name').textContent = fn || '';
                        document.getElementById('v_module_section').textContent = d.subject_name || '';
                        var m = new bootstrap.Modal(document.getElementById('viewModuleModal'));
                        m.show();
                    }).catch(function(err){ showToast('danger', err.message || 'Request failed'); });
                });
            }

            var edit = tr.querySelector('.btn-edit-module');
            if (edit && !edit._bound) {
                edit._bound = true;
                edit.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id; if (!id) return;
                    fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' })
                    .then(function(resp){
                        if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed'); return; }
                        var d = resp.data || {};
                        document.getElementById('e_module_id').value = d.id || '';
                        var fp = d.file_path || '';
                        var fn = fp ? fp.split('/').pop() : '';
                        document.getElementById('e_module_current').textContent = fn;
                        var fileInput = document.getElementById('e_module_file'); if (fileInput) fileInput.value = '';
                        if (d.subjects_id) document.getElementById('e_module_section').value = d.subjects_id; else document.getElementById('e_module_section').value = '';
                        var m = new bootstrap.Modal(document.getElementById('editModuleModal'));
                        m.show();
                    }).catch(function(err){ showToast('danger', err.message || 'Request failed'); });
                });
            }

            var del = tr.querySelector('.btn-delete-module');
            if (del && !del._bound) {
                del._bound = true;
                del.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = this.dataset.id; if (!id) return;
                    document.getElementById('delete_module_id').value = id;
                    var m = new bootstrap.Modal(document.getElementById('deleteModuleModal'));
                    m.show();
                });
            }
        }

        // bind existing rows
        document.querySelectorAll('#myTable tbody tr').forEach(function (r) { bindRowButtons(r); });

        // Create form AJAX
        var createForm = document.getElementById('createModuleForm');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(createForm);
                fd.append('action', 'add');
                if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                fetchJson(baseUrl, { method: 'POST', body: fd }).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Add failed'); return; }
                    showToast('success', 'Module added');
                    var id = resp.id || (resp.data && resp.data.id) || '';
                    var filePath = (resp.data && resp.data.file_path) || '';
                    var name = '';
                    if (filePath) name = filePath.split('/').pop();
                    else {
                        var fobj = fd.get('module_file'); if (fobj && fobj.name) name = fobj.name;
                    }
                    var sectionName = '';
                    if (resp.data && resp.data.subjects_id) {
                        // try to find from select
                        var sel = createForm.querySelector('select[name="subjects_id"]');
                        if (sel) {
                            var opt = sel.querySelector('option[value="' + resp.data.subjects_id + '"]');
                            if (opt) sectionName = opt.textContent;
                        }
                    } else {
                        var sel2 = createForm.querySelector('select[name="subjects_id"]');
                        if (sel2) { var sVal = sel2.value; var opt2 = sel2.querySelector('option[value="' + sVal + '"]'); if (opt2) sectionName = opt2.textContent; }
                    }
                    var actionHtml = '<a href="#" class="btn-view-module text-secondary me-2 fs-5" data-id="' + id + '" title="View"><i class="feather-eye"></i></a>' +
                                     '<a href="#" class="btn-edit-module text-primary me-2 fs-5" data-id="' + id + '" title="Edit"><i class="feather-edit"></i></a>' +
                                     '<a href="#" class="btn-delete-module text-danger fs-5" data-id="' + id + '" title="Delete"><i class="feather-trash-2"></i></a>';
                    try {
                        if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#myTable')) {
                            var dt = $('#myTable').DataTable();
                            var linkHtml = name ? '<a href="' + (filePath || ('learning_modules/' + name)) + '" target="_blank">' + name + '</a>' : '';
                            var newRow = dt.row.add([linkHtml, sectionName, actionHtml]).draw(false).node();
                            bindRowButtons(newRow);
                        } else {
                            var tbody = document.querySelector('#myTable tbody');
                            if (tbody) {
                                var tr = document.createElement('tr');
                                var linkHtml = name ? '<a href="' + (filePath || ('learning_modules/' + name)) + '" target="_blank">' + name + '</a>' : '';
                                tr.innerHTML = '<td>' + (linkHtml || '') + '</td><td>' + (sectionName || '') + '</td><td>' + actionHtml + '</td>';
                                tbody.appendChild(tr);
                                bindRowButtons(tr);
                            }
                        }
                    } catch (err) { console.error(err); }
                    var mEl = document.getElementById('createModuleModal'); if (mEl) { var inst = bootstrap.Modal.getInstance(mEl) || new bootstrap.Modal(mEl); inst.hide(); }
                    createForm.reset();
                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
            });
        }

        // Submit edit form
        var editForm = document.getElementById('editModuleForm');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(editForm);
                fd.append('action', 'update');
                if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                fetchJson(baseUrl, { method: 'POST', body: fd }).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Update failed'); return; }
                    showToast('success', 'Module updated');
                    var id = fd.get('id');
                    try {
                        var rowBtn = document.querySelector('a.btn-edit-module[data-id="' + id + '"]');
                        if (!rowBtn) rowBtn = document.querySelector('a.btn-view-module[data-id="' + id + '"]');
                        if (rowBtn) {
                            var tr = rowBtn.closest('tr');
                            if (tr) {
                                var tds = tr.querySelectorAll('td');
                                if (tds && tds.length >= 3) {
                                    var newName = '';
                                    if (resp && resp.data && resp.data.file_path) newName = resp.data.file_path.split('/').pop();
                                    else { var fobj = fd.get('module_file'); if (fobj && fobj.name) newName = fobj.name; }
                                    if (newName) {
                                        var newLink = '<a href="' + ((resp && resp.data && resp.data.file_path) || ('learning_modules/' + newName)) + '" target="_blank">' + newName + '</a>';
                                        tds[0].innerHTML = newLink;
                                    }
                                    var secOpt = document.getElementById('e_module_section');
                                    var secText = '';
                                    if (secOpt) { var o = secOpt.querySelector('option[value="' + (fd.get('subjects_id') || secOpt.value) + '"]'); if (o) secText = o.textContent; }
                                    tds[1].textContent = secText;
                                }
                            }
                        }
                    } catch (e) { console.error(e); }
                    var modal = bootstrap.Modal.getInstance(document.getElementById('editModuleModal'));
                    if (modal) modal.hide();
                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
            });
        }

        // Delete confirm
        var confirmDel = document.getElementById('confirmDeleteModule');
        if (confirmDel) {
            confirmDel.addEventListener('click', function () {
                var id = document.getElementById('delete_module_id').value;
                if (!id) return;
                var fd = new FormData(); fd.append('action', 'delete'); fd.append('id', id); fd.append('csrf_token', csrfToken);
                fetchJson(baseUrl, { method: 'POST', body: fd }).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Delete failed'); return; }
                    showToast('success', 'Module deleted');
                    try {
                        var rowBtn = document.querySelector('a.btn-delete-module[data-id="' + id + '"]');
                        if (!rowBtn) rowBtn = document.querySelector('a.btn-edit-module[data-id="' + id + '"]') || document.querySelector('a.btn-view-module[data-id="' + id + '"]');
                        if (rowBtn) {
                            var tr = rowBtn.closest('tr'); if (tr) tr.remove();
                        }
                    } catch (e) { console.error(e); }
                    var modal = bootstrap.Modal.getInstance(document.getElementById('deleteModuleModal'));
                    if (modal) modal.hide();
                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
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