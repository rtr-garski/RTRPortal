<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
?>
<div class="container-fluid">

    <div class="page-title-head d-flex align-items-center">
        <div class="flex-grow-1">
            <h4 class="page-main-title m-0">Insurance Match</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                <li class="breadcrumb-item active">Insurance Match</li>
            </ol>
        </div>
    </div>

    <div class="row g-4 pb-4">

        <!-- Search Form -->
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">Search</div>
                <div class="card-body">
                    <div id="insMatchAlert" class="d-none alert py-2" style="font-size:.82rem"></div>
                    <form id="insMatchForm">
                        <div class="mb-3">
                            <label class="form-label" for="ins_name">Name</label>
                            <input type="text" class="form-control" id="ins_name" placeholder="e.g. Stanislaus Cardiology Nuclear, Inc.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="ins_address">Address</label>
                            <input type="text" class="form-control" id="ins_address" placeholder="e.g. 1234 Main St">
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="ins_csz">City, State ZIP</label>
                            <input type="text" class="form-control" id="ins_csz" placeholder="e.g. Modesto, CA 95350">
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="insMatchBtn">
                            <span id="insMatchSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Results</span>
                    <span id="insMatchCount" class="badge bg-secondary d-none"></span>
                </div>
                <div class="card-body p-0">
                    <div id="insMatchPlaceholder" class="text-muted text-center py-5" style="font-size:.875rem">
                        Enter search fields and click Search.
                    </div>
                    <div id="insMatchTableWrap" class="d-none table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3" style="width:8%">Match</th>
                                    <th style="width:32%">Name</th>
                                    <th style="width:28%">Address</th>
                                    <th style="width:24%">City / State / ZIP</th>
                                    <th class="pe-3 text-end" style="width:8%">ID</th>
                                </tr>
                            </thead>
                            <tbody id="insMatchTbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
