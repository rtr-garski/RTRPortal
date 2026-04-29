<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../config/db.php';

$logs = $pdo2->query("
    SELECT l.*, u.name AS display_name
    FROM sys_login_logs l
    LEFT JOIN sys_users u ON u.user_id = l.user_id
    ORDER BY l.created_at DESC
    LIMIT 500
")->fetchAll();

$total    = count($logs);
$success  = count(array_filter($logs, fn($r) => $r['status'] === 'success'));
$failed   = $total - $success;
$sso      = count(array_filter($logs, fn($r) => $r['login_method'] === 'microsoft_sso'));
?>

<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Settings</a></li>
                        <li class="breadcrumb-item active">Login Logs</li>
                    </ol>
                </div>
                <h4 class="page-title">Login Logs</h4>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row row-cols-md-4 row-cols-1 g-1 mb-1">
        <div class="col">
            <div class="card mb-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="avatar-title text-bg-primary rounded-circle fs-22 avatar-md flex-shrink-0">
                            <i class="ti ti-login"></i>
                        </span>
                        <h3 class="mb-0"><?= $total ?></h3>
                    </div>
                    <p class="mb-0 text-muted">Total Logins (last 500)</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="avatar-title text-bg-success rounded-circle fs-22 avatar-md flex-shrink-0">
                            <i class="ti ti-check"></i>
                        </span>
                        <h3 class="mb-0"><?= $success ?></h3>
                    </div>
                    <p class="mb-0 text-muted">Successful</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="avatar-title text-bg-danger rounded-circle fs-22 avatar-md flex-shrink-0">
                            <i class="ti ti-shield-x"></i>
                        </span>
                        <h3 class="mb-0"><?= $failed ?></h3>
                    </div>
                    <p class="mb-0 text-muted">Failed Attempts</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="avatar-title text-bg-info rounded-circle fs-22 avatar-md flex-shrink-0">
                            <i class="ti ti-brand-windows"></i>
                        </span>
                        <h3 class="mb-0"><?= $sso ?></h3>
                    </div>
                    <p class="mb-0 text-muted">Microsoft SSO</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div data-table data-table-rows-per-page="25" class="card">
                <div class="card-header border-light justify-content-between">
                    <div class="app-search">
                        <input data-table-search type="search" class="form-control" placeholder="Search logs..." />
                        <i class="ti ti-search app-search-icon text-muted"></i>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <select data-table-set-rows-per-page class="form-select form-control">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom table-centered table-hover w-100 mb-0">
                        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                            <tr class="text-uppercase fs-xxs">
                                <th>Time</th>
                                <th>User</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>IP Address</th>
                                <th>Browser / Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= htmlspecialchars($log['created_at']) ?></td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($log['display_name'] ?? $log['user_name']) ?></div>
                                    <div class="text-muted fs-11"><?= htmlspecialchars($log['user_name']) ?></div>
                                </td>
                                <td>
                                    <?php if ($log['login_method'] === 'microsoft_sso'): ?>
                                        <span class="badge badge-soft-info"><i class="ti ti-brand-windows me-1"></i>Microsoft SSO</span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-secondary"><i class="ti ti-lock me-1"></i>Password</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'success'): ?>
                                        <span class="badge badge-soft-success">Success</span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted fs-12"><?= htmlspecialchars($log['fail_reason'] ?? '—') ?></td>
                                <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                                <td class="text-muted fs-11" style="max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                    <?= htmlspecialchars($log['user_agent'] ?? '—') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div data-table-pagination-info></div>
                        <div data-table-pagination></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
