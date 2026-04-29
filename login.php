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

                                        <?php if (!empty($_GET['sso_error'])): ?>
                                        <div class="alert alert-danger">
                                            <?php
                                            $ssoErrors = [
                                                'unauthorized'    => 'Your Microsoft account is not authorised to access this portal.',
                                                'state_mismatch'  => 'Sign-in session expired. Please try again.',
                                                'token_failed'    => 'Could not retrieve token from Microsoft. Please try again.',
                                                'no_email'        => 'Could not retrieve your email address from Microsoft.',
                                            ];
                                            $key = htmlspecialchars($_GET['sso_error']);
                                            echo $ssoErrors[$key] ?? 'Microsoft sign-in failed. Please try again.';
                                            ?>
                                        </div>
                                        <?php endif; ?>

                                        <div class="d-grid mb-3">
                                            <a href="auth/ms_login.php" class="btn btn-outline-secondary fw-semibold py-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 23 23" class="me-2" style="vertical-align:text-bottom"><path fill="#f25022" d="M0 0h11v11H0z"/><path fill="#00a4ef" d="M0 12h11v11H0z"/><path fill="#7fba00" d="M12 0h11v11H12z"/><path fill="#ffb900" d="M12 12h11v11H12z"/></svg>
                                                Sign in with Microsoft
                                            </a>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <hr class="flex-grow-1 m-0">
                                            <span class="text-muted small">or</span>
                                            <hr class="flex-grow-1 m-0">
                                        </div>

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
