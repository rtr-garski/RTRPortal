<?php include 'partials/main.php'; ?>
    <head>
        <?php $title = "Projects Dashboard"; include('partials/title-meta.php'); ?> 
        
        <?php include('partials/custom-css.php'); ?> 
        <?php include('partials/head-css.php'); ?>
    </head>

    <body>
        <!-- Begin page -->
        <div class="wrapper">
            <?php include('partials/topbar.php'); ?> <?php include('partials/sidenav.php'); ?>

            <!-- ============================================================== -->
            <!-- Start Main Content -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="container-fluid">
                    
                    <!-- DEBUG: remove after checking -->
                    <pre style="background:#111;color:#0f0;padding:1rem;font-size:12px"><?php print_r($_SESSION); ?></pre>

                    <!-- container-->
                    <div id="content"></div>
                   
                </div>
                <!-- container -->

                <?php include('partials/footer.php'); ?>
            </div>

            <!-- ============================================================== -->
            <!-- End of Main Content -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->

        <?php include('partials/customizer.php'); ?> <?php include('partials/footer-scripts.php'); ?>

        <!-- js links-->
        <script src="js/app.js"></script>

    </body>
</html>
