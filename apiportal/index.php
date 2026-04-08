<?php

// ─── Config (must match receiver.php) ─────────────────────────────────────────
define('API_TOKEN', 'K4AwY7EZCRMkUfRPnc2qFCZusN9uPvBH9cT8HjXcrBfHJ492HH');

// ─── Simulate the request server-side ─────────────────────────────────────────
$result       = null;
$statusCode   = null;
$responseTime = null;
$formError    = null;
$curlCommand  = null;   // shown after a successful submission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $apiKey   = trim($_POST['api_key']   ?? '');
    $jsonBody = trim($_POST['json_body'] ?? '');

    if (empty($apiKey) || empty($jsonBody)) {
        $formError = "API Key and JSON Body are required.";
    } else {
        $start = microtime(true);

        // Build a fake request context and include the receiver inline
        if ($apiKey !== API_TOKEN) {
            $statusCode = 401;
            $result     = json_encode(["success" => false, "message" => "Unauthorized — invalid or missing API key", "data" => null, "timestamp" => date('c')], JSON_PRETTY_PRINT);
        } else {
            $input = json_decode($jsonBody, true);
            if ($input === null) {
                $statusCode = 400;
                $result     = json_encode(["success" => false, "message" => "Invalid or missing JSON body", "data" => null, "timestamp" => date('c')], JSON_PRETTY_PRINT);
            } else {
                // Forward to receiver via cURL (self-call)
                $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                               ? 'https' : 'http';
                $host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
                $dir         = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                $receiverUrl = $scheme . '://' . $host . $dir . '/receiver.php';

                $ch = curl_init($receiverUrl);
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $jsonBody,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'X-API-Key: ' . $apiKey,
                    ],
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);

                $raw        = curl_exec($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $decoded = json_decode($raw, true);
                $result  = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                // Build the equivalent cURL command to show after success
                if ($statusCode >= 200 && $statusCode < 300) {
                    $curlCommand = 'curl -X POST "' . $receiverUrl . '" \\' . "\n"
                                 . '  -H "Content-Type: application/json" \\' . "\n"
                                 . '  -H "X-API-Key: ' . $apiKey . '" \\' . "\n"
                                 . '  -d \'' . $jsonBody . "'";
                }
            }
        }

        $responseTime = round((microtime(true) - $start) * 1000);
    }
}

// Endpoint URL for display
$scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
               ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$dir         = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$endpointUrl = htmlspecialchars($scheme . '://' . $host . $dir . '/receiver.php', ENT_QUOTES, 'UTF-8');

// Preserve form values
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

// Status badge colour
$badgeClass = 'secondary';
if ($statusCode) {
    if ($statusCode >= 200 && $statusCode < 300)     $badgeClass = 'success';
    elseif ($statusCode >= 400 && $statusCode < 500) $badgeClass = 'warning';
    elseif ($statusCode >= 500)                       $badgeClass = 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Portal &mdash; Submission Receiver</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --dark-bg: #1a1e2e;
            --code-bg: #272c3f;
            --accent:  #4f5de4;
        }

        body { background: #f4f6f9; font-size: .9rem; }

        /* ── Header ── */
        .page-header {
            background: var(--dark-bg);
            color: #fff;
            padding: 1.1rem 2rem;
            margin-bottom: 1.75rem;
        }
        .page-header h1 { font-size: 1.2rem; margin: 0; font-weight: 600; letter-spacing: .4px; }
        .page-header .subtitle { font-size: .75rem; opacity: .55; }

        /* ── Cards ── */
        .card { border: none; border-radius: .55rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .card-header { background: #fff; border-bottom: 1px solid #eaeaea; font-weight: 600; font-size: .85rem; padding: .75rem 1.25rem; }

        /* ── Form ── */
        .form-label { font-size: .82rem; font-weight: 600; color: #444; }
        .mono { font-family: 'Courier New', monospace; font-size: .8rem; }

        /* ── Endpoint bar ── */
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

        /* ── Result box ── */
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

        /* ── cURL preview ── */
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

        /* ── Schema table ── */
        .schema-wrap { overflow-x: auto; }
        .schema-table { font-size: .78rem; min-width: 560px; }
        .schema-table th { background: #f8f9fa; font-size: .74rem; text-transform: uppercase; letter-spacing: .4px; color: #888; }
        .schema-table td { vertical-align: middle; }
        .badge-req { background: #e8f0fe; color: #3b4bcf; font-size: .68rem; font-weight: 600; padding: .2rem .45rem; border-radius: .25rem; }
        .badge-opt { background: #f0f0f0; color: #888; font-size: .68rem; font-weight: 600; padding: .2rem .45rem; border-radius: .25rem; }
        .badge-multi { background: #fff3cd; color: #856404; font-size: .68rem; font-weight: 600; padding: .2rem .45rem; border-radius: .25rem; }

        /* ── Send button ── */
        .btn-send { background: var(--accent); border: none; font-weight: 600; }
        .btn-send:hover  { background: #3b4bcf; }
        .btn-send:active { transform: scale(.98); }

        .meta-bar { font-size: .76rem; color: #666; }
    </style>
</head>
<body>

<div class="page-header">
    <h1>API Portal <span class="subtitle ms-2">/ Submission Receiver</span></h1>
    <div class="subtitle mt-1">POST &rarr; receiver.php &mdash; Workers' Comp / Legal Document Submission</div>
</div>

<div class="container-fluid px-4 pb-5">
    <div class="row g-4">

        <!-- ═══ LEFT: Request Form ═══════════════════════════════════════════ -->
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
                        <div class="alert alert-danger py-2 fs-sm"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></div>
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
                                <span id="jsonStatus" class="fw-normal ms-2"></span>
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

            <!-- cURL Preview -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    cURL Preview
                    <button class="btn btn-sm btn-outline-secondary" id="copyBtn" type="button">Copy</button>
                </div>
                <div class="card-body p-0">
                    <div class="curl-preview" id="curlPreview"></div>
                </div>
            </div>

        </div>

        <!-- ═══ RIGHT: Response + Schema ═════════════════════════════════════ -->
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
                    <?php if ($formError): ?>
                        <div class="result-box is-error"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php elseif ($result !== null): ?>
                        <div class="result-box <?= ($statusCode >= 400) ? 'is-error' : '' ?>"><?= htmlspecialchars($result, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php else: ?>
                        <div class="result-box is-empty">// Response will appear here after you send a request.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- cURL Equivalent (shown after successful submission) -->
            <?php if ($curlCommand): ?>
            <div class="card border-success" style="border-width:1px!important">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#f0fdf4;border-bottom:1px solid #bbf7d0">
                    <span class="text-success fw-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1 mb-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
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
                        <thead>
                            <tr>
                                <th class="ps-3" style="width:30%">Field</th>
                                <th style="width:12%">Type</th>
                                <th style="width:12%"></th>
                                <th class="pe-3">Description / Rules</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-light"><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">CASE INFORMATION</td></tr>
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

                            <tr class="table-light"><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">PARTIES</td></tr>
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

                            <tr class="table-light"><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">PATIENT</td></tr>
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
                                    <div class="mb-1 text-muted" style="font-size:.74rem">Accepts flexible aliases — normalized &amp; resolved server-side. Canonical values:</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <code>medical</code>
                                        <code>billing</code>
                                        <code>xray</code> <span class="text-muted" style="font-size:.72rem">/ mri / imaging / films / radiology</span>
                                        <code>claimfile</code> <span class="text-muted" style="font-size:.72rem">/ claim</span>
                                        <code>employmentandpayroll</code>
                                        <code>payroll</code>
                                        <code>employment</code>
                                        <code>wcic</code> <span class="text-muted" style="font-size:.72rem">/ wcicinfo / defendant</span>
                                        <code>nonprivileged</code> <span class="text-muted" style="font-size:.72rem">/ nonpriv</span>
                                        <code>pharmacy</code> <span class="text-muted" style="font-size:.72rem">/ prescription / rx</span>
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

                            <tr class="table-light"><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">ATTACHMENTS</td></tr>
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

<script>
    const endpoint = <?= json_encode($scheme . '://' . $host . $dir . '/receiver.php') ?>;
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

    document.getElementById('copyBtn').addEventListener('click', function () {
        const cmd = `curl -X POST "${endpoint}" \\\n  -H "Content-Type: application/json" \\\n  -H "X-API-Key: ${keyEl.value}" \\\n  -d '${bodyEl.value}'`;
        navigator.clipboard.writeText(cmd).then(() => {
            this.textContent = 'Copied!';
            setTimeout(() => this.textContent = 'Copy', 1500);
        });
    });

    updatePreview();
    validateJson();

    // Copy the cURL result box (rendered server-side after success)
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
</script>

</body>
</html>
