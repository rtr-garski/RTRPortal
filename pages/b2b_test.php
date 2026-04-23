<?php
require "../config/session.php";
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">B2 Client Upload — Test</h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <!-- Step 1: Generate Presigned URL -->
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="badge bg-primary">1</span>
                    <h5 class="card-title mb-0">Generate Presigned URL</h5>
                </div>
                <div class="card-body">
                    <div id="b2b-gen-flash"></div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">API Token<span class="text-danger">*</span></label>
                            <input type="text" id="b2b-token" class="form-control font-monospace"
                                   placeholder="Paste an active API token">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Order ID <span class="text-danger">*</span></label>
                            <input type="text" id="b2b-order-id" class="form-control" placeholder="e.g. ORD123">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Filename <span class="text-muted fw-normal">(optional — auto-generated if blank)</span></label>
                            <input type="text" id="b2b-filename" class="form-control font-monospace" placeholder="e.g. garry.pdf">
                        </div>
                    </div>

                    <button id="b2b-gen-btn" class="btn btn-primary">
                        <i class="ti ti-link me-1"></i> Generate Presigned URL
                    </button>

                    <!-- Inline result -->
                    <div id="b2b-gen-result" class="d-none mt-3">
                        <div class="alert alert-success mb-2 py-2">
                            <i class="ti ti-circle-check me-1"></i>
                            Presigned URL generated — fields below have been populated.
                            <span class="float-end text-muted" style="font-size:.8rem">Expires in <strong id="gen-expires"></strong>s</span>
                        </div>
                        <table class="table table-sm table-borderless mb-0" style="font-size:.82rem">
                            <tbody>
                                <tr><th style="width:110px">Folder</th><td id="gen-folder" class="font-monospace text-break"></td></tr>
                                <tr>
                                    <th>Presigned URL</th>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="gen-url-display" class="form-control font-monospace" readonly>
                                            <button class="btn btn-outline-secondary" id="b2b-copy-url" type="button" title="Copy URL">
                                                <i class="ti ti-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- cURL Test Card — shown after presigned URL is generated -->
            <div id="b2b-curl-card" class="card d-none">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark">cURL</span>
                        <h5 class="card-title mb-0">Test via Terminal</h5>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="b2b-copy-curl" type="button">Copy</button>
                </div>
                <div class="card-body p-0">
                    <pre id="b2b-curl-cmd" class="mb-0 p-3" style="font-size:.78rem;white-space:pre-wrap;word-break:break-all;background:#1a1e2e;color:#c3e88d;border-radius:0 0 .375rem .375rem"></pre>
                </div>
            </div>

            <!-- Step 2: Upload File -->
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">2</span>
                    <h5 class="card-title mb-0">Upload File</h5>
                </div>
                <div class="card-body">
                    <div id="b2b-flash"></div>

                    <div class="mb-3">
                        <label class="form-label">Presigned URL <span class="text-danger">*</span></label>
                        <input type="text" id="b2b-presigned-url" class="form-control font-monospace"
                               placeholder="Generate above or paste a presigned URL manually">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" id="b2b-file" class="form-control">
                    </div>

                    <button id="b2b-upload-btn" class="btn btn-success w-100">
                        <i class="ti ti-cloud-upload me-1"></i> Upload to B2
                    </button>

                    <!-- Progress -->
                    <div id="b2b-progress-wrap" class="mt-3 d-none">
                        <div class="d-flex justify-content-between mb-1">
                            <small id="b2b-progress-label">Uploading...</small>
                            <small id="b2b-progress-pct">0%</small>
                        </div>
                        <div class="progress">
                            <div id="b2b-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result card — hidden until upload completes -->
            <div id="b2b-result-card" class="card d-none">
                <div class="card-header">
                    <h5 class="card-title mb-0 text-success"><i class="ti ti-circle-check me-1"></i>Upload Successful</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr><th style="width:140px">Bucket</th><td>RTR-ClientUpload</td></tr>
                            <tr><th>Folder</th><td id="res-folder" class="font-monospace"></td></tr>
                            <tr><th>Filename</th><td id="res-filename" class="font-monospace"></td></tr>
                            <tr><th>B2 Path</th><td id="res-b2path" class="font-monospace text-break"></td></tr>
                            <tr><th>File ID</th><td id="res-file-id" class="font-monospace text-break"></td></tr>
                            <tr><th>ETag (MD5)</th><td id="res-etag" class="font-monospace"></td></tr>
                            <tr><th>Presigned URL</th><td><a id="res-presigned" href="#" target="_blank" class="text-truncate d-block" style="max-width:400px">View</a></td></tr>
                        </tbody>
                    </table>
                    <div class="mt-3 d-flex gap-2">
                        <a id="res-download-public" href="#" target="_blank" class="btn btn-outline-secondary">
                            <i class="ti ti-world me-1"></i> Public Download
                        </a>
                        <a id="res-download-secure" href="#" target="_blank" class="btn btn-outline-primary">
                            <i class="ti ti-lock me-1"></i> Secure Download <small class="ms-1 opacity-75">(1hr)</small>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
