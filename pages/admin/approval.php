<?php include "includes/init.php"; ?>
<?php
// DB and functions
require_once __DIR__ . '/../../db/dbcon.php';
// (Removed: subjects backend include and server-side add_subject handler — not used by approvals)

// Handle approving users (set is_active = 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_user'])) {
    if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => 'Invalid CSRF token. Please try again.'];
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    if ($user_id > 0) {
        $up = mysqli_prepare($conn, "UPDATE tbl_users SET is_active = 1 WHERE id = ?");
        mysqli_stmt_bind_param($up, 'i', $user_id);
        if (mysqli_stmt_execute($up)) {
            $_SESSION['subject_msg'] = ['type' => 'success', 'text' => 'Student approved successfully.'];
        } else {
            $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => 'Failed to approve student.'];
        }
        mysqli_stmt_close($up);
    } else {
        $_SESSION['subject_msg'] = ['type' => 'danger', 'text' => 'Invalid user ID.'];
    }
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
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($name); ?></td>
                                                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                                            <td>
                                                                <?php if (!empty($u['auth_path'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($u['auth_path']); ?>" alt="ID" style="height:60px;object-fit:cover;border-radius:4px;">
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
                                                                    <form method="post" style="display:inline-block;">
                                                                        <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                                                        <button type="submit" name="approve_user" class="btn btn-success btn-sm">Approve</button>
                                                                    </form>
                                                                <?php else: ?>
                                                                    <button class="btn btn-secondary btn-sm" disabled>Approved</button>
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