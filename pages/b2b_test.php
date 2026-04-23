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
                            <label class="form-label">UUID <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" id="b2b-uuid" class="form-control font-monospace" placeholder="Auto-generated if blank">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Extension <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" id="b2b-extension" class="form-control" placeholder="e.g. pdf, jpg (default: pdf)">
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
                                <tr><th>Filename</th><td id="gen-filename" class="font-monospace text-break"></td></tr>
                                <tr><th>B2 Path</th><td id="gen-b2path" class="font-monospace text-break"></td></tr>
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
                            <tr><th>Presigned URL</th><td><a id="res-presigned" href="#" target="_blank" class="text-truncate d-block" style="max-width:400px">View</a></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
