<?php include "includes/init.php"; ?>
<?php
// DB and functions for users
require_once __DIR__ . '/../../db/dbcon.php';
require_once __DIR__ . '/functions/users.php';

$user_msg = null;
// fetch dropdown data
$sections = get_sections($conn);
$academic_years = get_active_academicyears($conn);
// fetch usertypes for edit dropdown
$usertypes = get_usertypes($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['user_msg'] = ['type' => 'danger', 'text' => 'Invalid CSRF token. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    // The section dropdown posts the section NAME; resolve it to an id here
    $posted_section = trim($_POST['section_id'] ?? '');
    $resolved_section_id = 0;
    if ($posted_section !== '') {
        $chk = mysqli_prepare($conn, "SELECT id FROM tbl_sections WHERE name = ? LIMIT 1");
        if ($chk) {
            mysqli_stmt_bind_param($chk, 's', $posted_section);
            mysqli_stmt_execute($chk);
            $res_chk = mysqli_stmt_get_result($chk);
            if ($row_chk = mysqli_fetch_assoc($res_chk)) {
                $resolved_section_id = (int)$row_chk['id'];
            }
            mysqli_stmt_close($chk);
        }
        if ($resolved_section_id === 0) {
            $_SESSION['user_msg'] = ['type' => 'danger', 'text' => 'Selected section not found.'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    $data = [
        'last_name' => $_POST['lastname'] ?? '',
        'first_name' => $_POST['firstname'] ?? '',
        'middle_name' => $_POST['middlename'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'year_level' => $_POST['year_level'] ?? '',
        'section_id' => $resolved_section_id,
        'academicyears_id' => $_POST['academicyears_id'] ?? 0,
        'usertypes_id' => isset($_POST['usertypes_id']) ? (int)$_POST['usertypes_id'] : 0,
    ];
    $res = add_user($conn, $data);
    if ($res['success']) {
        $_SESSION['user_msg'] = ['type' => 'success', 'text' => 'User added successfully.'];
    } else {
        $_SESSION['user_msg'] = ['type' => 'danger', 'text' => $res['error'] ?? 'Failed to add user.'];
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// If a message from a previous POST exists, pull it for display (PRG)
$user_msg = null;
if (!empty($_SESSION['user_msg'])) {
    $user_msg = $_SESSION['user_msg'];
    unset($_SESSION['user_msg']);
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
    <title>EEReviewer || Users</title>
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
                        <h5 class="m-b-10">Users</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Users</li>
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
                            <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="feather-plus me-2"></i>
                                <span>Create User</span>
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
                                    <h5 class="card-title mb-0">Users</h5>
                                </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="myTable" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $users = get_users($conn);
                                            if (!empty($users)) {
                                                foreach ($users as $u) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($u['role'] ?? ''); ?></td>
                                                        <td>
                                                            <a href="#" class="btn-view-user text-info me-2 fs-5" data-id="<?php echo (int)$u['id']; ?>" title="View">
                                                                <i class="feather-eye"></i>
                                                            </a>
                                                            <a href="#" class="btn-edit-user text-primary me-2 fs-5" data-id="<?php echo (int)$u['id']; ?>" title="Edit">
                                                                <i class="feather-edit"></i>
                                                            </a>
                                                            <?php if (!empty($u['is_superadmin'])): ?>
                                                                <a href="#" class="text-muted fs-5" aria-disabled="true" title="Super admin cannot be deleted">
                                                                    <i class="feather-trash-2"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="#" class="btn-delete-user text-danger fs-5" data-id="<?php echo (int)$u['id']; ?>" title="Delete">
                                                                    <i class="feather-trash-2"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="4">No users found.</td></tr>';
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

                <!-- View User Modal (read-only) -->
                <div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">User Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Last name:</strong> <span id="v_lastname"></span></p>
                                <p><strong>First name:</strong> <span id="v_firstname"></span></p>
                                <p><strong>Middle name:</strong> <span id="v_middlename"></span></p>
                                <p><strong>Email:</strong> <span id="v_email"></span></p>
                                <p><strong>Year level:</strong> <span id="v_year_level"></span></p>
                                <p><strong>Section:</strong> <span id="v_section"></span></p>
                                <p><strong>Academic year:</strong> <span id="v_academicyear"></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit User Modal -->
                <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form id="editUserForm" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="e_id" name="id" value="">
                                <div class="mb-3">
                                    <label for="e_lastname" class="form-label">Last name</label>
                                    <input type="text" id="e_lastname" name="last_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="e_firstname" class="form-label">First name</label>
                                    <input type="text" id="e_firstname" name="first_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="e_middlename" class="form-label">Middle name</label>
                                    <input type="text" id="e_middlename" name="middle_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="e_email" class="form-label">Email</label>
                                    <input type="email" id="e_email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="e_password" class="form-label">Password (leave blank to keep current)</label>
                                    <input type="password" id="e_password" name="password" class="form-control">
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="e_year_level" class="form-label">Year level</label>
                                        <select id="e_year_level" name="year_level" class="form-select" required>
                                            <option value="">Select Year</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="Graduate">Graduate</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="e_section_id" class="form-label">Section</label>
                                        <select id="e_section_id" name="section_id" class="form-select" required>
                                            <option value="">Select Section</option>
                                            <?php foreach ($sections as $s): ?>
                                                <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="e_academicyears_id" class="form-label">Academic year</label>
                                        <select id="e_academicyears_id" name="academicyears_id" class="form-select" required>
                                            <option value="">Select AY</option>
                                            <?php foreach ($academic_years as $ay): ?>
                                                <option value="<?php echo (int)$ay['id']; ?>"><?php echo htmlspecialchars($ay['sy_start'] . ' - ' . $ay['sy_end']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="e_usertypes_id" class="form-label">User type</label>
                                        <select id="e_usertypes_id" name="usertypes_id" class="form-select">
                                            <option value="">Select type</option>
                                            <?php foreach ($usertypes as $ut): ?>
                                                <option value="<?php echo (int)$ut['id']; ?>"><?php echo htmlspecialchars($ut['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
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

                <!-- Delete User Modal -->
                <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this user?</p>
                                <input type="hidden" id="delete_user_id" value="">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" id="confirmDeleteUser" class="btn btn-danger">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var baseUrl = 'functions/users.php';
                    var csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

                    function fetchJson(url, opts) {
                        return fetch(url, opts).then(function (res) {
                            var ct = res.headers.get('content-type') || '';
                            if (!res.ok) return res.text().then(function(t){ throw new Error(t || ('HTTP ' + res.status)); });
                            if (ct.indexOf('application/json') === -1) return res.text().then(function(t){ throw new Error(t || 'Unexpected response'); });
                            return res.json();
                        });
                    }

                    // View
                    document.querySelectorAll('.btn-view-user').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            var id = this.dataset.id;
                            if (!id) return;
                            fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' })
                                .then(function (resp) {
                                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed to load user'); return; }
                                    var d = resp.data || {};
                                    document.getElementById('v_lastname').textContent = d.last_name || '';
                                    document.getElementById('v_firstname').textContent = d.first_name || '';
                                    document.getElementById('v_middlename').textContent = d.middle_name || '';
                                    document.getElementById('v_email').textContent = d.email || '';
                                    document.getElementById('v_year_level').textContent = d.year_level || '';
                                    // show human-readable section and academic year when available
                                    document.getElementById('v_section').textContent = d.section_name || (d.sections_id || '');
                                    var ayText = '';
                                    if (d.sy_start && d.sy_end) ayText = d.sy_start + ' - ' + d.sy_end;
                                    else if (d.academicyears_id) ayText = d.academicyears_id;
                                    document.getElementById('v_academicyear').textContent = ayText;
                                    var m = new bootstrap.Modal(document.getElementById('viewUserModal'));
                                    m.show();
                                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
                        });
                    });

                    // Edit - open modal and populate
                    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            var id = this.dataset.id;
                            if (!id) return;
                            fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' })
                                .then(function (resp) {
                                    if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed to load user'); return; }
                                    var d = resp.data || {};
                                    document.getElementById('e_id').value = d.id || '';
                                    document.getElementById('e_lastname').value = d.last_name || '';
                                    document.getElementById('e_firstname').value = d.first_name || '';
                                    document.getElementById('e_middlename').value = d.middle_name || '';
                                    document.getElementById('e_email').value = d.email || '';
                                    document.getElementById('e_password').value = '';
                                    document.getElementById('e_year_level').value = d.year_level || '';
                                    // select section by id
                                    var sec = document.getElementById('e_section_id'); if (sec) sec.value = d.sections_id || '';
                                    var ay = document.getElementById('e_academicyears_id'); if (ay) ay.value = d.academicyears_id || '';
                                    var m = new bootstrap.Modal(document.getElementById('editUserModal'));
                                    m.show();
                                }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
                        });
                    });

                    // Submit edit
                    var editForm = document.getElementById('editUserForm');
                    if (editForm) {
                        editForm.addEventListener('submit', function (e) {
                            e.preventDefault();
                            var fd = new FormData(editForm);
                            fd.append('action', 'update');
                            if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                            fetch(baseUrl, { method: 'POST', body: fd }).then(function (resp) { return resp.json(); }).then(function (data) {
                                if (!data || !data.success) { showToast('danger', data && data.error ? data.error : 'Update failed'); return; }
                                showToast('success', 'User updated');
                                location.reload();
                            }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
                        });
                    }

                    // Delete
                    document.querySelectorAll('.btn-delete-user').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            var id = this.dataset.id;
                            if (!id) return;
                            document.getElementById('delete_user_id').value = id;
                            var m = new bootstrap.Modal(document.getElementById('deleteUserModal'));
                            m.show();
                        });
                    });

                    document.getElementById('confirmDeleteUser').addEventListener('click', function () {
                        var id = document.getElementById('delete_user_id').value;
                        if (!id) return;
                        var fd = new FormData();
                        fd.append('action', 'delete');
                        fd.append('id', id);
                        fd.append('csrf_token', csrfToken);
                        fetch(baseUrl, { method: 'POST', body: fd }).then(function (resp) { return resp.json(); }).then(function (data) {
                            if (!data || !data.success) { showToast('danger', data && data.error ? data.error : 'Delete failed'); return; }
                            showToast('success', 'User deleted');
                            try {
                                var rowBtn = document.querySelector('a.btn-delete-user[data-id="' + id + '"]');
                                if (!rowBtn) rowBtn = document.querySelector('a.btn-edit-user[data-id="' + id + '"]') || document.querySelector('a.btn-view-user[data-id="' + id + '"]');
                                if (rowBtn) {
                                    var tr = rowBtn.closest('tr');
                                    if (tr) {
                                        // If DataTable is active, remove via its API to keep UI consistent
                                        if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#myTable')) {
                                            var dt = $('#myTable').DataTable();
                                            dt.row(tr).remove().draw(false);
                                        } else {
                                            tr.remove();
                                        }
                                    }
                                }
                            } catch (e) { console.error(e); }
                            var modal = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
                            if (modal) modal.hide();
                        }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
                    });
                });
                </script>
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
    <?php if (!empty($user_msg)): ?>
    var _user_msg = <?php echo json_encode($user_msg); ?>;
    document.addEventListener('DOMContentLoaded', function () {
        showToast(_user_msg.type, _user_msg.text);
    });
    <?php endif; ?>
    </script>
    <!--! ================================================================ !-->
    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" class="modal-content" id="createUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label">Last name</label>
                            <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label">First name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="middlename" class="form-label">Middle name</label>
                        <input type="text" id="middlename" name="middlename" class="form-control" placeholder="Middle name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="year_level" class="form-label">Year level</label>
                            <select id="year_level" name="year_level" class="form-select" required>
                                <option value="">Select Year</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="Graduate">Graduate</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="section_id" class="form-label">Section</label>
                            <select id="section_id" name="section_id" class="form-select" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s['name']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="academicyears_id" class="form-label">Academic year</label>
                            <select id="academicyears_id" name="academicyears_id" class="form-select" required>
                                <option value="">Select AY</option>
                                <?php foreach ($academic_years as $ay): ?>
                                    <option value="<?php echo (int)$ay['id']; ?>"><?php echo htmlspecialchars($ay['sy_start'] . ' - ' . $ay['sy_end']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="usertypes_id" class="form-label">User type</label>
                            <select id="usertypes_id" name="usertypes_id" class="form-select">
                                <option value="">Select type</option>
                                <?php foreach ($usertypes as $ut): ?>
                                    <option value="<?php echo (int)$ut['id']; ?>"><?php echo htmlspecialchars($ut['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('createUserForm');
        var baseUrl = 'functions/users.php';
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
            var viewBtn = tr.querySelector('.btn-view-user');
            if (viewBtn) viewBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id;
                if (!id) return;
                fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' })
                    .then(function (resp) {
                        if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed to load user'); return; }
                        var d = resp.data || {};
                        document.getElementById('v_lastname').textContent = d.last_name || '';
                        document.getElementById('v_firstname').textContent = d.first_name || '';
                        document.getElementById('v_middlename').textContent = d.middle_name || '';
                        document.getElementById('v_email').textContent = d.email || '';
                        document.getElementById('v_year_level').textContent = d.year_level || '';
                        document.getElementById('v_section').textContent = d.section_name || (d.sections_id || '');
                        var ayText = '';
                        if (d.sy_start && d.sy_end) ayText = d.sy_start + ' - ' + d.sy_end;
                        else if (d.academicyears_id) ayText = d.academicyears_id;
                        document.getElementById('v_academicyear').textContent = ayText;
                        var m = new bootstrap.Modal(document.getElementById('viewUserModal'));
                        m.show();
                    }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
            });

            var editBtn = tr.querySelector('.btn-edit-user');
            if (editBtn) editBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id;
                if (!id) return;
                fetchJson(baseUrl + '?action=view&id=' + encodeURIComponent(id), { method: 'GET' })
                    .then(function (resp) {
                        if (!resp || !resp.success) { showToast('danger', resp && resp.error ? resp.error : 'Failed to load user'); return; }
                        var d = resp.data || {};
                        document.getElementById('e_id').value = d.id || '';
                        document.getElementById('e_lastname').value = d.last_name || '';
                        document.getElementById('e_firstname').value = d.first_name || '';
                        document.getElementById('e_middlename').value = d.middle_name || '';
                        document.getElementById('e_email').value = d.email || '';
                        document.getElementById('e_password').value = '';
                        document.getElementById('e_year_level').value = d.year_level || '';
                        var sec = document.getElementById('e_section_id'); if (sec) sec.value = d.sections_id || '';
                        var ay = document.getElementById('e_academicyears_id'); if (ay) ay.value = d.academicyears_id || '';
                        var ut = document.getElementById('e_usertypes_id'); if (ut) ut.value = d.usertypes_id || '';
                        var m = new bootstrap.Modal(document.getElementById('editUserModal'));
                        m.show();
                    }).catch(function (err) { showToast('danger', err.message || 'Request failed'); });
            });

            var delBtn = tr.querySelector('.btn-delete-user');
            if (delBtn) delBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id;
                if (!id) return;
                document.getElementById('delete_user_id').value = id;
                var m = new bootstrap.Modal(document.getElementById('deleteUserModal'));
                m.show();
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(form);
                fd.append('action', 'add');
                if (!fd.has('csrf_token')) fd.append('csrf_token', csrfToken);
                fetch(baseUrl, { method: 'POST', body: fd }).then(function (resp) { return resp.json(); }).then(function (data) {
                    if (!data || !data.success) { showToast('danger', data && data.error ? data.error : 'Add failed'); return; }
                    showToast('success', 'User added');
                    var id = data.id || '';
                    var full = data.full_name || (fd.get('firstname') + ' ' + (fd.get('middlename') || '') + ' ' + fd.get('lastname'));
                    var email = data.email || fd.get('email');
                    var role = data.role || '';
                    var is_sa = data.is_superadmin ? true : false;
                    var actionHtml = '<a href="#" class="btn-view-user text-primary me-2 fs-5" data-id="' + id + '" title="View"><i class="feather-eye"></i></a>' +
                                     '<a href="#" class="btn-edit-user text-primary me-2 fs-5" data-id="' + id + '" title="Edit"><i class="feather-edit"></i></a>';
                    if (is_sa) {
                        actionHtml += '<a href="#" class="text-muted fs-5" aria-disabled="true" title="Super admin cannot be deleted"><i class="feather-trash-2"></i></a>';
                    } else {
                        actionHtml += '<a href="#" class="btn-delete-user text-danger fs-5" data-id="' + id + '" title="Delete"><i class="feather-trash-2"></i></a>';
                    }

                    try {
                        if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable('#myTable')) {
                            var dt = $('#myTable').DataTable();
                            var newRow = dt.row.add([full, email, role, actionHtml]).draw(false).node();
                            // bind events on new row
                            bindRowButtons(newRow);
                        } else {
                            var tbody = document.querySelector('#myTable tbody');
                            if (tbody) {
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td>' + full + '</td><td>' + email + '</td><td>' + role + '</td><td>' + actionHtml + '</td>';
                                tbody.appendChild(tr);
                                bindRowButtons(tr);
                            }
                        }
                    } catch (err) {
                        console.error(err);
                    }

                    // hide modal and reset form
                    var mEl = document.getElementById('createUserModal');
                    if (mEl) {
                        var inst = bootstrap.Modal.getInstance(mEl) || new bootstrap.Modal(mEl);
                        inst.hide();
                    }
                    form.reset();
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
    <!--! END: Theme Customizer !-->
</body>

</html>