<?php include "includes/init.php"; ?>


<?php
// DB and functions
require_once __DIR__ . '/../../db/dbcon.php';
require_once __DIR__ . '/functions/sections.php';

$section_msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    // CSRF protection
    if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['section_msg'] = ['type' => 'danger', 'text' => 'Invalid CSRF token. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $name = $_POST['section_name'] ?? '';
    $res = add_section($conn, $name);
    if ($res['success']) {
        $_SESSION['section_msg'] = ['type' => 'success', 'text' => 'Section added successfully.'];
    } else {
        $_SESSION['section_msg'] = ['type' => 'danger', 'text' => $res['error'] ?? 'Failed to add section.'];
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// If a message from a previous POST exists, pull it for display (PRG)
$section_msg = null;
if (!empty($_SESSION['section_msg'])) {
    $section_msg = $_SESSION['section_msg'];
    unset($_SESSION['section_msg']);
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
                            <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSectionModal">
                                <i class="feather-plus me-2"></i>
                                <span>Create Section</span>
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
                                                        <th>Section</th>
                                                        <th>Action</th>
                                                    </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sections = get_sections_list($conn);
                                            if (!empty($sections)) {
                                                foreach ($sections as $sec) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($sec['name']); ?></td>
                                                        <td>
                                                            <a href="#" class="btn-view-section text-secondary me-2 fs-5" data-id="<?php echo (int)$sec['id']; ?>" title="View">
                                                                <i class="feather-eye"></i>
                                                            </a>
                                                            <a href="#" class="btn-edit-section text-primary me-2 fs-5" data-id="<?php echo (int)$sec['id']; ?>" title="Edit">
                                                                <i class="feather-edit"></i>
                                                            </a>
                                                            <a href="#" class="btn-delete-section text-danger fs-5" data-id="<?php echo (int)$sec['id']; ?>" title="Delete">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="2">No sections found.</td></tr>';
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
    <!-- View Section Modal -->
    <div class="modal fade" id="viewSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Section Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="v_section_name"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editSectionForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="e_section_id" name="id" value="">
                    <div class="mb-3">
                        <label for="e_section_name" class="form-label">Section Name</label>
                        <input type="text" id="e_section_name" name="name" class="form-control" required>
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

    <!-- Delete Section Modal -->
    <div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this section?</p>
                    <input type="hidden" id="delete_section_id" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteSection" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var baseUrl = 'functions/sections.php';
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
            var view = tr.querySelector('.btn-view-section');
            if (view && !view._bound) { view._bound = true; view.addEventListener('click', function (e) { e.preventDefault(); var id = this.dataset.id; if (!id) return; fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' }).then(function(resp){ if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed'); return; } var d = resp.data || {}; document.getElementById('v_section_name').textContent = d.name || ''; var m = new bootstrap.Modal(document.getElementById('viewSectionModal')); m.show(); }).catch(function(err){ showToast('danger', err.message || 'Request failed'); }); }); }

            var edit = tr.querySelector('.btn-edit-section');
            if (edit && !edit._bound) { edit._bound = true; edit.addEventListener('click', function (e) { e.preventDefault(); var id = this.dataset.id; if (!id) return; fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' }).then(function(resp){ if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed'); return; } var d = resp.data || {}; document.getElementById('e_section_id').value = d.id || ''; document.getElementById('e_section_name').value = d.name || ''; var m = new bootstrap.Modal(document.getElementById('editSectionModal')); m.show(); }).catch(function(err){ showToast('danger', err.message || 'Request failed'); }); }); }

            var del = tr.querySelector('.btn-delete-section');
            if (del && !del._bound) { del._bound = true; del.addEventListener('click', function (e) { e.preventDefault(); var id = this.dataset.id; if (!id) return; document.getElementById('delete_section_id').value = id; var m = new bootstrap.Modal(document.getElementById('deleteSectionModal')); m.show(); }); }
        }

        // bind existing rows
        document.querySelectorAll('#myTable tbody tr').forEach(function (r) { bindRowButtons(r); });

        // Create form AJAX
        var createForm = document.getElementById('createSectionForm');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(createForm);
                fd.append('action', 'add');
                if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                fetchJson(baseUrl, { method: 'POST', body: fd }).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Add failed'); return; }
                    showToast('success', 'Section added');
                    var id = resp.id || (resp.data && resp.data.id) || '';
                    var name = (resp.data && resp.data.name) || fd.get('section_name');
                    var actionHtml = '<a href="#" class="btn-view-section text-secondary me-2 fs-5" data-id="' + id + '" title="View"><i class="feather-eye"></i></a>' +
                                     '<a href="#" class="btn-edit-section text-primary me-2 fs-5" data-id="' + id + '" title="Edit"><i class="feather-edit"></i></a>' +
                                     '<a href="#" class="btn-delete-section text-danger fs-5" data-id="' + id + '" title="Delete"><i class="feather-trash-2"></i></a>';
                    try {
                        if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#myTable')) {
                            var dt = $('#myTable').DataTable();
                            var newRow = dt.row.add([name, actionHtml]).draw(false).node();
                            bindRowButtons(newRow);
                        } else {
                            var tbody = document.querySelector('#myTable tbody');
                            if (tbody) {
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td>' + (name || '') + '</td><td>' + actionHtml + '</td>';
                                tbody.appendChild(tr);
                                bindRowButtons(tr);
                            }
                        }
                    } catch (err) { console.error(err); }
                    var mEl = document.getElementById('createSectionModal'); if (mEl) { var inst = bootstrap.Modal.getInstance(mEl) || new bootstrap.Modal(mEl); inst.hide(); }
                    createForm.reset();
                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
            });
        }

        // Submit edit form
        var editForm = document.getElementById('editSectionForm');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(editForm);
                fd.append('action', 'update');
                if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                fetchJson(baseUrl, { method: 'POST', body: fd }).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Update failed'); return; }
                    showToast('success', 'Section updated');
                    var id = fd.get('id');
                    try {
                        var rowBtn = document.querySelector('a.btn-edit-section[data-id="' + id + '"]');
                        if (!rowBtn) rowBtn = document.querySelector('a.btn-view-section[data-id="' + id + '"]');
                        if (rowBtn) {
                            var tr = rowBtn.closest('tr');
                            if (tr) {
                                var tds = tr.querySelectorAll('td');
                                if (tds && tds.length >= 1) tds[0].textContent = fd.get('name') || document.getElementById('e_section_name').value;
                            }
                        }
                    } catch (e) { console.error(e); }
                    var modal = bootstrap.Modal.getInstance(document.getElementById('editSectionModal'));
                    if (modal) modal.hide();
                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
            });
        }

        // Delete confirm
        var confirmDel = document.getElementById('confirmDeleteSection');
        if (confirmDel) {
            confirmDel.addEventListener('click', function () {
                var id = document.getElementById('delete_section_id').value;
                if (!id) return;
                var fd = new FormData(); fd.append('action', 'delete'); fd.append('id', id); fd.append('csrf_token', csrfToken);
                fetchJson(baseUrl, { method: 'POST', body: fd }).then(function (resp) {
                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Delete failed'); return; }
                    showToast('success', 'Section deleted');
                    try {
                        var rowBtn = document.querySelector('a.btn-delete-section[data-id="' + id + '"]');
                        if (!rowBtn) rowBtn = document.querySelector('a.btn-edit-section[data-id="' + id + '"]') || document.querySelector('a.btn-view-section[data-id="' + id + '"]');
                        if (rowBtn) {
                            var tr = rowBtn.closest('tr'); if (tr) tr.remove();
                        }
                    } catch (e) { console.error(e); }
                    var modal = bootstrap.Modal.getInstance(document.getElementById('deleteSectionModal'));
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