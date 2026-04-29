<?php
require_once 'config/session.php';
if (!empty($_SESSION['user_id'])) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    header('Location: ' . $base . '/index.php');
    exit;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
    <head>
        <?php $title = "Sign In"; include('partials/title-meta.php'); ?> <?php include('partials/head-css.php'); ?>
    </head>

    <body>
        <div class="auth-box d-flex align-items-center">
            <div class="container-xxl">
                <div class="row align-items-center justify-content-center">
                    <div class="col-xl-10">
                        <div class="card rounded-4">
                            <div class="row justify-content-between g-0">
                                <div class="col-lg-6">
                                    <div class="card-body">
                                        <div class="auth-brand text-center mb-4">
                                            <a href="index.php" class="logo-dark">
                                                <img src="partials/rtr_logoblack2.png" alt="dark logo" />
                                            </a>
                                            <a href="index.php" class="logo-light">
                                                <img src="partials/rtr_logowhite2.png" alt="logo" />
                                            </a>
                                            <h4 class="fw-bold mt-4">Welcome to Admin</h4>
                                            <p class="text-muted w-lg-75 mx-auto">Let's get you signed in. Enter your username and password to continue.</p>
                                        </div>

                                        <div id="loginError" class="alert alert-danger d-none"></div>

                                        <form id="loginForm">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">
                                                    Username
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="ti ti-user fs-xl text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required />
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="userPassword" class="form-label">
                                                    Password
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="ti ti-lock-password fs-xl text-muted"></i>
                                                    </span>
                                                    <input type="password" class="form-control" id="userPassword" name="password" placeholder="••••••••" required />
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input form-check-input-light fs-14" type="checkbox" id="rememberMe" name="remember_me" value="1" />
                                                    <label class="form-check-label" for="rememberMe">Keep me signed in</label>
                                                </div>
                                            </div>

                                            <div class="d-grid">
                                                <button type="submit" id="loginBtn" class="btn btn-primary fw-semibold py-2">Sign In</button>
                                            </div>
                                        </form>

                                        <p class="text-center text-muted mt-4 mb-0">
                                            ©
                                            <script>document.write(new Date().getFullYear())</script>
                                            RTR-Order Entry Portal
                                        </p>
                                    </div>
                                </div>

                                <div class="col-lg-6 d-none d-lg-block">
                                    <div class="h-100 position-relative card-side-img rounded-end-4 rounded-end rounded-0 overflow-hidden" style="background-image: url('https://loremflickr.com/1514/1121/technology')">
                                        <div class="p-4 card-img-overlay rounded-4 rounded-start-0 auth-overlay d-flex align-items-end justify-content-center"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include('partials/footer-scripts.php'); ?>

    </body>
</html>
