<?php include "includes/init.php"; ?>

<?php
// DB and helper functions
require_once __DIR__ . '/../../db/dbcon.php';
require_once __DIR__ . '/functions/users.php';

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Helper: set flash message for toast
function set_profile_msg($type, $text) {
    $_SESSION['profile_msg'] = ['type' => $type, 'text' => $text];
}

// Helper: respond JSON and exit (for AJAX requests)
function respond_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Handle profile update (names + optional image)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $token = $_POST['csrf_token'] ?? '';
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if (!function_exists('verify_csrf_token') || !verify_csrf_token($token)) {
        if ($is_ajax) respond_json(['success' => false, 'message' => 'Invalid CSRF token.']);
        set_profile_msg('danger', 'Invalid CSRF token.');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }

    // Determine intent: update names if name fields were submitted; image if file provided
    $want_update_names = array_key_exists('first_name', $_POST) || array_key_exists('middle_name', $_POST) || array_key_exists('last_name', $_POST);
    $want_update_image = (!empty($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE);

    if (!$want_update_names && !$want_update_image) {
        if ($is_ajax) respond_json(['success' => true, 'message' => 'No changes submitted.']);
        set_profile_msg('info', 'No changes submitted.');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }

    $updates = [];
    $types = '';
    $values = [];

    // If names submitted, validate required fields
    if ($want_update_names) {
        $first = trim($_POST['first_name'] ?? '');
        $middle = trim($_POST['middle_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        if ($first === '' || $last === '') {
            set_profile_msg('danger', 'First and last name are required to update name.');
            header('Location: ' . $_SERVER['REQUEST_URI']); exit;
        }
        $updates[] = 'first_name = ?'; $types .= 's'; $values[] = $first;
        // include middle even if empty
        $updates[] = 'middle_name = ?'; $types .= 's'; $values[] = $middle;
        $updates[] = 'last_name = ?'; $types .= 's'; $values[] = $last;
    }

    // Handle file upload if provided
    if ($want_update_image) {
        // Fetch existing image path
        $stmt = mysqli_prepare($conn, "SELECT image_path FROM tbl_users WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        $current_image = $row['image_path'] ?? '';
        mysqli_stmt_close($stmt);

        $f = $_FILES['profile_image'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            if ($is_ajax) respond_json(['success' => false, 'message' => 'Error uploading file.']);
            set_profile_msg('danger', 'Error uploading file.');
            header('Location: ' . $_SERVER['REQUEST_URI']); exit;
        }
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            if ($is_ajax) respond_json(['success' => false, 'message' => 'Unsupported image type.']);
            set_profile_msg('danger', 'Unsupported image type.');
            header('Location: ' . $_SERVER['REQUEST_URI']); exit;
        }
        $upload_dir = __DIR__ . '/../../assets/images/auth/avatar/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        $fname = 'user_' . $uid . '_' . time() . '.' . $ext;
        $dest = $upload_dir . $fname;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            if ($is_ajax) respond_json(['success' => false, 'message' => 'Failed to save uploaded image.']);
            set_profile_msg('danger', 'Failed to save uploaded image.');
            header('Location: ' . $_SERVER['REQUEST_URI']); exit;
        }
        $new_image_path = 'assets/images/auth/avatar/' . $fname;
        $updates[] = 'image_path = ?'; $types .= 's'; $values[] = $new_image_path;
        // remove previous uploaded file if it was in same folder
        if ($current_image && strpos($current_image, 'assets/images/auth/avatar/') === 0) {
            $old = __DIR__ . '/../../' . $current_image;
            if (is_file($old)) @unlink($old);
        }
    }

    // Build and execute update if any
    if (!empty($updates)) {
        $sql = 'UPDATE tbl_users SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $types .= 'i';
        $values[] = $uid;
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            if (mysqli_stmt_execute($stmt)) {
                // prepare response data for AJAX
                if (!empty($new_image_path)) {
                    $resp = ['success' => true, 'message' => 'Profile updated successfully.', 'image_path' => $new_image_path];
                } else {
                    $resp = ['success' => true, 'message' => 'Profile updated successfully.'];
                }
                if (!empty($first) || !empty($last) || isset($middle)) {
                    $resp['first_name'] = $first ?? null;
                    $resp['middle_name'] = $middle ?? null;
                    $resp['last_name'] = $last ?? null;
                }
                if ($is_ajax) respond_json($resp);
                set_profile_msg('success', 'Profile updated successfully.');
            } else {
                if ($is_ajax) respond_json(['success' => false, 'message' => 'Failed to update profile.']);
                set_profile_msg('danger', 'Failed to update profile.');
            }
            mysqli_stmt_close($stmt);
        } else {
            if ($is_ajax) respond_json(['success' => false, 'message' => 'Database error preparing update.']);
            set_profile_msg('danger', 'Database error preparing update.');
        }
    } else {
        if ($is_ajax) respond_json(['success' => true, 'message' => 'No changes to save.']);
        set_profile_msg('info', 'No changes to save.');
    }

    if (!$is_ajax) {
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $token = $_POST['csrf_token'] ?? '';
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if (!function_exists('verify_csrf_token') || !verify_csrf_token($token)) {
        if ($is_ajax) respond_json(['success' => false, 'message' => 'Invalid CSRF token.']);
        set_profile_msg('danger', 'Invalid CSRF token.');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($new === '' || $confirm === '' || $new !== $confirm) {
        if ($is_ajax) respond_json(['success' => false, 'message' => 'New passwords do not match or are empty.']);
        set_profile_msg('danger', 'New passwords do not match or are empty.');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }

    // fetch stored password
    $stmt = mysqli_prepare($conn, "SELECT password FROM tbl_users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $stored = $row['password'] ?? '';
    mysqli_stmt_close($stmt);

    $ok = false;
    if (function_exists('password_verify') && $stored !== '' && password_verify($current, $stored)) $ok = true;
    if (!$ok && $current === $stored) $ok = true; // legacy plain-text
    if (!$ok) {
        if ($is_ajax) respond_json(['success' => false, 'message' => 'Current password is incorrect.']);
        set_profile_msg('danger', 'Current password is incorrect.');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }

    // Store password as plain text (no hashing)
    $upd = mysqli_prepare($conn, "UPDATE tbl_users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'si', $new, $uid);
    if (mysqli_stmt_execute($upd)) {
        if ($is_ajax) respond_json(['success' => true, 'message' => 'Password updated successfully.']);
        set_profile_msg('success', 'Password updated successfully.');
    } else {
        if ($is_ajax) respond_json(['success' => false, 'message' => 'Failed to update password.']);
        set_profile_msg('danger', 'Failed to update password.');
    }
    mysqli_stmt_close($upd);
    if (!$is_ajax) {
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }
}

// Pull any flash message
$profile_msg = null;
if (!empty($_SESSION['profile_msg'])) { $profile_msg = $_SESSION['profile_msg']; unset($_SESSION['profile_msg']); }

// Fetch current user data
$user = null;
$stmt = mysqli_prepare($conn, "SELECT id, first_name, middle_name, last_name, email, image_path FROM tbl_users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($res)) $user = $row;
mysqli_stmt_close($stmt);
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
    <title>EEReviewer || Profile Settings</title>
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
                        <h5 class="m-b-10">Profile Settings</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item">Profile</li>
                    </ul>
                </div>
            </div>

            <!-- [ page-header ] end -->

            <!-- [ Main Content ] start -->
            <div class="main-content">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $img = $user['image_path'] ?? '';
                                if (!$img) $img = 'assets/images/auth/avatar/default.png';
                                // convert stored relative path to absolute (site-root relative) so it loads from nested pages
                                if (!preg_match('#^https?://#i', $img) && (strlen($img) === 0 || $img[0] !== '/')) {
                                    $base = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');
                                    $img = $base . '/' . ltrim($img, '/');
                                }
                                ?>
                                <img id="profileAvatar" src="<?php echo htmlspecialchars($img); ?>" alt="avatar" class="rounded-circle mb-3" style="width:140px;height:140px;object-fit:cover;">
                                <h5 class="mb-0"><?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            </div>
                        </div>
                        <div class="card mt-3">
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Change Profile Picture</label>
                                        <input type="file" id="profile_image_input" name="profile_image" accept="image/*" class="form-control">
                                    </div>
                                    <input type="hidden" name="action" value="update_profile">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-primary">Upload & Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header"><h6 class="card-title mb-0">Edit Name</h6></div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" name="middle_name" class="form-control" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <input type="hidden" name="action" value="update_profile">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-success">Save Name</button>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header"><h6 class="card-title mb-0">Change Password</h6></div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <input type="hidden" name="action" value="change_password">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-warning">Change Password</button>
                                </form>
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
    <?php if (!empty($profile_msg)): ?>
    var _profile_msg = <?php echo json_encode($profile_msg); ?>;
    document.addEventListener('DOMContentLoaded', function () {
        showToast(_profile_msg.type, _profile_msg.text);
    });
    <?php endif; ?>
    </script>
    <script>
    // Submit profile/name/image forms via AJAX to avoid full page refresh
    (function(){
        function submitFormAJAX(form, onSuccess) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                var fd = new FormData(form);
                // ensure action flag present
                if (!fd.has('action')) fd.append('action', form.querySelector('input[name="action"]') ? form.querySelector('input[name="action"]').value : 'update_profile');
                fetch(window.location.href, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(res){ return res.json(); }).then(function(data){
                    if (!data) { showToast('danger','Unexpected response'); return; }
                    if (data.success) {
                        showToast('success', data.message || 'Saved');
                        if (onSuccess) onSuccess(data);
                    } else {
                        showToast('danger', data.message || 'Failed');
                    }
                }).catch(function(err){ showToast('danger', err.message || 'Request failed'); });
            });
        }

        // profile image form (first form on the left)
        var imgForm = document.querySelector('form[enctype="multipart/form-data"]');
        if (imgForm) {
            submitFormAJAX(imgForm, function(data){
                if (data.image_path) {
                    // convert to absolute-like path similar to server-side logic
                    var src = data.image_path;
                    if (!/^https?:\/\//i.test(src) && src.charAt(0) !== '/') {
                        var base = window.location.pathname.split('/').slice(0, -3).join('/');
                        if (base === '') base = '/';
                        src = base.replace(/\/$/, '') + '/' + src.replace(/^\//, '');
                    }
                    var avatar = document.getElementById('profileAvatar');
                    if (avatar) avatar.src = src;
                }
            });
        }

        // name edit form (second form)
        var nameForm = document.querySelector('form:not([enctype])');
        if (nameForm) {
            submitFormAJAX(nameForm, function(data){
                // Update displayed name on the left
                if (data.first_name || data.last_name || data.middle_name !== undefined) {
                    var h = document.querySelector('#profileAvatar').parentNode.querySelector('h5');
                    if (h) {
                        var f = data.first_name || nameForm.querySelector('input[name="first_name"]').value || '';
                        var m = data.middle_name || nameForm.querySelector('input[name="middle_name"]').value || '';
                        var l = data.last_name || nameForm.querySelector('input[name="last_name"]').value || '';
                        h.textContent = (f + ' ' + m + ' ' + l).replace(/\s+/g,' ').trim();
                    }
                }
            });
        }

        // password change form (third form)
        var pwForm = Array.prototype.slice.call(document.querySelectorAll('form')).filter(function(f){ return f.querySelector('input[name="action"]') && f.querySelector('input[name="action"]').value === 'change_password'; })[0];
        if (pwForm) submitFormAJAX(pwForm);
    })();
    </script>
    <!-- end toast setup -->

    <!-- footer toast initialization -->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
   <?php include "includes/scripts.php"; ?>
    <script>
    // Auto-dismiss alerts with class .auto-dismiss after 3 seconds
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.querySelector('.auto-dismiss');
        if (el) { setTimeout(function () { el.remove(); }, 3000); }
    });
    </script>
    <script>
    // Preview selected profile image before upload
    (function(){
        var input = document.getElementById('profile_image_input');
        var img = document.getElementById('profileAvatar');
        if (!input || !img) return;
        input.addEventListener('change', function(e){
            var f = this.files && this.files[0];
            if (!f) return;
            if (!f.type.match('image.*')) { showToast('danger','Selected file is not an image'); this.value = ''; return; }
            var reader = new FileReader();
            reader.onload = function(evt){ img.src = evt.target.result; };
            reader.readAsDataURL(f);
        });
    })();
    </script>
    <!--! END: Theme Customizer !-->
</body>

</html>