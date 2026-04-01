<?php

$result       = null;
$statusCode   = null;
$responseTime = null;
$error        = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $apiKey   = trim($_POST['api_key']   ?? '');
    $postUrl  = trim($_POST['post_url']  ?? '');
    $jsonBody = trim($_POST['json_body'] ?? '');

    // Basic server-side checks
    if (empty($apiKey) || empty($postUrl) || empty($jsonBody)) {
        $error = "All fields are required.";
    } elseif (!filter_var($postUrl, FILTER_VALIDATE_URL)) {
        $error = "Post URL is not a valid URL.";
    } elseif (json_decode($jsonBody) === null) {
        $error = "JSON body is not valid JSON.";
    } else {
        $start = microtime(true);

        $ch = curl_init($postUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonBody,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $responseBody = curl_exec($ch);
        $statusCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        $responseTime = round((microtime(true) - $start) * 1000); // ms

        if ($curlError) {
            $error = "cURL error: " . htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8');
        } else {
            $decoded = json_decode($responseBody, true);
            $result  = $decoded !== null
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                : htmlspecialchars($responseBody, ENT_QUOTES, 'UTF-8');
        }
    }
}

// Preserve form values on re-render
$fApiKey   = htmlspecialchars($_POST['api_key']   ?? '', ENT_QUOTES, 'UTF-8');
$fPostUrl  = htmlspecialchars($_POST['post_url']  ?? 'http://localhost/api/create-order.php', ENT_QUOTES, 'UTF-8');
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
    if ($statusCode >= 200 && $statusCode < 300) $badgeClass = 'success';
    elseif ($statusCode >= 400 && $statusCode < 500) $badgeClass = 'warning';
    elseif ($statusCode >= 500) $badgeClass = 'danger';
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
    <span>Send a POST request and inspect the response</span>
</div>

<div class="container-fluid px-4">
    <div class="row g-4">

        <!-- ── Left column: form ── -->
        <div class="col-xl-5 col-lg-6">
            <div class="card">
                <div class="card-header py-3">Request</div>
                <div class="card-body">

                    <form method="POST" id="apiForm">

                        <div class="mb-3">
                            <label class="form-label" for="post_url">POST URL</label>
                            <input type="url" class="form-control" id="post_url" name="post_url"
                                   value="<?= $fPostUrl ?>" required placeholder="https://yoursite.com/api/create-order.php">
                        </div>

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
                    <div class="curl-preview" id="curlPreview">Fill in the fields above to preview the cURL command.</div>
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
                        <div class="result-box"><?= $result ?></div>
                    <?php else: ?>
                        <div class="result-box" style="color:#555;">
// Response will appear here after you send a request.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const urlEl    = document.getElementById('post_url');
    const keyEl    = document.getElementById('api_key');
    const bodyEl   = document.getElementById('json_body');
    const preview  = document.getElementById('curlPreview');
    const jsonStat = document.getElementById('jsonStatus');

    function escapeHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function updatePreview() {
        const url  = urlEl.value  || '<POST_URL>';
        const key  = keyEl.value  || '<API_KEY>';
        const body = bodyEl.value || '{}';

        preview.innerHTML =
            `<span class="kw">curl</span> <span class="opt">-X POST</span> <span class="str">"${escapeHtml(url)}"</span> \\\n` +
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

    [urlEl, keyEl, bodyEl].forEach(el => el.addEventListener('input', updatePreview));
    bodyEl.addEventListener('input', validateJson);
    bodyEl.addEventListener('blur',  formatJson);

    document.getElementById('copyBtn').addEventListener('click', function () {
        const url  = urlEl.value  || '';
        const key  = keyEl.value  || '';
        const body = bodyEl.value || '{}';
        const cmd  = `curl -X POST "${url}" \\\n  -H "Content-Type: application/json" \\\n  -H "X-API-Key: ${key}" \\\n  -d '${body}'`;
        navigator.clipboard.writeText(cmd).then(() => {
            this.textContent = 'Copied!';
            setTimeout(() => this.textContent = 'Copy', 1500);
        });
    });

    // Init preview on page load
    updatePreview();
    validateJson();
</script>

</body>
</html>
