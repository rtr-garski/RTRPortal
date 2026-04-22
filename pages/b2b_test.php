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
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload Test (RTR-ClientUpload bucket)</h5>
                </div>
                <div class="card-body">
                    <div id="b2b-flash"></div>

                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="text" id="b2b-token" class="form-control font-monospace" placeholder="Paste an active API token">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order ID</label>
                        <input type="text" id="b2b-order-id" class="form-control" placeholder="e.g. ORD123">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UUID <span class="text-muted">(optional — auto-generated if blank)</span></label>
                        <input type="text" id="b2b-uuid" class="form-control font-monospace" placeholder="Leave blank to auto-generate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" id="b2b-file" class="form-control">
                    </div>

                    <button id="b2b-upload-btn" class="btn btn-primary w-100">
                        <i class="ti ti-cloud-upload me-1"></i> Get Presigned URL &amp; Upload
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
