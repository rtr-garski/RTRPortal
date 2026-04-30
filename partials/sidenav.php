<?php
// pdo2 is already open (included via main.php → db.php)
$dept = $_SESSION['department'] ?? 'all';

// All active sections
$sections = $pdo2->query(
    "SELECT id, label FROM nav_sections WHERE is_active = 1 ORDER BY sort_order"
)->fetchAll();

// Items visible to this department — must have an explicit row in nav_item_departments.
$stmt = $pdo2->prepare("
    SELECT ni.*
    FROM nav_items ni
    INNER JOIN nav_item_departments d ON d.item_id = ni.id
    WHERE ni.is_active = 1
      AND d.department = ?
      AND d.is_active = 1
    ORDER BY ni.section_id, ni.sort_order
");
$stmt->execute([$dept]);
$allItems = $stmt->fetchAll();

// Index: top-level items per section, children per parent item
$bySection  = [];   // section_id → [item, ...]
$byParent   = [];   // parent_id  → [item, ...]
foreach ($allItems as $item) {
    if ($item['parent_id'] === null) {
        $bySection[$item['section_id']][] = $item;
    } else {
        $byParent[$item['parent_id']][] = $item;
    }
}
?>
<div class="sidenav-menu">
    <!-- Brand Logo -->
    <a href="index.php" class="logo">
        <span class="logo logo-light">
            <span class="logo-lg"><img src="partials/rtr_logowhite2.png" alt="logo" /></span>
            <span class="logo-sm"><img src="partials/logo-icon.png" alt="small logo" /></span>
        </span>
        <span class="logo logo-dark">
            <span class="logo-lg"><img src="partials/rtr_logoblack2.png" alt="dark logo" /></span>
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
                        <span class="sidenav-user-name fw-bold"><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['user_name'] ?? 'User') ?></span>
                    </a>
                </div>
                <div>
                    <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon"
                       data-bs-toggle="dropdown" data-bs-offset="0,12"
                       href="#!" aria-haspopup="false" aria-expanded="false">
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
                        <a href="logout.php" class="dropdown-item text-danger fw-semibold">
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
<?php foreach ($sections as $section):
    $items = $bySection[$section['id']] ?? [];
    if (empty($items)) continue; // skip sections with no visible items
?>
                <li class="side-nav-title mt-2"><?= htmlspecialchars($section['label']) ?></li>
<?php foreach ($items as $item):
    $children   = $byParent[$item['id']] ?? [];
    $hasChildren = !empty($children);
    $collapseId  = 'nav-collapse-' . (int)$item['id'];
    $icon        = htmlspecialchars($item['icon'] ?? '');
    $label       = htmlspecialchars($item['label']);
    $pageKey     = htmlspecialchars($item['page_key'] ?? '');
    $href        = htmlspecialchars($item['href'] ?? '#');
?>
                <li class="side-nav-item">
<?php if ($hasChildren): ?>
                    <a href="#<?= $collapseId ?>" data-bs-toggle="collapse"
                       class="side-nav-link nav-link">
                        <?php if ($icon): ?><span class="menu-icon"><i class="<?= $icon ?>"></i></span><?php endif; ?>
                        <span class="menu-text"><?= $label ?></span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="<?= $collapseId ?>">
                        <ul class="sub-menu">
<?php foreach ($children as $child):
    $childIcon    = htmlspecialchars($child['icon'] ?? '');
    $childLabel   = htmlspecialchars($child['label']);
    $childPageKey = htmlspecialchars($child['page_key'] ?? '');
    $childHref    = htmlspecialchars($child['href'] ?? '#');
?>
                            <li class="side-nav-item">
                                <a href="<?= $childPageKey ? '#' : $childHref ?>"
                                   <?= $childPageKey ? 'data-page="' . $childPageKey . '"' : '' ?>
                                   class="side-nav-link nav-link">
                                    <?php if ($childIcon): ?><span class="menu-icon"><i class="<?= $childIcon ?>"></i></span><?php endif; ?>
                                    <span class="menu-text"><?= $childLabel ?></span>
                                </a>
                            </li>
<?php endforeach; ?>
                        </ul>
                    </div>
<?php else: ?>
                    <a href="<?= $pageKey ? '#' : $href ?>"
                       <?= $pageKey ? 'data-page="' . $pageKey . '"' : '' ?>
                       class="side-nav-link nav-link">
                        <?php if ($icon): ?><span class="menu-icon"><i class="<?= $icon ?>"></i></span><?php endif; ?>
                        <span class="menu-text"><?= $label ?></span>
                    </a>
<?php endif; ?>
                </li>
<?php endforeach; ?>
<?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<!-- Sidenav Menu End -->
