<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>Duralux || Dashboard</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <?php include "includes/css_scripts_head.php"; ?>
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>e
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
                        <h5 class="m-b-10">Student Dashboard</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Dashboard</li>
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
                            <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                                <span class="reportrange-picker-field"></span>
                            </div>
                            <div class="dropdown filter-dropdown">
                                <a class="btn btn-md btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                                    <i class="feather-filter me-2"></i>
                                    <span>Filter</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Role" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Role">Role</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Team" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Team">Team</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Email" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Email">Email</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Member" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Member">Member</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="Recommendation" checked="checked" />
                                            <label class="custom-control-label c-pointer" for="Recommendation">Recommendation</label>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-plus me-3"></i>
                                        <span>Create New</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-filter me-3"></i>
                                        <span>Manage Filter</span>
                                    </a>
                                </div>
                            </div>
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
                    <!-- [Total Students] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-primary border-soft-primary text-primary overflow-hidden">
                            <div class="card-body">
                                <i class="feather-users fs-20"></i>
                                <h5 class="fs-4 text-reset mt-4 mb-1">8,475</h5>
                                <div class="fs-12 text-reset fw-normal">Total Students</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Students] end -->
                    <!-- [Total Exam] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-success border-soft-success text-success overflow-hidden">
                            <div class="card-body">
                                <i class="feather-file-text fs-20"></i>
                                <h5 class="fs-4 text-reset mt-4 mb-1">1,200</h5>
                                <div class="fs-12 text-reset fw-normal">Total Exam</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Exam] end -->
                    <!-- [Total Lessons] start -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card bg-soft-warning border-soft-warning text-warning overflow-hidden">
                            <div class="card-body">
                                <i class="feather-book fs-20"></i>
                                <h5 class="fs-4 text-reset mt-4 mb-1">3,200</h5>
                                <div class="fs-12 text-reset fw-normal">Total Lessons</div>
                            </div>
                        </div>
                    </div>
                    <!-- [Total Lessons] end -->
                    <!-- Projects/Conversion cards removed per request -->
                    <!-- [Payment Records] start -->
                    <div class="col-xxl-12">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Average Score (Overall)</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Delete">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                            <div data-bs-toggle="tooltip" title="Options">
                                                <i class="feather-more-vertical"></i>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action p-0">
                                <div id="average-score-area-chart" style="min-height:280px;"></div>
                                <div class="p-3">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="card bg-soft-primary border-soft-primary text-primary overflow-hidden">
                                                <div class="card-body p-2 d-flex align-items-center gap-3">
                                                    <div class="avatar-text bg-soft-primary text-primary">
                                                        <i class="feather-users"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fs-12 text-muted">Average</div>
                                                        <div class="fs-5 fw-bold">78%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="card bg-soft-success border-soft-success text-success overflow-hidden">
                                                <div class="card-body p-2 d-flex align-items-center gap-3">
                                                    <div class="avatar-text bg-soft-success text-success">
                                                        <i class="feather-award"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fs-12 text-muted">Highest</div>
                                                        <div class="fs-5 fw-bold">95%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="card bg-soft-danger border-soft-danger text-danger overflow-hidden">
                                                <div class="card-body p-2 d-flex align-items-center gap-3">
                                                    <div class="avatar-text bg-soft-danger text-danger">
                                                        <i class="feather-trending-down"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fs-12 text-muted">Lowest</div>
                                                        <div class="fs-5 fw-bold">65%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [Payment Records] end -->
                    <!-- [Leads Status / Student Ranking] start -->
                    <div class="col-xxl-12">
                        <div class="card s  tretch stretch-full">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title text-white mb-0">Student Rankings</h5>
                                    <small class="text-white-50">Leads Status (Top performers)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-primary text-dark">Top 5</span>
                                    <div class="fw-bold text-white mt-1">8,475</div>
                                    <div class="fs-12 text-white-50">Total Students</div>
                                </div>
                            </div>
                            <div class="card-body custom-card-action p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Name</th>
                                                <th scope="col" class="wd-100">Avatar</th>
                                                <th scope="col">Last Exam</th>
                                                <th scope="col">Rank</th>
                                                <th scope="col">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="position-relative">
                                                    <div class="ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 border-success rounded"></div>
                                                    <a href="javascript:void(0);">A. Thompson</a>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="avatar-image avatar-md">
                                                        <img src="assets/images/avatar/1.png" alt="" class="img-fluid">
                                                    </a>
                                                </td>
                                                <td>15 June, 2023</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="badge bg-soft-success text-success">1</a>
                                                </td>
                                                <td><a href="javascript:void(0);">98%</a></td>
                                            </tr>
                                            <tr>
                                                <td class="position-relative">
                                                    <div class="ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 border-warning rounded"></div>
                                                    <a href="javascript:void(0);">H. Cherry</a>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="avatar-image avatar-md">
                                                        <img src="assets/images/avatar/2.png" alt="" class="img-fluid">
                                                    </a>
                                                </td>
                                                <td>20 June, 2023</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="badge bg-soft-warning text-warning">2</a>
                                                </td>
                                                <td><a href="javascript:void(0);">95%</a></td>
                                            </tr>
                                            <tr>
                                                <td class="position-relative">
                                                    <div class="ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 border-primary rounded"></div>
                                                    <a href="javascript:void(0);">K. Hune</a>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="avatar-image avatar-md">
                                                        <img src="assets/images/avatar/3.png" alt="" class="img-fluid">
                                                    </a>
                                                </td>
                                                <td>18 June, 2023</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="badge bg-soft-primary text-primary">3</a>
                                                </td>
                                                <td><a href="javascript:void(0);">93%</a></td>
                                            </tr>
                                            <tr>
                                                <td class="position-relative">
                                                    <div class="ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 border-danger rounded"></div>
                                                    <a href="javascript:void(0);">M. Hanvey</a>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="avatar-image avatar-md">
                                                        <img src="assets/images/avatar/4.png" alt="" class="img-fluid">
                                                    </a>
                                                </td>
                                                <td>22 June, 2023</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="badge bg-soft-danger text-danger">4</a>
                                                </td>
                                                <td><a href="javascript:void(0);">91%</a></td>
                                            </tr>
                                            <tr>
                                                <td class="position-relative">
                                                    <div class="ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 border-dark rounded"></div>
                                                    <a href="javascript:void(0);">V. Maton</a>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="avatar-image avatar-md">
                                                        <img src="assets/images/avatar/5.png" alt="" class="img-fluid">
                                                    </a>
                                                </td>
                                                <td>25 June, 2023</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="badge bg-soft-primary text-primary">5</a>
                                                </td>
                                                <td><a href="javascript:void(0);">90%</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center">Update: 30 Min Ago</a>
                        </div>
                    </div>
                    <!-- [Leads Status / Student Ranking] end -->
                    <!-- [Mini] start -->
                    <!-- [Leads Overview] end -->
                    <!-- [Latest Leads] start -->
                    <!-- [Latest Leads] end -->
                    <!--! BEGIN: [Upcoming Schedule] !-->
                    
                    <!--! END: [Upcoming Schedule] !-->
                    <!--! BEGIN: [Project Status] !-->
                   
                    <!--! END: [Project Status] !-->
                    <!--! BEGIN: [Team Progress] !-->
                    <!--! END: [Team Progress] !-->
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
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->
    <?php include "includes/customizer.php"; ?>
    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <?php include "includes/scripts.php"; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: { height: 350, type: 'area', stacked: false, toolbar: { show: false } },
            series: [{ name: 'Average Score', data: [65, 70, 75, 80, 78, 82, 85], type: 'area' }],
            stroke: { width: 2, curve: 'smooth', lineCap: 'round' },
            colors: ['#3454d1'],
            dataLabels: { enabled: false },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.8, stops: [0, 100] } },
            xaxis: {
                type: 'category',
                categories: ['2026-01-04','2026-01-11','2026-01-18','2026-01-25','2026-02-01','2026-02-08','2026-02-15'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '11px', colors: '#64748b' } },
                title: { text: 'Date (Week / Month)' }
            },
            yaxis: {
                min: 0,
                max: 100,
                labels: {
                    formatter: function (val) { return val + ' %'; },
                    offsetX: -22,
                    offsetY: 0,
                    style: { fontSize: '11px', color: '#64748b' }
                },
                title: { text: 'Average Score (%)' }
            },
            grid: { padding: { left: 0, right: 0 }, strokeDashArray: 3, borderColor: '#ebebf3', row: { colors: ['#ebebf3','transparent'], opacity: 0.02 } },
            legend: { show: false },
            tooltip: { y: { formatter: function (val) { return val + ' %'; } }, style: { fontSize: '11px', fontFamily: 'Inter' } }
        };

        var chart = new ApexCharts(document.querySelector('#average-score-area-chart'), options);
        chart.render();
    });
    </script>

    <!--! END: Theme Customizer !-->
</body>

</html>