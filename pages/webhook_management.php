<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../config/db.php';

try {
    $endpoints = $pdo2->query("SELECT * FROM webhook_endpoints ORDER BY created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $endpoints = [];
}

try {
    $logs = $pdo2->query("SELECT * FROM webhook_log ORDER BY sent_at DESC LIMIT 50")->fetchAll();
} catch (Throwable $e) {
    $logs = [];
}

$total  = count($endpoints);
$active = count(array_filter($endpoints, fn($e) => $e['active']));
$failed = count(array_filter($logs, fn($l) => !$l['success']));
?>

<div class="container-fluid">

    <!-- breadcrumb -->
    <div class="page-title-head d-flex align-items-center">
        <div class="flex-grow-1">
            <h4 class="page-main-title m-0">Webhook Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                <li class="breadcrumb-item active">Webhooks</li>
            </ol>
        </div>
    </div>

    <!-- flash -->
    <div id="webhookFlash" class="d-none"></div>

    <!-- stats -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="avatar-md rounded bg-primary-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-webhook fs-xl text-primary"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Total Endpoints</p>
                        <h4 class="mb-0"><?= $total ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="avatar-md rounded bg-success-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-circle-check fs-xl text-success"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Active Endpoints</p>
                        <h4 class="mb-0"><?= $active ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="avatar-md rounded bg-danger-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-alert-triangle fs-xl text-danger"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Recent Failures</p>
                        <h4 class="mb-0"><?= $failed ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- endpoint list -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0"><i class="ti ti-plug me-1"></i> Registered Endpoints</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEndpointModal">
                <i class="ti ti-plus me-1"></i> Add Endpoint
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead class="bg-light bg-opacity-25 thead-sm border-top border-light">
                        <tr class="text-uppercase fs-xxs align-middle">
                            <th class="ps-3">Event</th>
                            <th>URL</th>
                            <th>Secret</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($endpoints)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="ti ti-inbox fs-xxl d-block mb-1"></i>
                                No endpoints registered yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($endpoints as $ep): ?>
                        <tr>
                            <td class="ps-3">
                                <span class="badge badge-outline-primary fs-xxs"><?= htmlspecialchars($ep['event']) ?></span>
                            </td>
                            <td><span class="text-break fs-xs"><?= htmlspecialchars($ep['url']) ?></span></td>
                            <td>
                                <?php if ($ep['secret'] !== ''): ?>
                                    <code class="fs-xs text-muted"><?= htmlspecialchars(substr($ep['secret'], 0, 8)) ?>••••</code>
                                <?php else: ?>
                                    <span class="text-muted fs-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ep['active']): ?>
                                    <span class="badge badge-soft-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-xs text-muted"><?= date('M j, Y', strtotime($ep['created_at'])) ?></td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-xs btn-soft-info wh-test-btn"
                                            data-id="<?= (int) $ep['id'] ?>" title="Send Test">
                                        <i class="ti ti-send"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs <?= $ep['active'] ? 'btn-soft-warning' : 'btn-soft-success' ?> wh-toggle-btn"
                                            data-id="<?= (int) $ep['id'] ?>"
                                            title="<?= $ep['active'] ? 'Deactivate' : 'Activate' ?>">
                                        <i class="ti ti-<?= $ep['active'] ? 'player-pause' : 'player-play' ?>"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-soft-danger wh-delete-btn"
                                            data-id="<?= (int) $ep['id'] ?>" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- delivery log -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-history me-1"></i> Recent Delivery Log <small class="text-muted fw-normal">(last 50)</small></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead class="bg-light bg-opacity-25 thead-sm border-top border-light">
                        <tr class="text-uppercase fs-xxs align-middle">
                            <th class="ps-3">Sent At</th>
                            <th>Event</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>HTTP</th>
                            <th>Retries</th>
                            <th class="pe-3">Response</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-clock fs-xxl d-block mb-1"></i>
                                No deliveries logged yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-3 fs-xs text-muted text-nowrap"><?= date('M j, g:i A', strtotime($log['sent_at'])) ?></td>
                            <td><span class="badge badge-outline-primary fs-xxs"><?= htmlspecialchars($log['event']) ?></span></td>
                            <td class="fs-xs text-muted text-truncate" style="max-width:220px"><?= htmlspecialchars($log['url']) ?></td>
                            <td>
                                <?php if ($log['success']): ?>
                                    <span class="badge badge-soft-success"><i class="ti ti-check me-1"></i>OK</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-danger"><i class="ti ti-x me-1"></i>Failed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $log['http_code'] >= 200 && $log['http_code'] < 300 ? 'badge-soft-success' : 'badge-soft-danger' ?> fs-xxs">
                                    <?= (int) $log['http_code'] ?>
                                </span>
                            </td>
                            <td class="fs-xs text-muted"><?= (int) $log['retries'] ?></td>
                            <td class="pe-3">
                                <?php if ($log['response'] || $log['error_msg']): ?>
                                    <a href="#" class="badge badge-soft-secondary fs-xxs wh-log-popover"
                                       data-bs-toggle="popover" data-bs-trigger="click" data-bs-placement="left"
                                       data-bs-html="true" title="Response"
                                       data-bs-content="<pre class='mb-0 fs-xxs' style='max-width:320px;white-space:pre-wrap'><?= htmlspecialchars(substr($log['error_msg'] ?: $log['response'], 0, 400)) ?></pre>">
                                        View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fs-xs">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- add endpoint modal -->
<div class="modal fade" id="addEndpointModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-plus me-1"></i> Add Webhook Endpoint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="ep-event">Event <span class="text-danger">*</span></label>
                    <select class="form-select" id="ep-event" required>
                        <option value="">— Select an event —</option>
                        <option value="order.created">order.created</option>
                        <option value="order.updated">order.updated</option>
                        <option value="order.completed">order.completed</option>
                        <option value="order.cancelled">order.cancelled</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="ep-url">Endpoint URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="ep-url" placeholder="https://your-server.com/webhook">
                    <div class="form-text">Must be a publicly accessible HTTPS URL.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="ep-secret">
                        Signing Secret <span class="badge badge-soft-secondary fs-xxs ms-1">Optional</span>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="ep-secret" placeholder="Leave blank to use the global secret">
                        <button class="btn btn-outline-secondary" type="button" id="generateSecret" title="Generate random secret">
                            <i class="ti ti-refresh"></i>
                        </button>
                    </div>
                    <div class="form-text">Used to sign payloads via <code>X-Webhook-Signature</code> (HMAC-SHA256).</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addEndpointBtn">
                    <i class="ti ti-plug me-1"></i> Register Endpoint
                </button>
            </div>
        </div>
    </div>
</div>
