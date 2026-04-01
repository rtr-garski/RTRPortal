<?php

define('API_INCLUDED', true);
require_once __DIR__ . '/create-order.php';

$result       = null;
$statusCode   = null;
$responseTime = null;
$error        = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $apiKey   = trim($_POST['api_key']   ?? '');
    $jsonBody = trim($_POST['json_body'] ?? '');

    if (empty($apiKey) || empty($jsonBody)) {
        $error = "All fields are required.";
    } else {
        $start = microtime(true);

        if ($apiKey !== API_TOKEN) {
            $statusCode = 401;
            $result     = json_encode(["success" => false, "message" => "Unauthorized - Invalid API Key", "data" => null], JSON_PRETTY_PRINT);
        } else {
            $input = json_decode($jsonBody, true);
            if ($input === null) {
                $error = "JSON body is not valid JSON.";
            } else {
                $validationError = validateOrderPayload($input);
                if ($validationError) {
                    $statusCode = 400;
                    $result     = json_encode(["success" => false, "message" => $validationError, "data" => null], JSON_PRETTY_PRINT);
                } else {
                    $order      = buildOrder($input);
                    $statusCode = 201;
                    $result     = json_encode(["success" => true, "message" => "Order created successfully", "data" => $order], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
            }
        }

        $responseTime = round((microtime(true) - $start) * 1000);
    }
}

// Endpoint URL for display / cURL preview only
$scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
               ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$dir         = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$endpointUrl = htmlspecialchars($scheme . '://' . $host . $dir . '/create-order.php', ENT_QUOTES, 'UTF-8');

// Preserve form values on re-render
$fApiKey   = htmlspecialchars($_POST['api_key']   ?? API_TOKEN, ENT_QUOTES, 'UTF-8');
$fJsonBody = htmlspecialchars($_POST['json_body'] ?? '{
  "customer_name": "John Smith",
  "customer_email": "john@example.com",
  "phone_number": "555-1234",
  "items": [
    {
      "sku": "ITEM-001",
      "name": "Widget",
      "quantity": 2,
      "unit_price": 19.99
    }
  ],
  "shipping_address": {
    "street": "123 Main St",
    "city": "Austin",
    "state": "TX",
    "postal": "78701",
    "country": "US"
  },
  "notes": "Please leave at front door"
}', ENT_QUOTES, 'UTF-8');

// Status badge colour
$badgeClass = 'secondary';
if ($statusCode) {
    if ($statusCode >= 200 && $statusCode < 300)      $badgeClass = 'success';
    elseif ($statusCode >= 400 && $statusCode < 500)  $badgeClass = 'warning';
    elseif ($statusCode >= 500)                        $badgeClass = 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tester &mdash; Create Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; }

        .page-header {
            background: #1a1e2e;
            color: #fff;
            padding: 1.25rem 2rem;
            margin-bottom: 2rem;
        }
        .page-header h1 { font-size: 1.3rem; margin: 0; font-weight: 600; letter-spacing: .5px; }
        .page-header span { font-size: .8rem; opacity: .6; }

        .card { border: none; border-radius: .6rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .card-header { background: #fff; border-bottom: 1px solid #eaeaea; font-weight: 600; font-size: .9rem; }

        .form-label { font-size: .85rem; font-weight: 600; color: #444; }
        .form-control, .form-control:focus { font-size: .875rem; }

        .endpoint-bar {
            background: #272c3f;
            color: #7ec8e3;
            border-radius: .4rem;
            padding: .5rem .85rem;
            font-family: 'Courier New', monospace;
            font-size: .78rem;
            word-break: break-all;
        }
        .endpoint-bar .method { color: #ffcb6b; font-weight: 700; margin-right: .5rem; }

        textarea.mono { font-family: 'Courier New', monospace; font-size: .8rem; }

        .result-box {
            background: #1a1e2e;
            color: #a8d8a8;
            border-radius: .5rem;
            padding: 1.25rem;
            font-family: 'Courier New', monospace;
            font-size: .8rem;
            white-space: pre-wrap;
            word-break: break-all;
            min-height: 200px;
            max-height: 500px;
            overflow-y: auto;
        }
        .result-box.error { color: #f08080; }

        .meta-bar { font-size: .78rem; color: #666; }

        .curl-preview {
            background: #272c3f;
            color: #cdd3e0;
            border-radius: .5rem;
            padding: 1rem 1.25rem;
            font-family: 'Courier New', monospace;
            font-size: .75rem;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .curl-preview .kw  { color: #7ec8e3; }
        .curl-preview .str { color: #c3e88d; }
        .curl-preview .opt { color: #ffcb6b; }

        .btn-send { background: #4f5de4; border: none; font-weight: 600; letter-spacing: .3px; }
        .btn-send:hover { background: #3b4bcf; }
        .btn-send:active { transform: scale(.98); }
    </style>
</head>
<body>

<div class="page-header">
    <h1>API Tester</h1>
    <span>Create Order &mdash; direct PHP execution</span>
</div>

<div class="container-fluid px-4">
    <div class="row g-4">

        <!-- ── Left column: form ── -->
        <div class="col-xl-5 col-lg-6">
            <div class="card">
                <div class="card-header py-3">Request</div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Endpoint</label>
                        <div class="endpoint-bar">
                            <span class="method">POST</span><?= $endpointUrl ?>
                        </div>
                    </div>

                    <form method="POST" id="apiForm">

                        <div class="mb-3">
                            <label class="form-label" for="api_key">
                                API Key
                                <span class="text-muted fw-normal">(sent as <code>X-API-Key</code> header)</span>
                            </label>
                            <input type="text" class="form-control mono" id="api_key" name="api_key"
                                   value="<?= $fApiKey ?>" required placeholder="Your API token">
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="json_body">JSON Body</label>
                            <textarea class="form-control mono" id="json_body" name="json_body"
                                      rows="16" required><?= $fJsonBody ?></textarea>
                            <div class="form-text" id="jsonStatus"></div>
                        </div>

                        <button type="submit" class="btn btn-send btn-primary w-100 py-2">
                            Send Request
                        </button>

                    </form>

                </div>
            </div>

            <!-- cURL preview -->
            <div class="card mt-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    cURL Preview
                    <button class="btn btn-sm btn-outline-secondary" id="copyBtn" type="button">Copy</button>
                </div>
                <div class="card-body p-0">
                    <div class="curl-preview" id="curlPreview"></div>
                </div>
            </div>
        </div>

        <!-- ── Right column: response ── -->
        <div class="col-xl-7 col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Response</span>
                    <?php if ($statusCode): ?>
                        <span class="d-flex align-items-center gap-3 meta-bar">
                            <span>
                                Status:&nbsp;<span class="badge bg-<?= $badgeClass ?> fs-6"><?= $statusCode ?></span>
                            </span>
                            <span>Time: <strong><?= $responseTime ?>ms</strong></span>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="result-box error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php elseif ($result !== null): ?>
                        <div class="result-box"><?= htmlspecialchars($result, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php else: ?>
                        <div class="result-box" style="color:#555;">// Response will appear here after you send a request.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const endpoint = <?= json_encode($scheme . '://' . $host . $dir . '/create-order.php') ?>;
    const keyEl    = document.getElementById('api_key');
    const bodyEl   = document.getElementById('json_body');
    const preview  = document.getElementById('curlPreview');
    const jsonStat = document.getElementById('jsonStatus');

    function escapeHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function updatePreview() {
        const key  = keyEl.value  || '<API_KEY>';
        const body = bodyEl.value || '{}';

        preview.innerHTML =
            `<span class="kw">curl</span> <span class="opt">-X POST</span> <span class="str">"${escapeHtml(endpoint)}"</span> \\\n` +
            `  <span class="opt">-H</span> <span class="str">"Content-Type: application/json"</span> \\\n` +
            `  <span class="opt">-H</span> <span class="str">"X-API-Key: ${escapeHtml(key)}"</span> \\\n` +
            `  <span class="opt">-d</span> <span class="str">'${escapeHtml(body)}'</span>`;
    }

    function validateJson() {
        const v = bodyEl.value.trim();
        if (!v) { jsonStat.textContent = ''; return; }
        try {
            JSON.parse(v);
            jsonStat.className = 'form-text text-success';
            jsonStat.textContent = 'Valid JSON';
        } catch (e) {
            jsonStat.className = 'form-text text-danger';
            jsonStat.textContent = 'Invalid JSON: ' + e.message;
        }
    }

    function formatJson() {
        try {
            const parsed = JSON.parse(bodyEl.value);
            bodyEl.value = JSON.stringify(parsed, null, 2);
        } catch (_) {}
    }

    [keyEl, bodyEl].forEach(el => el.addEventListener('input', updatePreview));
    bodyEl.addEventListener('input', validateJson);
    bodyEl.addEventListener('blur',  formatJson);

    document.getElementById('copyBtn').addEventListener('click', function () {
        const key  = keyEl.value  || '';
        const body = bodyEl.value || '{}';
        const cmd  = `curl -X POST "${endpoint}" \\\n  -H "Content-Type: application/json" \\\n  -H "X-API-Key: ${key}" \\\n  -d '${body}'`;
        navigator.clipboard.writeText(cmd).then(() => {
            this.textContent = 'Copied!';
            setTimeout(() => this.textContent = 'Copy', 1500);
        });
    });

    updatePreview();
    validateJson();
</script>

</body>
</html>
