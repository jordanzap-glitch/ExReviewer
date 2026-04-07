<?php ob_start(); ?>
<?php
session_start();

require_once __DIR__ . '/db/dbcon.php';

// Image upload functionality removed — uploads are no longer accepted here.

// Simple helper: insert user row, returns true on success
function insert_user(mysqli $conn, string $lastname, string $firstname, string $middlename, string $email, string $password, ?string $auth_path, string $year_level, int $section_id, int $academicyears_id): bool
{
    $insert = mysqli_prepare($conn, "INSERT INTO tbl_users (last_name, first_name, middle_name, email, password, auth_path, year_level, sections_id, academicyears_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$insert) return false;
    // mysqli_stmt_bind_param does not accept a true NULL for params easily; store empty string when no path provided
    $auth_param = $auth_path ?? '';
    mysqli_stmt_bind_param($insert, 'sssssssii', $lastname, $firstname, $middlename, $email, $password, $auth_param, $year_level, $section_id, $academicyears_id);
    $ok = mysqli_stmt_execute($insert);
    mysqli_stmt_close($insert);
    return (bool)$ok;
}

$error = '';
$success = '';
$show_modal = false;

// Fetch sections
$sections = [];
$res = mysqli_query($conn, "SELECT id, name FROM tbl_sections ORDER BY name");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $sections[] = $row;
    }
}

// Fetch active academic years (is_active = 1)
$academic_years = [];
$res2 = mysqli_query($conn, "SELECT id, sy_start, sy_end FROM tbl_academicyears WHERE is_active = 1");
if ($res2) {
    while ($row = mysqli_fetch_assoc($res2)) {
        $academic_years[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname = trim($_POST['lastname'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $year_level = $_POST['year_level'] ?? '';
    $section_id = (int)($_POST['section_id'] ?? 0);
    $academicyears_id = (int)($_POST['academicyears_id'] ?? 0);

    if ($lastname === '' || $firstname === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'An account with that email already exists.';
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);

            // Handle optional identification image upload: validate, save to pages/admin/, set $auth_path
            $auth_path = null;
            if (!empty($_FILES['auth_image']['name'])) {
                $file = $_FILES['auth_image'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $tmp = $file['tmp_name'];
                    $imginfo = @getimagesize($tmp);
                    if ($imginfo === false) {
                        $error = 'Uploaded file is not a valid image.';
                    } else {
                        $mime = $imginfo['mime'];
                        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!in_array($mime, $allowed, true)) {
                            $error = 'Invalid image type. Only JPG, PNG and GIF are allowed.';
                        } elseif ($file['size'] > 2 * 1024 * 1024) {
                            $error = 'Image too large. Maximum allowed size is 2MB.';
                        } else {
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $ext = preg_replace('/[^a-z0-9]/', '', $ext);
                            if ($ext === '') $ext = ($mime === 'image/png' ? 'png' : ($mime === 'image/gif' ? 'gif' : 'jpg'));
                            try {
                                $newname = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                            } catch (Exception $e) {
                                $newname = uniqid('img_', true) . '.' . $ext;
                            }
                            $upload_dir = __DIR__ . '/pages/admin/auth_image/';
                            if (!is_dir($upload_dir)) {
                                @mkdir($upload_dir, 0755, true);
                            }
                            $dest = $upload_dir . $newname;
                            if (move_uploaded_file($tmp, $dest)) {
                                // store relative path to be saved in DB
                                $auth_path = 'pages/admin/auth_image/' . $newname;
                            } else {
                                $error = 'Failed to save uploaded image.';
                            }
                        }
                    }
                } else {
                    if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                        $error = 'File upload error (code: ' . (int)$file['error'] . ').';
                    }
                }
            }

            if (empty($error)) {
                // Store password as provided (no hashing)
                $password_to_store = $password;
                if (insert_user($conn, $lastname, $firstname, $middlename, $email, $password_to_store, $auth_path, $year_level, $section_id, $academicyears_id)) {
                    $success = 'Account created successfully.';
                    $show_modal = true; // show modal then redirect on client
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
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
    <title>PSU || Register</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/psu.png" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/vendors.min.css">
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/theme.min.css">
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-minimal-wrapper">
        <div class="auth-minimal-inner">
            <div class="minimal-card-wrapper">
                <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative mx-auto" style="max-width:900px;">
                    <div class="bg-white shadow-lg position-absolute translate-middle top-0 start-50" style="width:112px;height:112px;padding:6px;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        <img src="assets/images/psu.png" alt="" class="img-fluid" style="width:100%;height:100%;object-fit:cover;display:block;">
                    </div>
                    <div class="card-body p-sm-5">
                        <h2 class="fs-20 fw-bolder mb-4">Register</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <form action="" method="post" enctype="multipart/form-data" class="w-100 mt-4 pt-2">
                            <div class="row g-2">
                                <div class="col-md-4 mb-4">
                                    <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <input type="text" name="middlename" class="form-control" placeholder="Middle Name">
                                </div>
                            </div>
                            <div class="mb-4">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-4 mb-4">
                                    <select name="year_level" class="form-select" required>
                                        <option value="">Select Year Level</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="Graduate">Graduate</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <select name="section_id" class="form-select" required>
                                        <option value="">Select Section</option>
                                        <?php foreach ($sections as $s): ?>
                                            <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <select name="academicyears_id" class="form-select" required>
                                        <option value="">Select Academic Year</option>
                                        <?php foreach ($academic_years as $ay): ?>
                                            <option value="<?php echo (int)$ay['id']; ?>"><?php echo htmlspecialchars($ay['sy_start'] . ' - ' . $ay['sy_end']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 text-center">
                                <label for="authImage" class="form-label d-block mb-2">Identification (optional)</label>
                                <div style="max-width:420px;margin:0 auto 0.75rem;">
                                    <div style="width:100%;height:180px;border:1px solid #ddd;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">
                                        <img id="authPreview" src="assets/images/default.png" alt="Preview" style="width:100%;height:100%;object-fit:cover;display:block">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <input type="file" name="auth_image" id="authImage" accept="image/*" class="form-control w-50">
                                </div>
                                <div class="form-text">Allowed: JPG, PNG, GIF. Max 2MB.</div>
                            </div>

                            <div class="mb-4 generate-pass">
                                <div class="input-group field">
                                    <input type="password" name="password" class="form-control password" id="newPassword" placeholder="Password" required>
                                    <div class="input-group-text c-pointer gen-pass" data-bs-toggle="tooltip" title="Generate Password"><i class="feather-hash"></i></div>
                                    <div class="input-group-text border-start bg-gray-2 c-pointer show-pass" data-bs-toggle="tooltip" title="Show/Hide Password"><i></i></div>
                                </div>
                                <div class="progress-bar mt-2">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder="Password again" required aria-describedby="pw-match-feedback">
                                <div id="pw-match-feedback" class="form-text mt-1"></div>
                            </div>
                            <div class="mt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="termsCondition" required>
                                    <label class="custom-control-label c-pointer text-muted" for="termsCondition" style="font-weight: 400 !important">I agree to all the <a href="">Terms &amp; Conditions</a> and <a href="">Fees</a>.</label>
                                </div>
                            </div>
                            <div class="mt-5">
                                <button type="submit" id="registerBtn" class="btn btn-lg btn-primary w-100">Create Account</button>
                            </div>
                        </form>
                        <div class="mt-5 text-muted">
                            <span>Already have an account?</span>
                            <a href="index.php" class="fw-bold">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->
    <div class="theme-customizer">
        <div class="customizer-handle">
            <a href="javascript:void(0);" class="cutomizer-open-trigger bg-primary">
                <i class="feather-settings"></i>
            </a>
        </div>
        <div class="customizer-sidebar-wrapper">
            <div class="customizer-sidebar-header px-4 ht-80 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Theme Settings</h5>
                <a href="javascript:void(0);" class="cutomizer-close-trigger d-flex">
                    <i class="feather-x"></i>
                </a>
            </div>
            <div class="customizer-sidebar-body position-relative p-4" data-scrollbar-target="#psScrollbarInit">
                <!--! BEGIN: [Skins] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Skins</label>
                    <div class="row g-2 theme-options-items app-skin" id="appSkinList">
                        <div class="col-6 text-center position-relative single-option light-button active">
                            <input type="radio" class="btn-check" id="app-skin-light" name="app-skin" value="1" data-app-skin="app-skin-light">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-skin-light">Light</label>
                        </div>
                        <div class="col-6 text-center position-relative single-option dark-button">
                            <input type="radio" class="btn-check" id="app-skin-dark" name="app-skin" value="2" data-app-skin="app-skin-dark">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-skin-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Skins] !-->
                <!--! BEGIN: [Typography] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-0 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Typography</label>
                    <div class="row g-2 theme-options-items font-family" id="fontFamilyList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-lato" name="font-family" value="1" data-font-family="app-font-family-lato">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-lato">Lato</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-rubik" name="font-family" value="2" data-font-family="app-font-family-rubik">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-rubik">Rubik</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-inter" name="font-family" value="3" data-font-family="app-font-family-inter" checked>
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-inter">Inter</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-cinzel" name="font-family" value="4" data-font-family="app-font-family-cinzel">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-cinzel">Cinzel</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-nunito" name="font-family" value="6" data-font-family="app-font-family-nunito">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-nunito">Nunito</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-roboto" name="font-family" value="7" data-font-family="app-font-family-roboto">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-roboto">Roboto</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-ubuntu" name="font-family" value="8" data-font-family="app-font-family-ubuntu">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-ubuntu">Ubuntu</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-poppins" name="font-family" value="9" data-font-family="app-font-family-poppins">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-poppins">Poppins</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-raleway" name="font-family" value="10" data-font-family="app-font-family-raleway">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-raleway">Raleway</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-system-ui" name="font-family" value="11" data-font-family="app-font-family-system-ui">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-system-ui">System UI</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-noto-sans" name="font-family" value="12" data-font-family="app-font-family-noto-sans">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-noto-sans">Noto Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-fira-sans" name="font-family" value="13" data-font-family="app-font-family-fira-sans">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-fira-sans">Fira Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-work-sans" name="font-family" value="14" data-font-family="app-font-family-work-sans">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-work-sans">Work Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-open-sans" name="font-family" value="15" data-font-family="app-font-family-open-sans">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-open-sans">Open Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-maven-pro" name="font-family" value="16" data-font-family="app-font-family-maven-pro">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-maven-pro">Maven Pro</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-quicksand" name="font-family" value="17" data-font-family="app-font-family-quicksand">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-quicksand">Quicksand</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-montserrat" name="font-family" value="18" data-font-family="app-font-family-montserrat">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-montserrat">Montserrat</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-josefin-sans" name="font-family" value="19" data-font-family="app-font-family-josefin-sans">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-josefin-sans">Josefin Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-ibm-plex-sans" name="font-family" value="20" data-font-family="app-font-family-ibm-plex-sans">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-ibm-plex-sans">IBM Plex Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-source-sans-pro" name="font-family" value="5" data-font-family="app-font-family-source-sans-pro">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-source-sans-pro">Source Sans Pro</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-montserrat-alt" name="font-family" value="21" data-font-family="app-font-family-montserrat-alt">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-montserrat-alt">Montserrat Alt</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-roboto-slab" name="font-family" value="22" data-font-family="app-font-family-roboto-slab">
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-roboto-slab">Roboto Slab</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Typography] !-->
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <script src="assets/vendors/js/vendors.min.js"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <script src="assets/vendors/js/lslstrength.min.js"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="assets/js/common-init.min.js"></script>
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    <script src="assets/js/theme-customizer-init.min.js"></script>
    <!--! END: Theme Customizer !-->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var pwd = document.getElementById('newPassword');
        var cpwd = document.getElementById('confirmPassword');
        var feedback = document.getElementById('pw-match-feedback');
        var submitBtn = document.getElementById('registerBtn');
        if (!pwd || !cpwd || !feedback || !submitBtn) return;

        function validate() {
            var a = pwd.value || '';
            var b = cpwd.value || '';
            if (b.length === 0) {
                feedback.textContent = '';
                pwd.classList.remove('is-invalid','is-valid');
                cpwd.classList.remove('is-invalid','is-valid');
                submitBtn.disabled = false;
                return;
            }
            if (a === b) {
                feedback.textContent = 'Passwords match.';
                feedback.style.color = '#198754';
                pwd.classList.remove('is-invalid'); pwd.classList.add('is-valid');
                cpwd.classList.remove('is-invalid'); cpwd.classList.add('is-valid');
                submitBtn.disabled = false;
            } else {
                feedback.textContent = 'Passwords do not match.';
                feedback.style.color = '#dc3545';
                pwd.classList.remove('is-valid'); pwd.classList.add('is-invalid');
                cpwd.classList.remove('is-valid'); cpwd.classList.add('is-invalid');
                submitBtn.disabled = true;
            }
        }

        pwd.addEventListener('input', validate);
        cpwd.addEventListener('input', validate);

        // Image preview and client-side checks for identification upload
        var authInput = document.getElementById('authImage');
        var authPreview = document.getElementById('authPreview');
        if (authInput && authPreview) {
            authInput.addEventListener('change', function () {
                var f = this.files && this.files[0];
                if (!f) {
                    authPreview.src = 'assets/images/psu.png';
                    return;
                }
                if (!f.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    this.value = '';
                    return;
                }
                if (f.size > 2 * 1024 * 1024) {
                    alert('Image too large. Max 2MB.');
                    this.value = '';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (evt) { authPreview.src = evt.target.result; };
                reader.readAsDataURL(f);
            });
        }
    });
    </script>
    <?php if (!empty($show_modal)): ?>
    <!-- Styled Thank-you modal -->
    <style>
    #thankYouModal .modal-content { border: 0; border-radius: .8rem; overflow: hidden; }
    #thankYouModal .check-icon { width: 84px; height: 84px; display: inline-block; }
    #thankYouModal .modal-body { padding: 2rem 1.75rem; }
    #thankYouModal .modal-body h4 { font-size: 1.45rem; }
    #thankYouModal .modal-body p { margin-bottom: 1.25rem; }
    </style>
    <div class="modal fade" id="thankYouModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <svg class="check-icon text-success" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" stroke="#28a745" stroke-width="1.5" fill="#e9f7ef" />
                            <path d="M7.5 12.5l2.5 2.5L16.5 9" stroke="#28a745" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h4 class="fw-bold">Thank you &amp; Good luck!</h4>
                    <p class="text-muted">Your account has been created successfully. You may now log in.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-success btn-lg px-4" id="modalOkBtn" data-bs-dismiss="modal">Continue to Login</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            var modalEl = document.getElementById('thankYouModal');
            if (modalEl) {
                var bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                bsModal.show();
                var redirect = function() { window.location = 'index.php'; };
                var okBtn = document.getElementById('modalOkBtn');
                if (okBtn) okBtn.addEventListener('click', redirect);
                modalEl.addEventListener('hidden.bs.modal', redirect);
                // auto-redirect after 5 seconds as a fallback
                setTimeout(function() { if (document.getElementById('thankYouModal')) redirect(); }, 5000);
            }
        } catch (e) {
            window.location = 'index.php';
        }
    });
    </script>
    <?php endif; ?>
    <?php ob_end_flush(); ?>
</body>

</html>