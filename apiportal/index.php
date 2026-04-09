<!doctype html>
<html lang="en">
    <head>
        <?php $title = "Starter Page"; include('./partials/title-meta.php'); ?> <?php include('./partials/head-css.php'); ?>
    </head>

    <body>
        <!-- Begin page -->
        <div class="wrapper">
            <?php include('./partials/topbar.php'); ?> <?php include('./partials/sidenav.php'); ?>

            <!-- ============================================================== -->
            <!-- Start Main Content -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="container-fluid"><?php $subtitle = "Pages"; $title = "Starter"; include('./partials/page-title.php'); ?></div>
                <!-- container -->

                <?php include('./partials/footer.php'); ?>
            </div>

            <!-- ============================================================== -->
            <!-- End of Main Content -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->

        <?php include('./partials/customizer.php'); ?> <?php include('./partials/footer-scripts.php'); ?>
    </body>
</html>
