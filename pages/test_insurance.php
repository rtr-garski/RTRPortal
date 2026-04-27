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

    <!-- Search Form -->
    <div class="row g-4 pb-3">
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
    </div>

    <!-- Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" id="insResultsCard">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Results</h4>
                    <span id="insMatchCount" class="badge bg-secondary d-none"></span>
                </div>

                <div class="card-header border-light justify-content-between">
                    <div class="d-flex gap-2">
                        <div class="app-search">
                            <input type="search" id="insMatchFilter" class="form-control" placeholder="Filter results..." />
                            <i class="ti ti-search app-search-icon text-muted"></i>
                        </div>
                    </div>
                    <div>
                        <select id="insMatchPerPage" class="form-select form-control my-1 my-md-0">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom table-centered table-hover w-100 mb-0">
                        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                            <tr class="text-uppercase fs-xxs">
                                <th class="ps-3" style="width:8%">Match</th>
                                <th style="width:34%">Name</th>
                                <th style="width:28%">Address</th>
                                <th style="width:22%">City / State / ZIP</th>
                                <th class="text-end pe-3" style="width:8%">ID</th>
                            </tr>
                        </thead>
                        <tbody id="insMatchTbody">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Enter search fields and click Search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="insPaginationInfo" class="text-muted" style="font-size:.82rem"></div>
                        <ul id="insPagination" class="pagination pagination-sm mb-0"></ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
