<?php include "includes/init.php"; ?>
<?php
// Minimal Terms page with PRG-to-toast support
$term_msg = null;
if (!empty($_SESSION['term_msg'])) {
    $term_msg = $_SESSION['term_msg'];
    unset($_SESSION['term_msg']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EEReviewer || Terms</title>
    <?php include "includes/css_scripts_head.php"; ?>
</head>
<body>
<?php include "includes/sidebar.php"; ?>
<?php include "includes/header.php"; ?>
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Terms</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Terms</li>
                </ul>
            </div>
        </div>
        <div class="main-content">
            <div class="card">
                <div class="card-body p-3">
                    <p>Manage academic terms here.</p>
                </div>
            </div>
        </div>
    </div>
    <?php include "includes/footer.php"; ?>
</main>
<?php include "includes/customizer2.php"; ?>

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
<?php if (!empty($term_msg)): ?>
var _term_msg = <?php echo json_encode($term_msg); ?>;
document.addEventListener('DOMContentLoaded', function () {
    showToast(_term_msg.type, _term_msg.text);
});
<?php endif; ?>
</script>

<?php include "includes/scripts.php"; ?>
</body>
</html>
