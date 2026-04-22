<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../apiportal/functions2.php';

$scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
               ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$dir         = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$endpointUrl = htmlspecialchars($scheme . '://' . $host . $dir . '/apiportal/receiver2.php', ENT_QUOTES, 'UTF-8');
$defaultKey  = htmlspecialchars(API_TOKEN, ENT_QUOTES, 'UTF-8');
?>
<style>
    :root {
        --dark-bg: #1a1e2e;
        --code-bg: #272c3f;
        --accent:  #4f5de4;
    }
    .mono { font-family: 'Courier New', monospace; font-size: .8rem; }
    .endpoint-bar {
        background: var(--code-bg); color: #7ec8e3; border-radius: .4rem;
        padding: .45rem .85rem; font-family: 'Courier New', monospace;
        font-size: .76rem; word-break: break-all;
    }
    .endpoint-bar .method { color: #ffcb6b; font-weight: 700; margin-right: .5rem; }
    .result-box {
        background: var(--dark-bg); color: #a8d8a8; border-radius: .5rem;
        padding: 1.1rem 1.25rem; font-family: 'Courier New', monospace;
        font-size: .78rem; white-space: pre-wrap; word-break: break-all;
        min-height: 220px; max-height: 520px; overflow-y: auto;
    }
    .result-box.is-error { color: #f08080; }
    .result-box.is-empty { color: #555; }
    .curl-preview {
        background: var(--code-bg); color: #cdd3e0; border-radius: .5rem;
        padding: .9rem 1.1rem; font-family: 'Courier New', monospace;
        font-size: .73rem; white-space: pre-wrap; word-break: break-all;
    }
    .curl-preview .kw  { color: #7ec8e3; }
    .curl-preview .str { color: #c3e88d; }
    .curl-preview .opt { color: #ffcb6b; }
    .schema-wrap { overflow-x: auto; }
    .schema-table { font-size: .78rem; min-width: 560px; }
    .schema-table td { vertical-align: middle; }
    .btn-send { background: var(--accent); border: none; font-weight: 600; }
    .btn-send:hover  { background: #3b4bcf; }
    .btn-send:active { transform: scale(.98); }
    .meta-bar { font-size: .76rem; color: #666; }
    .success-curl-card { border: 1px solid #bbf7d0 !important; }
    .success-curl-header { background: #f0fdf4; border-bottom: 1px solid #bbf7d0; }
</style>

<div class="container-fluid" id="api-portal-root"
     data-endpoint="<?= $endpointUrl ?>"
     data-default-key="<?= $defaultKey ?>">

    <div class="page-title-head d-flex align-items-center">
        <div class="flex-grow-1">
            <h4 class="page-main-title m-0">Submission Receiver</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">API</a></li>
                <li class="breadcrumb-item active">Submission Receiver</li>
            </ol>
        </div>
    </div>

    <div class="row g-4 pb-4">

        <!-- LEFT: Request Form -->
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

                    <div id="apiFormError" class="d-none alert alert-danger py-2" style="font-size:.82rem"></div>

                    <form id="apiForm">

                        <div class="mb-3">
                            <label class="form-label" for="api_key">
                                API Key
                                <span class="text-muted fw-normal">(X-API-Key header)</span>
                            </label>
                            <input type="text" class="form-control mono" id="api_key" name="api_key"
                                   value="<?= $defaultKey ?>" required autocomplete="off">
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="json_body">
                                JSON Body
                                <span id="jsonStatus" class="fw-normal ms-2" style="font-size:.78rem"></span>
                            </label>
                            <textarea class="form-control mono" id="json_body" name="json_body"
                                      rows="22" required>{
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
}</textarea>
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

        <!-- RIGHT: Response + cURL result + Schema -->
        <div class="col-xl-7 col-lg-6 d-flex flex-column gap-3">

            <!-- Response -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Response</span>
                    <span class="d-flex align-items-center gap-3 meta-bar">
                        <span id="statusBadge"></span>
                        <span id="timeBadge"></span>
                    </span>
                </div>
                <div class="card-body">
                    <div class="result-box is-empty" id="resultBox">// Response will appear here after you send a request.</div>
                </div>
            </div>

            <!-- cURL Equivalent — shown after a successful 2xx response -->
            <div class="card success-curl-card d-none" id="curlResultCard">
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
                    <div class="curl-preview" id="curlResultBox"></div>
                </div>
            </div>

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
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted"><code>IMR</code>, <code>SIBTF</code>, <code>Special Notice of Lawsuit</code>, <code>Trial Depo Subpoena for WCAB</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>case_no</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted">WCAB Case No / ADJ number</td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>doi_start</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted">Date of Injury start — <code>YYYY-MM-DD</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>doi_end</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-info">optional</span></td>
                                <td class="pe-3 text-muted">Date of Injury end — <code>YYYY-MM-DD</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>court_venue</code></td>
                                <td>object</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted"><code>name</code>, <code>address</code>, <code>city</code>, <code>state</code>, <code>phone</code> — all required</td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>letter_of_rep_date</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted">Letter of Representation date — <code>YYYY-MM-DD</code></td>
                            </tr>
                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">PARTIES</td></tr>
                            <tr>
                                <td class="ps-3"><code>insurance_carriers</code></td>
                                <td>array</td>
                                <td><span class="badge badge-soft-info">optional</span> <span class="badge badge-soft-primary">multiple</span></td>
                                <td class="pe-3 text-muted">Each: <code>name*</code>, <code>address</code>, <code>city</code>, <code>state</code>, <code>zip</code>, <code>phone</code>, <code>adjuster_name</code>, <code>adjuster_phone</code>, <code>adjuster_fax</code>, <code>adjuster_email</code>, <code>claim_no</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>opposing_counsel</code></td>
                                <td>array</td>
                                <td><span class="badge badge-soft-info">optional</span> <span class="badge badge-soft-primary">multiple</span></td>
                                <td class="pe-3 text-muted">Each: <code>name*</code>, <code>address</code>, <code>city</code>, <code>state</code>, <code>zip</code>, <code>phone</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>employer_name</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted">Name of employer</td>
                            </tr>
                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">PATIENT</td></tr>
                            <tr>
                                <td class="ps-3"><code>patient.name</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted">Patient full name</td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>patient.dob</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted">Date of birth — <code>YYYY-MM-DD</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>patient.ssn</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-info">optional</span></td>
                                <td class="pe-3 text-muted">Social security number</td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>patient.street / city / state / zip</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-info">optional</span></td>
                                <td class="pe-3 text-muted">Patient address fields</td>
                            </tr>
                            <tr><th colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">RECORDS LOCATIONS</th></tr>
                            <tr>
                                <td class="ps-3"><code>records_locations</code></td>
                                <td>array</td>
                                <td><span class="badge badge-soft-danger">required</span> <span class="badge badge-soft-primary">multiple</span></td>
                                <td class="pe-3 text-muted">At least one entry required</td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>&nbsp;&nbsp;.priority</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-info">optional</span></td>
                                <td class="pe-3 text-muted"><code>standard</code> (default) or <code>rush</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3 align-top"><code>&nbsp;&nbsp;.record_type</code></td>
                                <td class="align-top">string</td>
                                <td class="align-top"><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 align-top">
                                    <div class="text-muted mb-1" style="font-size:.74rem">Flexible aliases — normalized server-side:</div>
                                    <div class="d-flex flex-wrap gap-1" style="font-size:.75rem">
                                        <code>medical</code> <code>billing</code>
                                        <code>xray</code><span class="text-muted">/mri/imaging/films/radiology</span>
                                        <code>claimfile</code> <code>employmentandpayroll</code>
                                        <code>payroll</code> <code>employment</code> <code>wcic</code>
                                        <code>nonprivileged</code>
                                        <code>pharmacy</code><span class="text-muted">/prescription/rx</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>&nbsp;&nbsp;.date_needed</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted"><code>YYYY-MM-DD</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>&nbsp;&nbsp;.location</code></td>
                                <td>object</td>
                                <td><span class="badge badge-soft-danger">required</span></td>
                                <td class="pe-3 text-muted"><code>name*</code>, <code>address*</code>, <code>phone*</code></td>
                            </tr>
                            <tr>
                                <td class="ps-3"><code>&nbsp;&nbsp;.special_instruction</code></td>
                                <td>string</td>
                                <td><span class="badge badge-soft-info">optional</span></td>
                                <td class="pe-3 text-muted">Free-text instructions</td>
                            </tr>
                            <tr><td colspan="4" class="ps-3 fw-semibold text-muted" style="font-size:.72rem">ATTACHMENTS</td></tr>
                            <tr>
                                <td class="ps-3"><code>attachments</code></td>
                                <td>array</td>
                                <td><span class="badge badge-soft-info">optional</span> <span class="badge badge-soft-primary">multiple</span></td>
                                <td class="pe-3 text-muted">Each: <code>filename*</code>, <code>mime_type*</code>, <code>data*</code> (base64). Max 10 MB each. Allowed: PDF, JPEG, PNG, TIFF, DOC, DOCX</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
