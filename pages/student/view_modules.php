<?php include "includes/init.php"; ?>

<?php
// Load modules for selected subjects_id and render as cards
$modules = [];
$subject = null;
$subjects_id = isset($_GET['subjects_id']) ? (int)$_GET['subjects_id'] : 0;
// Ensure DB connection exists; try to include dbcon if not present
if (!isset($conn) && file_exists(__DIR__ . '/../../db/dbcon.php')) {
    require_once __DIR__ . '/../../db/dbcon.php';
}
if ($subjects_id > 0 && isset($conn)) {
    try {
        if ($conn instanceof PDO) {
            $sstmt = $conn->prepare("SELECT id, name FROM tbl_subjects WHERE id = :id LIMIT 1");
            $sstmt->execute([':id' => $subjects_id]);
            $subject = $sstmt->fetch(PDO::FETCH_ASSOC) ?: null;

            $mstmt = $conn->prepare("SELECT id, file_path FROM tbl_modules WHERE subjects_id = :id ORDER BY id ASC");
            $mstmt->execute([':id' => $subjects_id]);
            $modules = $mstmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($conn instanceof mysqli) {
            $sid = $conn->real_escape_string($subjects_id);
            $sres = $conn->query("SELECT id, name FROM tbl_subjects WHERE id = {$sid} LIMIT 1");
            if ($sres) $subject = $sres->fetch_assoc();

            $mres = $conn->query("SELECT id, file_path FROM tbl_modules WHERE subjects_id = {$sid} ORDER BY id ASC");
            if ($mres) { while ($row = $mres->fetch_assoc()) $modules[] = $row; }
        } else {
            $sid = (int)$subjects_id;
            $sres = @$conn->query("SELECT id, name FROM tbl_subjects WHERE id = {$sid} LIMIT 1");
            if ($sres && is_object($sres)) $subject = $sres->fetch_assoc();
            $mres = @$conn->query("SELECT id, file_path FROM tbl_modules WHERE subjects_id = {$sid} ORDER BY id ASC");
            if ($mres && is_object($mres)) { while ($row = $mres->fetch_assoc()) $modules[] = $row; }
        }
    } catch (Exception $e) {
        // ignore DB errors to preserve layout
    }
}
// Application base (e.g. /exrev) derived from script path — go up three levels to reach project root
$appBase = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
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
                        <h5 class="m-b-10">Modules</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
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
                <div class="row g-3">
                    <?php if ($subjects_id <= 0): ?>
                        <div class="col-12"><div class="alert alert-warning">No subject selected. Please go back and choose a subject.</div></div>
                    <?php else: ?>
                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Modules for: <?php echo htmlspecialchars($subject['name'] ?? 'Unknown Subject'); ?></h5>
                        </div>
                        <?php if (!empty($modules)): foreach ($modules as $mod): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <?php $title = !empty($mod['title']) ? $mod['title'] : (basename($mod['file_path'] ?? 'Untitled Module')); ?>
                                        <h5 class="card-title mb-3"><?php echo htmlspecialchars($title); ?></h5>
                                        <p class="text-muted small mb-3">File: <?php echo htmlspecialchars(basename($mod['file_path'] ?? '')); ?></p>
                                        <div class="mt-auto d-flex gap-2">
                                            <?php
                                            $fp = isset($mod['file_path']) ? ltrim($mod['file_path'], '/\\') : '';
                                            // basic sanitization: remove any path traversal
                                            $fp = str_replace(array('..\\','../'), '', $fp);
                                            $clean = ltrim($fp, '/\\');
                                            $href = $clean ? ($appBase . '/' . $clean) : '#';
                                            ?>
                                            <button type="button" class="btn btn-outline-primary btn-view-module" data-id="<?php echo (int)$mod['id']; ?>" data-file="<?php echo htmlspecialchars($clean); ?>" data-title="<?php echo htmlspecialchars($title); ?>">Open</button>
                                            <a href="<?php echo htmlspecialchars($href); ?>" download class="btn btn-secondary">Download</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="col-12"><div class="alert alert-info">No modules found for this subject.</div></div>
                        <?php endif; ?>
                    <?php endif; ?>
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

    <!-- Inline toast script removed per request; keep toast container markup for design. -->
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

    <!-- Section-related inline JS removed per request. -->
    <!-- Module viewer modal -->
    <div class="modal fade" id="viewModuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModuleTitle">Module Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="min-height:60vh;">
                    <iframe id="viewModuleFrame" src="about:blank" style="width:100%; height:70vh; border:0;" allowfullscreen></iframe>
                </div>
                <div class="modal-footer">
                    <a id="viewModuleDownload" class="btn btn-secondary" href="#" download>Download</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
   <?php include "includes/scripts.php"; ?>
    <script>
    (function(){
        // expose server-computed app base to client
        var nxlAppBase = '<?php echo $appBase; ?>';

        document.addEventListener('click', function(e){
            var btn = e.target.closest && e.target.closest('.btn-view-module');
            if (!btn) return;
            e.preventDefault();
            var id = btn.getAttribute('data-id') || '';
            var fp = btn.getAttribute('data-file') || '';
            var title = btn.getAttribute('data-title') || 'Module Viewer';

            function openFilePath(path) {
                if (!path) { alert('No file available'); return; }
                // sanitize path and remove traversal
                path = path.replace(/(\.\.\\|\.\.\/)/g, '');
                path = path.replace(/^\/+/, '');
                // build URL including app base when present
                var prefix = (nxlAppBase && nxlAppBase !== '/') ? nxlAppBase : '';
                var url = (prefix ? prefix + '/' : '/') + encodeURI(path);
                var frame = document.getElementById('viewModuleFrame');
                var modTitle = document.getElementById('viewModuleTitle');
                var dl = document.getElementById('viewModuleDownload');
                if (frame) frame.src = url;
                if (modTitle) modTitle.textContent = title;
                if (dl) { dl.href = url; dl.setAttribute('download', ''); }
                var m = new bootstrap.Modal(document.getElementById('viewModuleModal'));
                m.show();
            }

            if (fp) {
                // fp is stored path relative to project (e.g. pages/student/learning_modules/..)
                openFilePath(fp);
                return;
            }

            // No data-file provided: fetch from server by module id
            if (!id) { alert('No file available'); return; }
            fetch((nxlAppBase ? nxlAppBase : '') + '/pages/admin/functions/modules.php?action=view&id=' + encodeURIComponent(id), { method: 'GET' }).then(function(res){
                return res.json();
            }).then(function(json){
                if (!json || !json.success || !json.data || !json.data.file_path) {
                    alert((json && json.error) ? json.error : 'File not found');
                    return;
                }
                openFilePath(json.data.file_path);
            }).catch(function(err){ alert(err.message || 'Request failed'); });
        });
        // clear iframe src on modal hide to stop playback
        var vm = document.getElementById('viewModuleModal');
        if (vm) vm.addEventListener('hidden.bs.modal', function(){ var f = document.getElementById('viewModuleFrame'); if (f) f.src = 'about:blank'; });
    })();
    </script>
    <!-- External/inline JS for sections and DataTables removed per request. -->
    <!--! END: Theme Customizer !-->
</body>

</html>