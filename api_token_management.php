<?php

require_once 'config/db.php';

//function to generate string
function generateToken(): string {
    return bin2hex(random_bytes(32)); // 64 hex chars
}

function maskToken(string $token): string {
    return substr($token, 0, 8) . '••••••••••••••••••••••••' . substr($token, -4);
}

// hander

$flash    = null;
$newToken = null; // shown only once on creation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $label = trim($_POST['label'] ?? '');

        if ($label === '') {
            $flash = ['type' => 'danger', 'msg' => 'A label is required to identify your token.'];
        } else {
            $token = generateToken();
            $stmt  = $pdo->prepare("
                INSERT INTO api_tokens (label, token, active, created_at)
                VALUES (:label, :token, 1, NOW())
            ");
            $stmt->execute([':label' => $label, ':token' => $token]);
            // Pass the new token through the redirect so it shows exactly once
            header('Location: api_token_management.php?new_token=' . urlencode($token) . '&label=' . urlencode($label));
            exit;
        }

    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE api_tokens SET active = IF(active=1,0,1) WHERE id = ?")->execute([$id]);
            $flash = ['type' => 'success', 'msg' => 'Token status updated.'];
        }

    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM api_tokens WHERE id = ?")->execute([$id]);
            $flash = ['type' => 'success', 'msg' => 'Token revoked and deleted.'];
        }
    }

    if (!$flash) {
        header('Location: api_token_management.php');
        exit;
    }
    // flash set on validation error — fall through to render
}

// onetime new token reveal from redirect
if (isset($_GET['new_token'])) {
    $newToken  = $_GET['new_token'];
    $newLabel  = $_GET['label'] ?? 'New Token';
    $flash     = ['type' => 'success', 'msg' => "Token <strong>" . htmlspecialchars($newLabel) . "</strong> created. Copy it now — it won't be shown again."];
}

// restore flash from redirect
if (!$flash && isset($_GET['flash'])) {
    [$ftype, $fmsg] = explode('|', urldecode($_GET['flash']), 2);
    $flash = ['type' => $ftype, 'msg' => $fmsg];
}

//now to fetch tokeen from db
try {
    $tokens = $pdo->query("SELECT * FROM api_tokens ORDER BY created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $tokens = [];
    $flash  = ['type' => 'warning', 'msg' => 'Could not load tokens — run the DB setup first.'];
}

$total  = count($tokens);
$active = count(array_filter($tokens, fn($t) => $t['active']));

$title = 'API Token Management';
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

            <!-- breadcrums -->
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

            <!-- onetimetokenreveal -->
            <?php if ($newToken): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="mb-2">
                    <i class="ti ti-shield-check me-1"></i>
                    <?= $flash['msg'] ?>
                </div>
                <div class="input-group">
                    <input type="text" id="newTokenValue" class="form-control form-control-sm font-monospace"
                           value="<?= htmlspecialchars($newToken) ?>" readonly>
                    <button class="btn btn-sm btn-success" type="button" id="copyNewToken">
                        <i class="ti ti-copy me-1"></i> Copy
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <?php elseif ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <i class="ti ti-info-circle me-1"></i> <?= $flash['msg'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- stats -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card mb-0">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="avatar-md rounded bg-primary-subtle d-flex align-items-center justify-content-center">
                                <i class="ti ti-key fs-xl text-primary"></i>
                            </span>
                            <div>
                                <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Total Tokens</p>
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
                                <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Active Tokens</p>
                                <h4 class="mb-0"><?= $active ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-0">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="avatar-md rounded bg-danger-subtle d-flex align-items-center justify-content-center">
                                <i class="ti ti-lock-off fs-xl text-danger"></i>
                            </span>
                            <div>
                                <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Revoked / Inactive</p>
                                <h4 class="mb-0"><?= $total - $active ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- token table─ -->
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
                                    <th class="ps-3">Label</th>
                                    <th>Token</th>
                                    <th>Status</th>
                                    <th>Last Used</th>
                                    <th>Created</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($tokens)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="ti ti-key-off fs-xxl d-block mb-2"></i>
                                        No tokens yet. Generate your first token to start submitting to the API.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tokens as $tok): ?>
                                <tr class="<?= !$tok['active'] ? 'opacity-50' : '' ?>">
                                    <td class="ps-3 fw-semibold"><?= htmlspecialchars($tok['label']) ?></td>
                                    <td>
                                        <code class="fs-xs text-muted"><?= htmlspecialchars(maskToken($tok['token'])) ?></code>
                                    </td>
                                    <td>
                                        <?php if ($tok['active']): ?>
                                            <span class="badge badge-soft-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-danger">Revoked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fs-xs text-muted">
                                        <?= $tok['last_used'] ? date('M j, Y g:i A', strtotime($tok['last_used'])) : '—' ?>
                                    </td>
                                    <td class="fs-xs text-muted"><?= date('M j, Y', strtotime($tok['created_at'])) ?></td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end gap-1">

                                            <!-- Toggle active/revoke -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id" value="<?= (int) $tok['id'] ?>">
                                                <button type="submit"
                                                        class="btn btn-xs <?= $tok['active'] ? 'btn-soft-warning' : 'btn-soft-success' ?>"
                                                        title="<?= $tok['active'] ? 'Revoke' : 'Re-activate' ?>">
                                                    <i class="ti ti-<?= $tok['active'] ? 'ban' : 'check' ?>"></i>
                                                </button>
                                            </form>

                                            <!-- Delete -->
                                            <form method="POST" class="d-inline"
                                                  onsubmit="return confirm('Permanently delete this token? Any API calls using it will stop working.')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $tok['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-soft-danger" title="Delete permanently">
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

            <!-- instruction guide -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-book me-1"></i> How to Use Your Token</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Include your API token in the <code>X-API-Key</code> header with every request to our API.</p>

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
                        If a token is compromised, revoke it immediately and generate a new one.
                    </div>
                </div>
            </div>

        </div>
        <!-- end container -->

        <?php include('partials/footer.php'); ?>
    </div>
</div>
<!-- END wrapper -->

<!-- modal token -->
<div class="modal fade" id="createTokenModal" tabindex="-1" aria-labelledby="createTokenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="api_token_management.php">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTokenModalLabel">
                        <i class="ti ti-key me-1"></i> Generate New API Token
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="tok-label">
                            Token Label <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="tok-label" name="label"
                               placeholder="e.g. Production, Staging, Integration Test" required maxlength="100">
                        <div class="form-text">A name to help you identify this token's purpose.</div>
                    </div>

                    <div class="alert alert-info py-2 mb-0 fs-xs">
                        <i class="ti ti-info-circle me-1"></i>
                        A 64-character secure token will be generated for you. <strong>You will only see it once</strong> — copy it immediately after creation.
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-key me-1"></i> Generate Token
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
// copy new token to clipboard
var copyBtn = document.getElementById('copyNewToken');
if (copyBtn) {
    copyBtn.addEventListener('click', function () {
        var input = document.getElementById('newTokenValue');
        navigator.clipboard.writeText(input.value).then(function () {
            copyBtn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
            setTimeout(function () {
                copyBtn.innerHTML = '<i class="ti ti-copy me-1"></i> Copy';
            }, 2000);
        });
    });
}
</script>

</body>
</html>
