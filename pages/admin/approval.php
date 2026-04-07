<?php include "includes/init.php"; ?>
<?php
// DB and functions
require_once __DIR__ . '/../../db/dbcon.php';
// (Removed: subjects backend include and server-side add_subject handler — not used by approvals)

// Handle approving or denying users (set is_active = 1 or 0)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['approval_action'])) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $msg = 'Invalid CSRF token. Please try again.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
        $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => $msg];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $action = $_POST['approval_action'];
    if ($user_id > 0) {
        $new_status = ($action === 'approve') ? 1 : 0;
        $up = mysqli_prepare($conn, "UPDATE tbl_users SET is_active = ? WHERE id = ?");
        mysqli_stmt_bind_param($up, 'ii', $new_status, $user_id);
        if (mysqli_stmt_execute($up)) {
            $msg = ($new_status === 1) ? 'Student approved successfully.' : 'Student denied successfully.';
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg, 'new_status' => $new_status]);
                exit;
            }
            $_SESSION['subject_msg'] = ['type' => 'success', 'text' => $msg];
        } else {
            $msg = 'Failed to update student status.';
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => $msg];
        }
        mysqli_stmt_close($up);
    } else {
        $msg = 'Invalid user ID.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
        $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => $msg];
    }
    if (!$is_ajax) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
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
                        <h5 class="m-b-10">User Approvals</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Approvals</li>
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
                                    <h5 class="card-title mb-0">User Approvals</h5>
                                </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="myTable" class="table table-hover mb-0">
                                        <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Identification</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php
                                                // List users pending approval (is_active = 0)
                                                // show both pending and active users
                                                $uq = "SELECT id, first_name, last_name, email, auth_path, is_active FROM tbl_users ORDER BY is_active DESC, id DESC";
                                                $ures = mysqli_query($conn, $uq);
                                                if ($ures && mysqli_num_rows($ures) > 0) {
                                                    while ($u = mysqli_fetch_assoc($ures)) {
                                                        $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                                                        $img_src = '';
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($name); ?></td>
                                                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                                            <td>
                                                                <?php if (!empty($u['auth_path'])): ?>
                                                                    <?php
                                                                    // Normalize stored path and make it relative to this admin page.
                                                                    $stored = $u['auth_path'];
                                                                    $img_src = '';
                                                                    // If stored path starts with 'pages/admin/', remove that prefix so the path is relative to this folder
                                                                    if (strpos($stored, 'pages/admin/') === 0) {
                                                                        $img_src = substr($stored, strlen('pages/admin/'));
                                                                    } elseif (strpos($stored, '/pages/admin/') === 0) {
                                                                        $img_src = substr($stored, strlen('/pages/admin/'));
                                                                    } else {
                                                                        // fallback: use stored path as-is
                                                                        $img_src = $stored;
                                                                    }
                                                                    // ensure proper escaping when output
                                                                    ?>
                                                                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="ID" style="height:60px;object-fit:cover;border-radius:4px;">
                                                                <?php else: ?>
                                                                    <span class="text-muted">No ID</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $is_active = isset($u['is_active']) ? (int)$u['is_active'] : 0;
                                                                if ($is_active === 1) {
                                                                    echo '<span class="badge bg-success">Active</span>';
                                                                } else {
                                                                    echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($is_active === 0): ?>
                                                                    <button type="button" class="btn btn-success btn-sm approve-btn"
                                                                        data-user-id="<?php echo (int)$u['id']; ?>"
                                                                        data-img-src="<?php echo htmlspecialchars($img_src); ?>"
                                                                    >Approve</button>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-danger btn-sm deny-btn"
                                                                        data-user-id="<?php echo (int)$u['id']; ?>"
                                                                        data-img-src="<?php echo htmlspecialchars($img_src); ?>"
                                                                    >Deny</button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo '<tr><td colspan="5">No pending approvals.</td></tr>';
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
    <!-- View/Edit/Delete subject modals removed (not used on approvals page) -->

    <!-- Subject-related JavaScript removed -->
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
    <!-- Subject-related JS (create/bind) removed -->
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
    <!-- Approval modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" class="modal-content" id="approveForm">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="approveImageWrapper" style="max-height:360px;overflow:hidden;">
                        <img id="approveImagePreview" src="" alt="ID Preview" style="width:100%;height:auto;object-fit:cover;border-radius:6px;">
                    </div>
                    <p class="mt-3 text-muted" id="noImageText" style="display:none;">No identification image provided.</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="user_id" id="approveUserId" value="">
                    <input type="hidden" name="approval_action" id="approvalAction" value="approve">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="approveConfirmBtn" class="btn btn-success">Approve User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var approveBtns = document.querySelectorAll('.approve-btn');
            var denyBtns = document.querySelectorAll('.deny-btn');
            var approveModalEl = document.getElementById('approveModal');
            var approveImage = document.getElementById('approveImagePreview');
            var noImageText = document.getElementById('noImageText');
            var approveUserId = document.getElementById('approveUserId');
            var approvalActionInput = document.getElementById('approvalAction');
            var approveConfirmBtn = document.getElementById('approveConfirmBtn');
            var bsApproveModal = null;
            if (approveModalEl) bsApproveModal = new bootstrap.Modal(approveModalEl);

            function openApproveModal(uid, img, action) {
                approveUserId.value = uid;
                if (img) {
                    approveImage.src = img;
                    approveImage.style.display = '';
                    noImageText.style.display = 'none';
                } else {
                    approveImage.src = '';
                    approveImage.style.display = 'none';
                    noImageText.style.display = '';
                }
                if (approvalActionInput) approvalActionInput.value = action;
                if (approveConfirmBtn) {
                    if (action === 'approve') {
                        approveConfirmBtn.textContent = 'Approve User';
                        approveConfirmBtn.classList.remove('btn-danger');
                        approveConfirmBtn.classList.add('btn-success');
                    } else {
                        approveConfirmBtn.textContent = 'Deny User';
                        approveConfirmBtn.classList.remove('btn-success');
                        approveConfirmBtn.classList.add('btn-danger');
                    }
                }
                if (bsApproveModal) bsApproveModal.show();
            }

                // bind click handlers
                function bindActionButtons() {
                    document.querySelectorAll('.approve-btn').forEach(function (btn) {
                        btn.onclick = function () {
                            var uid = this.getAttribute('data-user-id') || '';
                            var img = this.getAttribute('data-img-src') || '';
                            openApproveModal(uid, img, 'approve');
                        };
                    });
                    document.querySelectorAll('.deny-btn').forEach(function (btn) {
                        btn.onclick = function () {
                            var uid = this.getAttribute('data-user-id') || '';
                            var img = this.getAttribute('data-img-src') || '';
                            openApproveModal(uid, img, 'deny');
                        };
                    });
                }

                bindActionButtons();

                // AJAX submit for approve/deny to avoid page reload
                var approveForm = document.getElementById('approveForm');
                if (approveForm) {
                    approveForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        var fd = new FormData(approveForm);
                        fetch(window.location.href, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function (res) { return res.json(); }).then(function (data) {
                            if (!data) return;
                            if (data.success) {
                                var uid = fd.get('user_id');
                                var newStatus = data.new_status;
                                // find the row for this user
                                var triggerBtn = document.querySelector('[data-user-id="' + uid + '"]');
                                if (triggerBtn) {
                                    var row = triggerBtn.closest('tr');
                                    if (row) {
                                        // update status cell (4th column)
                                        var statusCell = row.querySelectorAll('td')[3];
                                        if (statusCell) {
                                            if (parseInt(newStatus, 10) === 1) statusCell.innerHTML = '<span class="badge bg-success">Active</span>';
                                            else statusCell.innerHTML = '<span class="badge bg-warning text-dark">Pending</span>';
                                        }
                                        // update action cell (5th column)
                                        var actionCell = row.querySelectorAll('td')[4];
                                        var imgEl = row.querySelector('td:nth-child(3) img');
                                        var imgSrc = imgEl ? imgEl.getAttribute('src') : '';
                                        if (actionCell) {
                                            if (parseInt(newStatus, 10) === 1) {
                                                actionCell.innerHTML = '<button type="button" class="btn btn-danger btn-sm deny-btn" data-user-id="' + uid + '" data-img-src="' + (imgSrc || '') + '">Deny</button>';
                                            } else {
                                                actionCell.innerHTML = '<button type="button" class="btn btn-success btn-sm approve-btn" data-user-id="' + uid + '" data-img-src="' + (imgSrc || '') + '">Approve</button>';
                                            }
                                        }
                                        bindActionButtons();
                                    }
                                }
                                if (typeof showToast === 'function') showToast('success', data.message || 'Updated');
                                if (bsApproveModal) bsApproveModal.hide();
                            } else {
                                if (typeof showToast === 'function') showToast('danger', data.message || 'Failed');
                            }
                        }).catch(function (err) {
                            if (typeof showToast === 'function') showToast('danger', 'Request failed');
                        });
                    });
                }
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