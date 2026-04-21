<div class="sidenav-menu">
    <!-- Brand Logo -->
    <a href="index.php" class="logo">
        <span class="logo logo-light">
            <span class="logo-lg"><img src="partials/rtr_logowhite.png" alt="logo" /></span>
            <!-- <span class="logo-lg"><img src="../source/inspinia5/assets/images/logo.png" alt="logo" /></span> -->
            <span class="logo-sm"><img src="partials/logo-icon.png" alt="small logo" /></span>
        </span>

        <span class="logo logo-dark">
            <span class="logo-lg"><img src="partials/rtr_logoblack.png" alt="dark logo" /></span>
            <span class="logo-sm"><img src="partials/logo-icon.png" alt="small logo" /></span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-on-hover">
        <span class="btn-on-hover-icon"></span>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-offcanvas">
        <i class="ti ti-menu-4 align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar="">
        <div id="user-profile-settings" class="sidenav-user" style="background: url(../source/inspinia5/assets/images/user-bg-pattern.svg)">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="#!" class="link-reset">
                        <!-- <img src="../source/inspinia5/assets/images/users/user-1.jpg" alt="user-image" class="rounded-circle mb-2 avatar-md" /> -->
                        <span class="sidenav-user-name fw-bold">Client Name</span>
                        <!-- <span class="fs-12 fw-semibold" data-lang="user-role">Verifier</span> -->
                    </a>
                </div>
                <div>
                    <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon" data-bs-toggle="dropdown" data-bs-offset="0,12" href="#!" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                    </a>

                    <div class="dropdown-menu">
                       
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome back!</h6>
                        </div>
                        
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-settings-2 me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Account Settings</span>
                        </a>

                        <a href="javascript:void(0);" class="dropdown-item text-danger fw-semibold">
                            <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!--- Sidenav Menu -->
        <div id="sidenav-menu">
            <ul class="side-nav">
                <li class="side-nav-title mt-2" data-lang="main">Main</li>
                <li class="side-nav-item">
                    <a href="api.php" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-api"></i></span>
                        <span class="menu-text">API Portal</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#dashboards" aria-expanded="false" aria-controls="dashboards" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                        <span class="menu-text" data-lang="dashboards">Dashboards</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="dashboards">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="javascript:void(0);" class="side-nav-link">
                                    <span class="menu-text" data-lang="dashboard-ecommerce">Ecommerce</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="javascript:void(0);" class="side-nav-link">
                                    <span class="menu-text" data-lang="dashboard-analytics">Analytics</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="javascript:void(0);" class="side-nav-link">
                                    <span class="menu-text" data-lang="dashboard-projects">Projects</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="side-nav-title mt-2" data-lang="apps">RecordHost Portal</li>

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-table-column"></i></span>
                        <span class="menu-text" data-lang="">Reports</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="tables">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="reports.php" class="side-nav-link">
                                    <span class="menu-text">Order Entry Portal</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="client_reports.php" class="side-nav-link">
                                    <span class="menu-text">Client Portal</span>
                                </a>
                            </li>
                           
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item">
                    <a href="webhook_management.php" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-webhook"></i></span>
                        <span class="menu-text">Webhooks</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="api_token_management.php" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-key"></i></span>
                        <span class="menu-text">API Tokens</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="file_upload.php" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-cloud-upload"></i></span>
                        <span class="menu-text">File Storage</span>
                    </a>
                </li>
               

                <li class="side-nav-title mt-2" data-lang="apps">Old Webprogram</li>

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#archive" aria-expanded="false" aria-controls="archive" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-archive"></i></span>
                        <span class="menu-text" data-lang="archive">Archive</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="archive">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="api_token_management0421.php" class="side-nav-link">
                                    <span class="menu-icon"><i class="ti ti-key"></i></span>
                                    <span class="menu-text">API Tokens_Old</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</div>
<!-- Sidenav Menu End -->
