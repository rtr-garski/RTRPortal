<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/access.php';
require_page_access('theme_editor', $pdo2);
?>

<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Settings</a></li>
                        <li class="breadcrumb-item active">Theme Editor</li>
                    </ol>
                </div>
                <h4 class="page-title">Theme Editor</h4>
            </div>
        </div>
    </div>

    <!-- Quick Theme Presets -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-light">
                    <h5 class="card-title mb-0"><i class="ti ti-sparkles me-1"></i> Quick Themes</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3" id="themePresets"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Color Pickers -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header border-light">
                    <h5 class="card-title mb-0"><i class="ti ti-color-picker me-1"></i> Custom Colors</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="colorPickerGrid">

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Page Background</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorPageBg">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textPageBg" maxlength="7" placeholder="#313a46">
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Font Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorFontColor">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textFontColor" maxlength="7" placeholder="#aab8c5">
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Card Background</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorCardBg">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textCardBg" maxlength="7" placeholder="#404954">
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Topbar Background</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorTopbarBg">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textTopbarBg" maxlength="7" placeholder="#3c4655">
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Table Header Background</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorThBg">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textThBg" maxlength="7" placeholder="#404954">
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Table Header Text</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorThText">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textThText" maxlength="7" placeholder="#8391a2">
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold fs-13">Sidebar Background</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color flex-shrink-0" id="colorSidebarBg">
                                <input type="text" class="form-control form-control-sm font-monospace" id="textSidebarBg" maxlength="7" placeholder="#313a46">
                            </div>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4 pt-2 border-top border-light">
                        <button id="applyThemeBtn" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Theme
                        </button>
                        <button id="resetThemeBtn" class="btn btn-outline-secondary">
                            <i class="ti ti-refresh me-1"></i> Reset Default
                        </button>
                    </div>

                    <div id="themeSaveFlash" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header border-light">
                    <h5 class="card-title mb-0"><i class="ti ti-eye me-1"></i> Live Preview</h5>
                    <small class="text-muted">Changes apply to the whole portal in real time</small>
                </div>
                <div class="card-body">
                    <p class="mb-3">This is a sample <strong>paragraph</strong> showing your selected <a href="#">font color</a> and background.</p>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>Record ID</th>
                                    <th>Patient Name</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>#10041</strong></td>
                                    <td>John Smith</td>
                                    <td>Records Retrieval</td>
                                    <td><span class="badge badge-soft-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td><strong>#10042</strong></td>
                                    <td>Jane Doe</td>
                                    <td>Medical Summary</td>
                                    <td><span class="badge badge-soft-info">In Review</span></td>
                                </tr>
                                <tr>
                                    <td><strong>#10043</strong></td>
                                    <td>Robert Lee</td>
                                    <td>Records Retrieval</td>
                                    <td><span class="badge badge-soft-secondary">New Request</span></td>
                                </tr>
                                <tr>
                                    <td><strong>#10044</strong></td>
                                    <td>Maria Garcia</td>
                                    <td>Imaging</td>
                                    <td><span class="badge badge-soft-danger">Canceled</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary btn-sm">Primary</button>
                        <button class="btn btn-secondary btn-sm">Secondary</button>
                        <button class="btn btn-success btn-sm">Success</button>
                        <button class="btn btn-danger btn-sm">Danger</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- end row -->

</div>
<!-- container -->
