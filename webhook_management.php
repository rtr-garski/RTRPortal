<?php

require_once 'config/db.php';
require_once 'webhook_sender.php';

// ─── Action Handler ───────────────────────────────────────────────────────────

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $url    = trim($_POST['url']    ?? '');
        $event  = trim($_POST['event']  ?? '');
        $secret = trim($_POST['secret'] ?? '');

        if ($url === '' || $event === '') {
            $flash = ['type' => 'danger', 'msg' => 'URL and Event are required.'];
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $flash = ['type' => 'danger', 'msg' => 'Please enter a valid URL.'];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO webhook_endpoints (event, url, secret, active, created_at)
                VALUES (:event, :url, :secret, 1, NOW())
            ");
            $stmt->execute([':event' => $event, ':url' => $url, ':secret' => $secret]);
            $flash = ['type' => 'success', 'msg' => 'Webhook endpoint added successfully.'];
        }

    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM webhook_endpoints WHERE id = ?")->execute([$id]);
            $flash = ['type' => 'success', 'msg' => 'Endpoint deleted.'];
        }

    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE webhook_endpoints SET active = IF(active=1,0,1) WHERE id = ?")->execute([$id]);
            $flash = ['type' => 'success', 'msg' => 'Endpoint status updated.'];
        }

    } elseif ($action === 'test') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $row = $pdo->prepare("SELECT * FROM webhook_endpoints WHERE id = ?");
            $row->execute([$id]);
            $ep = $row->fetch();
            if ($ep) {
                $result = webhookDeliver($ep['url'], [
                    'event'     => $ep['event'],
                    'data'      => ['test' => true, 'message' => 'This is a test webhook delivery.'],
                    'timestamp' => date('c'),
                ], $ep['secret']);
                webhookLog($pdo, $ep['event'], $ep['url'], $result);
                $flash = $result['success']
                    ? ['type' => 'success', 'msg' => "Test delivered — HTTP {$result['http_code']}."]
                    : ['type' => 'warning', 'msg' => "Test failed — HTTP {$result['http_code']}. " . ($result['error'] ?? '')];
            }
        }
    }

    header('Location: webhook_management.php' . ($flash ? '?flash=' . urlencode($flash['type'] . '|' . $flash['msg']) : ''));
    exit;
}

// Restore flash from redirect
if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = explode('|', urldecode($_GET['flash']), 2);
    $flash = ['type' => $ftype, 'msg' => $fmsg];
}

// ─── Fetch Data ───────────────────────────────────────────────────────────────

try {
    $endpoints = $pdo->query("SELECT * FROM webhook_endpoints ORDER BY created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $endpoints = [];
    $flash = ['type' => 'danger', 'msg' => 'Could not load endpoints. Run the DB setup first.'];
}

try {
    $logs = $pdo->query("SELECT * FROM webhook_log ORDER BY sent_at DESC LIMIT 50")->fetchAll();
} catch (Throwable $e) {
    $logs = [];
}

$title = 'Webhook Management';
?>
<!doctype html>
<html lang="en">
<head>
    <?php include('partials/title-meta.php'); ?>
    <?php include('partials/head-css.php'); ?>
</head>

<body>
<div class="wrapper">
    <?php include('partials/topbar.php'); ?>
    <?php include('partials/sidenav.php'); ?>

    <div class="content-page">
        <div class="container-fluid">

            <!-- ─── Page Header ─────────────────────────────────────────────── -->
            <?php $subtitle = 'Settings'; ?>
            <div class="page-title-head d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="page-main-title m-0"><?= $title ?></h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);"><?= $subtitle ?></a></li>
                        <li class="breadcrumb-item active"><?= $title ?></li>
                    </ol>
                </div>
            </div>

            <!-- ─── Flash Message ───────────────────────────────────────────── -->
            <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- ─── Stat Badges ─────────────────────────────────────────────── -->
            <div class="row mb-3">
                <?php
                $total  = count($endpoints);
                $active = count(array_filter($endpoints, fn($e) => $e['active']));
                $failed = count(array_filter($logs, fn($l) => !$l['success']));
                ?>
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

            <!-- ─── Endpoints Table ─────────────────────────────────────────── -->
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
                                    <td>
                                        <span class="text-break fs-xs"><?= htmlspecialchars($ep['url']) ?></span>
                                    </td>
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

                                            <!-- Test -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="test">
                                                <input type="hidden" name="id" value="<?= (int) $ep['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-soft-info" title="Send Test">
                                                    <i class="ti ti-send"></i>
                                                </button>
                                            </form>

                                            <!-- Toggle -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id" value="<?= (int) $ep['id'] ?>">
                                                <button type="submit" class="btn btn-xs <?= $ep['active'] ? 'btn-soft-warning' : 'btn-soft-success' ?>"
                                                        title="<?= $ep['active'] ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="ti ti-<?= $ep['active'] ? 'player-pause' : 'player-play' ?>"></i>
                                                </button>
                                            </form>

                                            <!-- Delete -->
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this endpoint?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $ep['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-soft-danger" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>

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

            <!-- ─── Delivery Log ────────────────────────────────────────────── -->
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
                                    <td class="ps-3 fs-xs text-muted text-nowrap">
                                        <?= date('M j, g:i A', strtotime($log['sent_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline-primary fs-xxs"><?= htmlspecialchars($log['event']) ?></span>
                                    </td>
                                    <td class="fs-xs text-muted text-truncate" style="max-width:220px">
                                        <?= htmlspecialchars($log['url']) ?>
                                    </td>
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
                                            <a href="#" class="badge badge-soft-secondary fs-xxs"
                                               data-bs-toggle="popover"
                                               data-bs-trigger="click"
                                               data-bs-placement="left"
                                               data-bs-html="true"
                                               title="Response"
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
        <!-- end container -->

        <?php include('partials/footer.php'); ?>
    </div>
</div>
<!-- END wrapper -->

<!-- ─── Add Endpoint Modal ─────────────────────────────────────────────────── -->
<div class="modal fade" id="addEndpointModal" tabindex="-1" aria-labelledby="addEndpointModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="webhook_management.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEndpointModalLabel">
                        <i class="ti ti-plus me-1"></i> Add Webhook Endpoint
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="ep-event">Event <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ep-event" name="event"
                               placeholder="e.g. order.created" required>
                        <div class="form-text">The event name that will trigger this webhook.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="ep-url">Endpoint URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="ep-url" name="url"
                               placeholder="https://your-server.com/webhook" required>
                        <div class="form-text">Must be a publicly accessible HTTPS URL.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="ep-secret">
                            Signing Secret
                            <span class="badge badge-soft-secondary fs-xxs ms-1">Optional</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="ep-secret" name="secret"
                                   placeholder="Leave blank to use the global secret">
                            <button class="btn btn-outline-secondary" type="button" id="generateSecret" title="Generate random secret">
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
                        <div class="form-text">Used to sign payloads via <code>X-Webhook-Signature</code> (HMAC-SHA256).</div>
                    </div>

                    <div class="alert alert-info py-2 mb-0 fs-xs">
                        <i class="ti ti-info-circle me-1"></i>
                        Common events: <code>order.created</code>, <code>order.updated</code>, <code>order.completed</code>, <code>order.cancelled</code>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-plug me-1"></i> Register Endpoint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->

<?php include('partials/customizer.php'); ?>
<?php include('partials/footer-scripts.php'); ?>

<script>
// Generate random secret
document.getElementById('generateSecret').addEventListener('click', function () {
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var secret = Array.from(crypto.getRandomValues(new Uint8Array(32)))
        .map(function (b) { return chars[b % chars.length]; })
        .join('');
    document.getElementById('ep-secret').value = secret;
});

// Init popovers
document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
    new bootstrap.Popover(el, { sanitize: false });
});
</script>

</body>
</html>
