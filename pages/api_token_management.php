<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/access.php';
require_page_access('api_token_management', $pdo2);

try {
    $tokens = $pdo->query("SELECT * FROM API_Tokens ORDER BY Timestamp_Issued DESC")->fetchAll();
} catch (Throwable $e) {
    $tokens = [];
}

$total        = count($tokens);
$expiredCount = count(array_filter($tokens, fn($t) => strtotime($t['Timestamp_Expiration']) < time()));
$validCount   = $total - $expiredCount;
?>

<div class="container-fluid">

    <!-- breadcrumb -->
    <div class="page-title-head d-flex align-items-center">
        <div class="flex-grow-1">
            <h4 class="page-main-title m-0">API Token Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                <li class="breadcrumb-item active">API Tokens</li>
            </ol>
        </div>
    </div>

    <!-- flash -->
    <div id="tokenFlash" class="d-none"></div>

    <!-- stats -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="avatar-md rounded bg-primary-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-key fs-xl text-primary"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Total</p>
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
                        <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Valid</p>
                        <h4 class="mb-0"><?= $validCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <span class="avatar-md rounded bg-danger-subtle d-flex align-items-center justify-content-center">
                        <i class="ti ti-clock-off fs-xl text-danger"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Expired</p>
                        <h4 class="mb-0"><?= $expiredCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- token table -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0"><i class="ti ti-key me-1"></i> API Tokens</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTokenModal">
                <i class="ti ti-plus me-1"></i> Generate Token
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead class="bg-light bg-opacity-25 thead-sm border-top border-light">
                        <tr class="text-uppercase fs-xxs align-middle">
                            <th>Label</th>
                            <th>Token</th>
                            <th>Issued</th>
                            <th>Expires</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($tokens)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="ti ti-key-off fs-xxl d-block mb-2"></i>
                                No tokens yet. Generate your first token to start submitting to the API.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tokens as $tok): ?>
                        <?php
                            $exp     = strtotime($tok['Timestamp_Expiration']);
                            $expDate = date('M j, Y', $exp);
                        ?>
                        <tr>
                            <td class="fw-semibold fs-sm"><?= htmlspecialchars($tok['label'] ?? '—') ?></td>
                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm font-monospace" readonly
                                           value="<?= htmlspecialchars($tok['Token']) ?>">
                                    <button class="btn btn-sm btn-icon btn-light copy-tok-btn" type="button"
                                            data-token="<?= htmlspecialchars($tok['Token']) ?>" title="Copy token">
                                        <i class="ti ti-copy fs-lg"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="fs-xs text-muted"><?= date('M j, Y', strtotime($tok['Timestamp_Issued'])) ?></td>
                            <td class="fs-xs">
                                <?php if ($exp < time()): ?>
                                    <span class="badge badge-soft-danger"><?= $expDate ?></span>
                                <?php elseif ($exp < strtotime('+30 days')): ?>
                                    <span class="badge badge-soft-warning"><?= $expDate ?></span>
                                <?php else: ?>
                                    <span class="text-muted"><?= $expDate ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-xs btn-soft-danger delete-tok-btn"
                                        data-id="<?= (int) $tok['id'] ?>" title="Delete permanently">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- instruction guide -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-book me-1"></i> How to Use Your Token</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Include your API token in the <code>X-API-Key</code> header with every request.</p>
            <div class="row g-3">
                <div class="col-lg-6">
                    <h6 class="fw-semibold mb-2">cURL Example</h6>
                    <pre class="bg-light rounded p-3 fs-xs mb-0"><code>curl -X POST https://your-api-endpoint/api/create-order \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_TOKEN_HERE" \
  -d '{"subtype": "IMR", ...}'</code></pre>
                </div>
                <div class="col-lg-6">
                    <h6 class="fw-semibold mb-2">PHP Example</h6>
                    <pre class="bg-light rounded p-3 fs-xs mb-0"><code>$ch = curl_init('https://your-api-endpoint/api/create-order');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-Key: YOUR_TOKEN_HERE',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));</code></pre>
                </div>
            </div>
            <div class="alert alert-warning mt-3 mb-0 py-2 fs-xs">
                <i class="ti ti-alert-triangle me-1"></i>
                Keep your token secret. Never expose it in client-side code or public repositories.
            </div>
        </div>
    </div>

</div>

<!-- create token modal -->
<div class="modal fade" id="createTokenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-key me-1"></i> Generate New API Token</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="tok-label">
                        Token Label <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="tok-label" name="label"
                           placeholder="e.g. Production, Staging, Integration Test" maxlength="100">
                    <div class="form-text">A name to help you identify this token's purpose.</div>
                </div>
                <div class="alert alert-info py-2 mb-0 fs-xs">
                    <i class="ti ti-info-circle me-1"></i>
                    A 64-character secure token will be generated. <strong>You will only see it once</strong> — copy it immediately. Tokens expire after <strong>6 months</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createTokenBtn">
                    <i class="ti ti-key me-1"></i> Generate Token
                </button>
            </div>
        </div>
    </div>
</div>
