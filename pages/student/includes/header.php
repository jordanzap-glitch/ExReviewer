<?php // header markup only - session and auth handled in includes/init.php ?>
<?php
// Load current user info for header (if logged in)
$header_user_name = 'User';
$header_user_email = '';
$header_user_image = 'assets/images/avatar/1.png';
if (!empty($_SESSION['user_id'])) {
    if (!isset($conn) || !$conn) {
        @include_once __DIR__ . '/../../../db/dbcon.php';
    }
    $uid = (int)($_SESSION['user_id'] ?? 0);
    if ($uid > 0 && isset($conn) && $conn) {
        $stmt = mysqli_prepare($conn, "SELECT first_name, last_name, email, image_path FROM tbl_users WHERE id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $uid);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($r = mysqli_fetch_assoc($res)) {
                $first = trim($r['first_name'] ?? '');
                $last = trim($r['last_name'] ?? '');
                $header_user_name = trim($first . ' ' . $last) ?: $header_user_name;
                $header_user_email = $r['email'] ?? '';
                if (!empty($r['image_path'])) {
                    $raw = $r['image_path'];
                    // normalize to absolute URL path relative to site root when a relative path is stored
                    if (!preg_match('#^https?://#i', $raw) && (strlen($raw) === 0 || $raw[0] !== '/')) {
                        $base = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');
                        $header_user_image = $base . '/' . ltrim($raw, '/');
                    } else {
                        $header_user_image = $raw;
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [Start] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                        <i class="feather-align-left"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
                <!--! [Start] nxl-lavel-mega-menu-toggle !-->
                <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                    <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                        <i class="feather-align-left"></i>
                    </a>
                </div>
                <!--! [End] nxl-lavel-mega-menu-toggle !-->
                <!--! [Start] nxl-lavel-mega-menu !-->
                <div class="nxl-drp-link nxl-lavel-mega-menu">
                    <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                        <a href="javascript:void(0)" id="nxl-lavel-mega-menu-hide">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    <!--! [Start] nxl-lavel-mega-menu-wrapper !-->
                    <!--! [End] nxl-lavel-mega-menu-wrapper !-->
                </div>
                <!--! [End] nxl-lavel-mega-menu !-->
            </div>
            <!--! [End] Header Left !-->
            <!--! [Start] Header Right !-->
            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">
                    
                    <div class="nxl-h-item d-none d-sm-flex">
                        <div class="full-screen-switcher">
                            <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                                <i class="feather-maximize maximize"></i>
                                <i class="feather-minimize minimize"></i>
                            </a>
                        </div>
                    </div>
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    
         
                    <div class="dropdown nxl-h-item no-hover">
                            <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                            <img src="<?php echo htmlspecialchars($header_user_image); ?>" alt="user-image" class="img-fluid user-avtar me-0" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($header_user_image); ?>" alt="user-image" class="img-fluid user-avtar" />
                                    <div>
                                        <h6 class="text-dark mb-0"><?php echo htmlspecialchars($header_user_name); ?> <span class="badge bg-soft-success text-success ms-1"></span></h6>
                                        <span class="fs-12 fw-medium text-muted"><?php echo htmlspecialchars($header_user_email); ?></span>
                                    </div>
                                </div>
                            </div>
                           
                            <div class="dropdown">
                               
                            </div>
                            <a href="profile.php" class="dropdown-item">
                                <i class="feather-settings"></i>
                                <span>Account Settings</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../../logout.php" class="dropdown-item">
                                <i class="feather-log-out"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--! [End] Header Right !-->
        </div>
    </header>

    <!-- Logout confirmation modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure? Do you really want to logout?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmLogoutBtn" class="btn btn-danger">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Prevent hover-based opening for the profile dropdown; allow click only.
    (function(){
        try {
            var dd = document.querySelector('.nxl-header .header-wrapper .dropdown.no-hover');
            if (!dd) return;
            // stop hover events before any other handlers (capture phase)
            ['mouseover','mouseenter','mouseleave','mouseout'].forEach(function(evt){
                dd.addEventListener(evt, function(e){
                    e.stopImmediatePropagation();
                }, true);
            });
        } catch (e) { console && console.warn && console.warn(e); }
    })();

    // Intercept logout links and show confirmation modal
    document.addEventListener('DOMContentLoaded', function () {
        var logoutLinks = document.querySelectorAll('a[href$="logout.php"]');
        var modalEl = document.getElementById('logoutConfirmModal');
        var confirmBtn = document.getElementById('confirmLogoutBtn');
        var pendingHref = null;
        if (!modalEl) return;
        logoutLinks.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                pendingHref = this.getAttribute('href');
                var bs = bootstrap.Modal.getOrCreateInstance(modalEl);
                bs.show();
            });
        });
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (pendingHref) window.location.href = pendingHref;
            });
        }
    });
    </script>