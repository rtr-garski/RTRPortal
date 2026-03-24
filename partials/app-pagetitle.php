<?php if (!empty($pageTitle)): ?>
    <h1 class="subheader-title mb-2"><?php echo $pageTitle; ?></h1>
<?php endif; ?>

<?php if (!empty($pageTitle)): ?>
    <nav class="app-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb ms-0 text-muted mb-0">

            <?php if (!empty($pageSubTitle1)): ?>
                <li class="breadcrumb-item"><?php echo $pageSubTitle1; ?></li>
            <?php endif; ?>

            <?php if (!empty($pageSubTitle2)): ?>
                <li class="breadcrumb-item"><?php echo $pageSubTitle2; ?></li>
            <?php endif; ?>

            <li class="breadcrumb-item active" aria-current="page">
                <?php echo $pageTitle; ?>
            </li>

        </ol>
    </nav>
<?php endif; ?>

<?php if (isset($pageSubText) && $pageSubText !== "false" && $pageSubText !== ""): ?>
    <h6 class="mt-3 mb-4 fst-italic"><?php echo $pageSubText; ?></h6>
<?php endif; ?>