<header class="app-topbar">
    <div class="container-fluid topbar-menu">
        <div class="d-flex align-items-center gap-2">
            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="index.php" class="logo-light">
                    <span class="logo-lg">
                        <img src="../source/inspinia5/assets/images/logo.png" alt="logo" />
                    </span>
                    <span class="logo-sm">
                        <img src="../source/inspinia5/assets/images/logo-sm.png" alt="small logo" />
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="index.php" class="logo-dark">
                    <span class="logo-lg">
                        <img src="../source/inspinia5/assets/images/logo-black.png" alt="dark logo" />
                    </span>
                    <span class="logo-sm">
                        <img src="../source/inspinia5/assets/images/logo-sm.png" alt="small logo" />
                    </span>
                </a>
            </div>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-primary btn-icon">
                <i class="ti ti-menu-4"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu">
                <i class="ti ti-menu-4"></i>
            </button>

            <div id="search-box" class="app-search d-none d-xl-flex">
                <input type="search" class="form-control topbar-search" name="search" placeholder="Search for something..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
           
        </div>

        <div class="d-flex align-items-center gap-2">
            <div id="theme-toggler" class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i class="ti ti-moon topbar-link-icon mode-light-moon"></i>
                    <i class="ti ti-sun topbar-link-icon mode-light-sun"></i>
                </button>
            </div>

            <div id="fullscreen-toggler" class="topbar-item d-none d-md-flex">
                <button class="topbar-link" type="button" data-toggle="fullscreen">
                    <i class="ti ti-maximize topbar-link-icon"></i>
                    <i class="ti ti-minimize topbar-link-icon d-none"></i>
                </button>
            </div>

            <div id="monochrome-toggler" class="topbar-item d-none d-xl-flex">
                <button id="monochrome-mode" class="topbar-link" type="button" data-toggle="monochrome">
                    <i class="ti ti-palette topbar-link-icon"></i>
                </button>
            </div>

            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link btn-theme-setting" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button">
                    <i class="ti ti-settings topbar-link-icon"></i>
                </button>
            </div>

            <div id="simple-user-dropdown" class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" href="#!" aria-haspopup="false" aria-expanded="false">
                        <?php $initials = strtoupper(substr($_SESSION['name'] ?? $_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                        <span class="rounded-circle me-lg-2 d-flex align-items-center justify-content-center bg-primary text-white fw-bold"
                              style="width:32px;height:32px;font-size:14px;flex-shrink:0">
                            <?= $initials ?>
                        </span>
                        <div class="d-lg-flex align-items-center gap-1 d-none">
                            <h5 class="my-0"><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['user_name'] ?? 'User') ?></h5>
                            <i class="ti ti-chevron-down align-middle"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- Header -->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome back!</h6>
                        </div>

                        <!-- Wallet -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-credit-card me-1 fs-lg align-middle"></i>
                            <span class="align-middle">
                                Balance:
                                <span class="fw-semibold">$985.25</span>
                            </span>
                        </a>

                        <!-- Support -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-headset me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Support Center</span>
                        </a>

                        <!-- Divider -->
                        <div class="dropdown-divider"></div>

                        <!-- Logout -->
                        <a href="logout.php" class="dropdown-item text-danger fw-semibold">
                            <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Topbar End -->
