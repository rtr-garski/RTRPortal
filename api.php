<?php

require_once __DIR__ . '/apiportal/functions2.php';

// ─── Process form submission ───────────────────────────────────────────────────

$result       = null;
$statusCode   = null;
$responseTime = null;
$formError    = null;
$curlCommand  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $apiKey   = trim($_POST['api_key']   ?? '');
    $jsonBody = trim($_POST['json_body'] ?? '');

    if (empty($apiKey) || empty($jsonBody)) {
        $formError = "API Key and JSON Body are required.";
    } else {
        $start = microtime(true);

        if ($apiKey !== API_TOKEN) {
            $statusCode = 401;
            $result     = json_encode([
                'success'   => false,
                'message'   => 'Unauthorized — invalid or missing API key',
                'data'      => null,
                'timestamp' => date('c'),
            ], JSON_PRETTY_PRINT);

        } else {
            $input = json_decode($jsonBody, true);
            if ($input === null) {
                $statusCode = 400;
                $result     = json_encode([
                    'success'   => false,
                    'message'   => 'Invalid or missing JSON body',
                    'data'      => null,
                    'timestamp' => date('c'),
                ], JSON_PRETTY_PRINT);
            } else {
                try {
                    $payload    = buildPayload($input);
                    $statusCode = 201;
                    $result     = json_encode([
                        'success'   => true,
                        'message'   => 'Submission received successfully',
                        'data'      => $payload,
                        'timestamp' => date('c'),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                    $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                                   || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                                   ? 'https' : 'http';
                    $host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
                    $dir         = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                    $endpointRaw = $scheme . '://' . $host . $dir . '/apiportal/receiver2.php';

                    $curlCommand = 'curl -X POST "' . $endpointRaw . '" \\' . "\n"
                                 . '  -H "Content-Type: application/json" \\' . "\n"
                                 . '  -H "X-API-Key: ' . $apiKey . '" \\' . "\n"
                                 . "  -d '" . $jsonBody . "'";

                } catch (ApiValidationException $e) {
                    $statusCode = 400;
                    $result     = json_encode([
                        'success'   => false,
                        'message'   => $e->getMessage(),
                        'data'      => null,
                        'timestamp' => date('c'),
                    ], JSON_PRETTY_PRINT);
                }
            }
        }

        $responseTime = round((microtime(true) - $start) * 1000);
    }
}

// ─── Endpoint URL for display ─────────────────────────────────────────────────

$scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
               ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$dir         = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$endpointUrl = htmlspecialchars($scheme . '://' . $host . $dir . '/apiportal/receiver2.php', ENT_QUOTES, 'UTF-8');

// ─── Preserve form values ─────────────────────────────────────────────────────

$fApiKey   = htmlspecialchars($_POST['api_key'] ?? API_TOKEN, ENT_QUOTES, 'UTF-8');
$fJsonBody = htmlspecialchars($_POST['json_body'] ?? '{
  "subtype": "IMR",
  "case_no": "ADJ1234567",
  "doi_start": "2023-01-15",
  "doi_end": "2023-06-30",
  "court_venue": {
    "name": "WCAB Los Angeles",
    "address": "320 W 4th St",
    "city": "Los Angeles",
    "state": "CA",
    "phone": "213-555-0100"
  },
  "letter_of_rep_date": "2023-02-01",
  "insurance_carriers": [
    {
      "name": "State Fund Insurance",
      "address": "123 Insurance Blvd",
      "city": "Sacramento",
      "state": "CA",
      "zip": "95814",
      "phone": "916-555-0200",
      "adjuster_name": "John Smith",
      "adjuster_phone": "916-555-0201",
      "adjuster_fax": "916-555-0202",
      "adjuster_email": "john.smith@statefund.com",
      "claim_no": "SF-2023-001"
    }
  ],
  "opposing_counsel": [
    {
      "name": "Jane Doe",
      "address": "456 Law Ave",
      "city": "Los Angeles",
      "state": "CA",
      "zip": "90001",
      "phone": "213-555-0300"
    }
  ],
  "employer_name": "ABC Company Inc.",
  "patient": {
    "name": "John Patient",
    "dob": "1980-05-15",
    "ssn": "123-45-6789",
    "street": "789 Patient St",
    "city": "Los Angeles",
    "state": "CA",
    "zip": "90002"
  },
  "records_locations": [
    {
      "priority": "standard",
      "record_type": "medical",
      "date_needed": "2024-03-01",
      "location": {
        "name": "UCLA Medical Center",
        "address": "100 Medical Plaza Dr",
        "phone": "310-555-0400"
      },
      "special_instruction": "Please call ahead before arrival"
    }
  ],
  "attachments": []
}', ENT_QUOTES, 'UTF-8');

// ─── Status badge colour ──────────────────────────────────────────────────────

$badgeClass = 'secondary';
if ($statusCode) {
    if ($statusCode >= 200 && $statusCode < 300)     $badgeClass = 'success';
    elseif ($statusCode >= 400 && $statusCode < 500) $badgeClass = 'warning';
    elseif ($statusCode >= 500)                       $badgeClass = 'danger';
}
?>
<!doctype html>
<html lang="en">
    <head>
        <?php $title = "API Portal"; include('partials/title-meta.php'); ?>
        <?php include('partials/head-css.php'); ?>
        <style>
            :root {
                --dark-bg: #1a1e2e;
                --code-bg: #272c3f;
                --accent:  #4f5de4;
            }

            .mono { font-family: 'Courier New', monospace; font-size: .8rem; }

            .endpoint-bar {
                background: var(--code-bg);
                color: #7ec8e3;
                border-radius: .4rem;
                padding: .45rem .85rem;
                font-family: 'Courier New', monospace;
                font-size: .76rem;
                word-break: break-all;
            }
            .endpoint-bar .method { color: #ffcb6b; font-weight: 700; margin-right: .5rem; }

            .result-box {
                background: var(--dark-bg);
                color: #a8d8a8;
                border-radius: .5rem;
                padding: 1.1rem 1.25rem;
                font-family: 'Courier New', monospace;
                font-size: .78rem;
                white-space: pre-wrap;
                word-break: break-all;
                min-height: 220px;
                max-height: 520px;
                overflow-y: auto;
            }
            .result-box.is-error { color: #f08080; }
            .result-box.is-empty { color: #555; }

            .curl-preview {
                background: var(--code-bg);
                color: #cdd3e0;
                border-radius: .5rem;
                padding: .9rem 1.1rem;
                font-family: 'Courier New', monospace;
                font-size: .73rem;
                white-space: pre-wrap;
                word-break: break-all;
            }
            .curl-preview .kw  { color: #7ec8e3; }
            .curl-preview .str { color: #c3e88d; }
            .curl-preview .opt { color: #ffcb6b; }

            .schema-wrap { overflow-x: auto; }
            .schema-table { font-size: .78rem; min-width: 560px; }
            .schema-table th { background: #f8f9fa; font-size: .74rem; text-transform: uppercase; letter-spacing: .4px; color: #888; }
            .schema-table td { vertical-align: middle; }
            .badge-req   { background: #e8f0fe; color: #3b4bcf;  font-size: .68rem; font-weight: 600; padding: .2rem .45rem; border-radius: .25rem; }
            .badge-opt   { background: #f0f0f0; color: #888;     font-size: .68rem; font-weight: 600; padding: .2rem .45rem; border-radius: .25rem; }
            .badge-multi { background: #fff3cd; color: #856404;  font-size: .68rem; font-weight: 600; padding: .2rem .45rem; border-radius: .25rem; }

            .btn-send { background: var(--accent); border: none; font-weight: 600; }
            .btn-send:hover  { background: #3b4bcf; }
            .btn-send:active { transform: scale(.98); }

            .meta-bar { font-size: .76rem; color: #666; }

            .success-curl-card { border: 1px solid #bbf7d0 !important; }
            .success-curl-header { background: #f0fdf4; border-bottom: 1px solid #bbf7d0; }
        </style>
    </head>

    <body>
        <div class="wrapper">
            <?php include('partials/topbar.php'); ?> <?php include('partials/sidenav.php'); ?>

            <div class="content-page">
                <div class="container-fluid">
                    <?php $subtitle = "API"; $title = "Submission Receiver"; include('partials/page-title.php'); ?>

                    <div class="row g-4 pb-4">

                        <!-- ═══ LEFT: Request Form ═══════════════════════════ -->
                        <div class="col-xl-5 col-lg-6">

                            <div class="card">
                                <div class="card-header">Request</div>
                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Endpoint</label>
                                        <div class="endpoint-bar">
                                            <span class="method">POST</span><?= $endpointUrl ?>
                                        </div>
                                    </div>

                                    <?php if ($formError): ?>
                                        <div class="alert alert-danger py-2" style="font-size:.82rem"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>

                                    <form method="POST" id="apiForm">

                                        <div class="mb-3">
                                            <label class="form-label" for="api_key">
                                                API Key
                                                <span class="text-muted fw-normal">(X-API-Key header)</span>
                                            </label>
                                            <input type="text" class="form-control mono" id="api_key" name="api_key"
                                                   value="<?= $fApiKey ?>" required autocomplete="off">
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="json_body">
                                                JSON Body
                                                <span id="jsonStatus" class="fw-normal ms-2" style="font-size:.78rem"></span>
                                            </label>
                                            <textarea class="form-control mono" id="json_body" name="json_body"
                                                      rows="22" required><?= $fJsonBody ?></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-send btn-primary w-100 py-2">
                                            Send Request
                                        </button>

                                    </form>
                                </div>
                            </div>

                            <!-- live cURL preview -->
                            <div class="card mt-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    cURL Preview
                                    <button class="btn btn-sm btn-outline-secondary" id="copyPreviewBtn" type="button">Copy</button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="curl-preview" id="curlPreview"></div>
                                </div>
                            </div>

                        </div>

                        <!-- ═══ RIGHT: Response + cURL result + Schema ═══════ -->
                        <div class="col-xl-7 col-lg-6 d-flex flex-column gap-3">

                            <!-- Response -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Response</span>
                                    <?php if ($statusCode): ?>
                                        <span class="d-flex align-items-center gap-3 meta-bar">
                                            <span>Status:&nbsp;<span class="badge bg-<?= $badgeClass ?>"><?= $statusCode ?></span></span>
                                            <span>Time: <strong><?= $responseTime ?>ms</strong></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($result !== null): ?>
                                        <div class="result-box <?= ($statusCode >= 400) ? 'is-error' : '' ?>"><?= htmlspecialchars($result, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php else: ?>
                                        <div class="result-box is-empty">// Response will appear here after you send a request.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- cURL Equivalent — shown only after a successful 2xx response -->
                            <?php if ($curlCommand): ?>
                            <div class="card success-curl-card">
                                <div class="card-header success-curl-header d-flex justify-content-between align-items-center">
                                    <span class="text-success fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1 mb-1" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Submission Accepted — cURL Equivalent
                                    </span>
                                    <button class="btn btn-sm btn-outline-success" id="copyCurlResult" type="button">Copy</button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="curl-preview" id="curlResultBox"><?= htmlspecialchars($curlCommand, ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Schema Reference -->
                            <div class="card">
                                <div class="card-header">Request Schema</div>
                                <div class="card-body p-0 schema-wrap">
                                    <table class="table table-sm table-hover mb-0 schema-table">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="ps-3" style="width:32%">Field</th>
                                                <th style="width:12%">Type</th>
                                                <th style="width:14%"></th>
                                                <th class="pe-3">Description / Rules</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">CASE INFORMATION</td></tr>
                                            <tr>
                                                <td class="ps-3"><code>subtype</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted"><code>IMR</code>, <code>SIBTF</code>, <code>Special Notice of Lawsuit</code>, <code>Trial Depo Subpoena for WCAB</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>case_no</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted">WCAB Case No / ADJ number</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>doi_start</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted">Date of Injury start — <code>YYYY-MM-DD</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>doi_end</code></td>
                                                <td>string</td>
                                                <td><span class="badge-opt">optional</span></td>
                                                <td class="pe-3 text-muted">Date of Injury end — <code>YYYY-MM-DD</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>court_venue</code></td>
                                                <td>object</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted"><code>name</code>, <code>address</code>, <code>city</code>, <code>state</code>, <code>phone</code> — all required</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>letter_of_rep_date</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted">Letter of Representation date — <code>YYYY-MM-DD</code></td>
                                            </tr>

                                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">PARTIES</td></tr>
                                            <tr>
                                                <td class="ps-3"><code>insurance_carriers</code></td>
                                                <td>array</td>
                                                <td><span class="badge-opt">optional</span> <span class="badge-multi">multiple</span></td>
                                                <td class="pe-3 text-muted">Each: <code>name*</code>, <code>address</code>, <code>city</code>, <code>state</code>, <code>zip</code>, <code>phone</code>, <code>adjuster_name</code>, <code>adjuster_phone</code>, <code>adjuster_fax</code>, <code>adjuster_email</code>, <code>claim_no</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>opposing_counsel</code></td>
                                                <td>array</td>
                                                <td><span class="badge-opt">optional</span> <span class="badge-multi">multiple</span></td>
                                                <td class="pe-3 text-muted">Each: <code>name*</code>, <code>address</code>, <code>city</code>, <code>state</code>, <code>zip</code>, <code>phone</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>employer_name</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted">Name of employer</td>
                                            </tr>

                                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">PATIENT</td></tr>
                                            <tr>
                                                <td class="ps-3"><code>patient.name</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted">Patient full name</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>patient.dob</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted">Date of birth — <code>YYYY-MM-DD</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>patient.ssn</code></td>
                                                <td>string</td>
                                                <td><span class="badge-opt">optional</span></td>
                                                <td class="pe-3 text-muted">Social security number</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>patient.street / city / state / zip</code></td>
                                                <td>string</td>
                                                <td><span class="badge-opt">optional</span></td>
                                                <td class="pe-3 text-muted">Patient address fields</td>
                                            </tr>

                                            <tr class="table-light"><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">RECORDS LOCATIONS</td></tr>
                                            <tr>
                                                <td class="ps-3"><code>records_locations</code></td>
                                                <td>array</td>
                                                <td><span class="badge-req">required</span> <span class="badge-multi">multiple</span></td>
                                                <td class="pe-3 text-muted">At least one entry required</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>&nbsp;&nbsp;.priority</code></td>
                                                <td>string</td>
                                                <td><span class="badge-opt">optional</span></td>
                                                <td class="pe-3 text-muted"><code>standard</code> (default) or <code>rush</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>&nbsp;&nbsp;.record_type</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3">
                                                    <div class="text-muted mb-1" style="font-size:.74rem">Flexible aliases — normalized server-side:</div>
                                                    <div class="d-flex flex-wrap gap-1" style="font-size:.75rem">
                                                        <code>medical</code>
                                                        <code>billing</code>
                                                        <code>xray</code><span class="text-muted">/mri/imaging/films/radiology</span>
                                                        <code>claimfile</code>
                                                        <code>employmentandpayroll</code>
                                                        <code>payroll</code>
                                                        <code>employment</code>
                                                        <code>wcic</code>
                                                        <code>nonprivileged</code>
                                                        <code>pharmacy</code><span class="text-muted">/prescription/rx</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>&nbsp;&nbsp;.date_needed</code></td>
                                                <td>string</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted"><code>YYYY-MM-DD</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>&nbsp;&nbsp;.location</code></td>
                                                <td>object</td>
                                                <td><span class="badge-req">required</span></td>
                                                <td class="pe-3 text-muted"><code>name*</code>, <code>address*</code>, <code>phone*</code></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3"><code>&nbsp;&nbsp;.special_instruction</code></td>
                                                <td>string</td>
                                                <td><span class="badge-opt">optional</span></td>
                                                <td class="pe-3 text-muted">Free-text instructions</td>
                                            </tr>

                                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">ATTACHMENTS</td></tr>
                                            <tr>
                                                <td class="ps-3"><code>attachments</code></td>
                                                <td>array</td>
                                                <td><span class="badge-opt">optional</span> <span class="badge-multi">multiple</span></td>
                                                <td class="pe-3 text-muted">Each: <code>filename*</code>, <code>mime_type*</code>, <code>data*</code> (base64). Max 10 MB each. Allowed: PDF, JPEG, PNG, TIFF, DOC, DOCX</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <?php include('partials/footer.php'); ?>
            </div>
        </div>

        <?php include('partials/customizer.php'); ?> <?php include('partials/footer-scripts.php'); ?>

        <script>
            const endpoint = <?= json_encode($scheme . '://' . $host . $dir . '/apiportal/receiver2.php') ?>;
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
                    jsonStat.className = 'text-success fw-semibold';
                    jsonStat.textContent = '✓ valid JSON';
                } catch (e) {
                    jsonStat.className = 'text-danger fw-semibold';
                    jsonStat.textContent = '✗ ' + e.message;
                }
            }

            function formatJson() {
                try { bodyEl.value = JSON.stringify(JSON.parse(bodyEl.value), null, 2); } catch (_) {}
            }

            [keyEl, bodyEl].forEach(el => el.addEventListener('input', updatePreview));
            bodyEl.addEventListener('input', validateJson);
            bodyEl.addEventListener('blur',  formatJson);

            document.getElementById('copyPreviewBtn').addEventListener('click', function () {
                const cmd = `curl -X POST "${endpoint}" \\\n  -H "Content-Type: application/json" \\\n  -H "X-API-Key: ${keyEl.value}" \\\n  -d '${bodyEl.value}'`;
                navigator.clipboard.writeText(cmd).then(() => {
                    this.textContent = 'Copied!';
                    setTimeout(() => this.textContent = 'Copy', 1500);
                });
            });

            const copyCurlResult = document.getElementById('copyCurlResult');
            if (copyCurlResult) {
                copyCurlResult.addEventListener('click', function () {
                    const text = document.getElementById('curlResultBox').textContent;
                    navigator.clipboard.writeText(text).then(() => {
                        this.textContent = 'Copied!';
                        setTimeout(() => this.textContent = 'Copy', 1500);
                    });
                });
            }

            updatePreview();
            validateJson();
        </script>
    </body>
</html>
