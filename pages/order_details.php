<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
require_once __DIR__ . '/../config/db.php';

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$order_id) {
    http_response_code(400);
    echo '<div class="container-fluid"><div class="alert alert-danger">No order ID provided.</div></div>';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM API_Input_Orders WHERE __kp_API_Input_Order_ID = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    echo '<div class="container-fluid"><div class="alert alert-danger">Order not found.</div></div>';
    exit;
}

$stmt2 = $pdo->prepare("SELECT * FROM API_Input_Order_Locations WHERE _kf_API_Input_Order_ID = ?");
$stmt2->execute([$order_id]);
$locations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $pdo->prepare("SELECT * FROM API_Input_Insurance_Carriers WHERE _kf_API_Input_Order_ID = ?");
$stmt3->execute([$order_id]);
$insurance = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$order['locations'] = $locations;
$order['insurance'] = $insurance;

$recTypeOptions = [
    'Medical',
    'Billing',
    'X-Ray/MRI Images/Films',
    'Claim File',
    'Employment & Payroll',
    'Payroll',
    'Employment',
    'WCIC Information for Defendant/Employee',
    'Non-Privileged',
    'Pharmacy Prescription',
];

$fmPayload = ['Order_ID' => $order['__kp_API_Input_Order_ID'], 'Service_Type' => $order['_kf_Service_Type_ID_Str'], 'Service_Subtype' => $order['_kf_Service_Subtype_ID_Str'], 'LOR_Date' => $order['LOR_Date'], 'Employer_Name' => $order['Employer_Name'], 'Pat_Name' => $order['Pat_Name'], 'Pat_AKA' => $order['Pat_AKA'], 'Pat_DOB' => $order['Pat_DOB'], 'Pat_SSN' => $order['PAT_SSN'], 'Pat_Address_Street' => $order['Pat_Address_Street'], 'Pat_Address_City' => $order['Pat_Address_City'], 'Pat_Address_State' => $order['Pat_Address_State'], 'Pat_Address_Zip' => $order['Pat_Address_Zip']];
if (!empty($locations)) {
    $loc = $locations[0];
    $fmPayload += ['Loc_Name' => $loc['Loc_Name'], 'Loc_Address_Street' => $loc['Loc_Address_Street'], 'Loc_Address_City' => $loc['Loc_Address_City'], 'Loc_Address_State' => $loc['Loc_Address_State'], 'Loc_Address_Zip' => $loc['Loc_Address_Zip'], 'Loc_Phone' => $loc['Loc_Address_Phone'], 'Loc_Fax' => $loc['Loc_Address_Phone_Fax'], 'Rec_Type' => $loc['Rec_Type'], 'Rec_Dates_Needed' => $loc['Rec_Dates_Needed'], 'Special_Instructions' => $loc['Special_Instructions']];
}
if (!empty($insurance)) {
    $ins = $insurance[0];
    $fmPayload += ['Ins_Name' => $ins['Ins_Name'], 'Ins_Address_Street' => $ins['Ins_Address_Street'], 'Ins_Address_City' => $ins['Ins_Address_City'], 'Ins_Address_State' => $ins['Ins_Address_State'], 'Ins_Address_Zip' => $ins['Ins_Address_Zip'], 'Ins_Phone' => $ins['Ins_Address_Phone'], 'Ins_Fax' => $ins['Ins_Address_Phone_Fax'], 'Adj_Claim_ID' => $ins['Adj_Claim_ID'], 'Adj_Name' => $ins['Adj_Name'], 'Adj_Phone' => $ins['Adj_Phone'], 'Adj_Fax' => $ins['Adj_Phone_Fax'], 'Adj_Email' => $ins['Adj_Email']];
}
?>

<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Reports</a></li>
                        <li class="breadcrumb-item"><a href="#" class="order-entry-nav">Orders</a></li>
                        <li class="breadcrumb-item active">Order Details</li>
                    </ol>
                </div>
                <h4 class="page-title">Order Details</h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="row">
                <!-- Main Details -->
                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-header align-items-start p-4">
                            <div>
                                <h3 class="mb-1 d-flex fs-xl align-items-center"><a href="#" class="link-reset text-decoration-none" id="order-detail-reload" data-order-id="<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?>">Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></a></h3>
                                <p class="text-muted mb-3"><i class="ti ti-calendar"></i> <?= date('d M, Y', strtotime($order['API_Input_Timestamp'])) ?> <small class="text-muted"><?= date('g:i A', strtotime($order['API_Input_Timestamp'])) ?></small></p>
                                <span class="badge badge-soft-info fs-xxs badge-label"><i class="ti ti-inbox fs-sm align-middle"></i> Order Received</span>
                            </div>
                            <div class="ms-auto d-flex gap-2">
                                <a href="javascript: void(0);" class="btn btn-success" id="releaseToSystemBtn"><i class="ti ti-send me-1"></i> Release the Kraken</a>
                                <a href="javascript: void(0);" class="btn btn-primary" id="releaseToApiBtn"><i class="ti ti-api me-1"></i> Release to API-RH</a>
                            </div>
                        </div>
                        <div class="card-body px-4">
                            <h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Case Information</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Service Type</label>
                                    <input type="text" class="form-control is-valid" value="<?= htmlspecialchars($order['_kf_Service_Type_ID_Str']) ?>" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Service Subtype</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['_kf_Service_Subtype_ID_Str']) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">LOR Date</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['LOR_Date']) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Employer</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['Employer_Name']) ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end card -->

                    <!-- Insurance Carriers -->
                    <div class="card">
                        <div class="card-header justify-content-between align-items-center">
                            <h4 class="card-title">Insurance Carriers</h4>
                            <div class="card-action">
                                <a href="#!" class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></a>
                            </div>
                        </div>
                        <div class="card-body px-4">
                            <?php foreach ($order['insurance'] as $i => $ins): ?>
                                <?php if ($i > 0): ?><hr class="my-3"><?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Carrier Name</label>
                                        <?php $insValid = rand(0,1); ?>
                                        <input type="text" class="form-control change-info-input <?= $insValid ? 'is-valid' : 'is-invalid' ?>" value="<?= htmlspecialchars($ins['Ins_Name']) ?>"
                                            data-address="<?= htmlspecialchars($ins['Ins_Address_Street'] . ', ' . $ins['Ins_Address_City'] . ', ' . $ins['Ins_Address_State'] . ' ' . $ins['Ins_Address_Zip']) ?>"
                                            data-phone="<?= htmlspecialchars($ins['Ins_Address_Phone']) ?>"
                                            data-fax="<?= htmlspecialchars($ins['Ins_Address_Phone_Fax']) ?>">
                                        <div class="valid-feedback">Carrier verified.</div>
                                        <div class="invalid-feedback">Please select the correct carrier.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Address</label>
                                        <p class="mb-0"><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($ins['Ins_Address_Street']) ?>, <?= htmlspecialchars($ins['Ins_Address_City']) ?>, <?= htmlspecialchars($ins['Ins_Address_State']) ?> <?= htmlspecialchars($ins['Ins_Address_Zip']) ?></p>
                                        <p class="mb-0 text-muted"><i class="ti ti-phone me-1"></i><?= htmlspecialchars($ins['Ins_Address_Phone']) ?> &nbsp; <i class="ti ti-printer me-1"></i><?= htmlspecialchars($ins['Ins_Address_Phone_Fax']) ?></p>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Claim No.</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($ins['Adj_Claim_ID']) ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Adjuster Name</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($ins['Adj_Name']) ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Adjuster Phone</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($ins['Adj_Phone']) ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Adjuster Fax</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($ins['Adj_Phone_Fax']) ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Adjuster Email</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($ins['Adj_Email']) ?>" readonly>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- end card -->

                    <!-- Records Locations -->
                    <div class="card">
                        <div class="card-header justify-content-between align-items-center">
                            <h4 class="card-title">Records Locations</h4>
                            <div class="card-action">
                                <a href="#!" class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></a>
                            </div>
                        </div>
                        <div class="card-body px-4">
                            <?php foreach ($order['locations'] as $i => $loc): ?>
                                <?php if ($i > 0): ?><hr class="my-3"><?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Record Type</label>
                                        <select class="form-select">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($recTypeOptions as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>" <?= $loc['Rec_Type'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Dates Needed</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($loc['Rec_Dates_Needed']) ?>" readonly>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Location Name</label>
                                        <?php $locValid = rand(0,1); ?>
                                        <input type="text" class="form-control change-info-input <?= $locValid ? 'is-valid' : 'is-invalid' ?>" value="<?= htmlspecialchars($loc['Loc_Name']) ?>"
                                            data-address="<?= htmlspecialchars($loc['Loc_Address_Street'] . ', ' . $loc['Loc_Address_City'] . ', ' . $loc['Loc_Address_State'] . ' ' . $loc['Loc_Address_Zip']) ?>"
                                            data-phone="<?= htmlspecialchars($loc['Loc_Address_Phone']) ?>"
                                            data-fax="<?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?>">
                                        <p class="mb-0"><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($loc['Loc_Address_Street']) ?>, <?= htmlspecialchars($loc['Loc_Address_City']) ?>, <?= htmlspecialchars($loc['Loc_Address_State']) ?> <?= htmlspecialchars($loc['Loc_Address_Zip']) ?></p>
                                        <p class="mb-0 text-muted"><i class="ti ti-phone me-1"></i><?= htmlspecialchars($loc['Loc_Address_Phone']) ?> &nbsp; <i class="ti ti-printer me-1"></i><?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?></p>
                                        <div class="valid-feedback">Location verified.</div>
                                        <div class="invalid-feedback">Please select the correct location.</div>
                                    </div>
                                    <div class="col-md-10">
                                        <label class="form-label fw-semibold">Special Instructions</label>
                                        <p class="mb-0"><?= htmlspecialchars($loc['Special_Instructions']) ?>Lorem Ipsum is dummy or placeholder text commonly used in graphic design, publishing, and web development to fill spaces where content will eventually appear.</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- end card -->

                    <!-- Records Locations 2 -->
                    <div class="card">
                        <div class="card-header justify-content-between align-items-center">
                            <h4 class="card-title">Records Locations 2 (With Order Entry Validation)</h4>
                            <div class="card-action">
                                <a href="#!" class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></a>
                            </div>
                        </div>
                        <div class="card-body px-4">
                            <?php foreach ($order['locations'] as $i => $loc): ?>
                                <?php if ($i > 0): ?><hr class="my-3"><?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Record Type</label>
                                        <select class="form-select">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($recTypeOptions as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>" <?= $loc['Rec_Type'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Dates Needed</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($loc['Rec_Dates_Needed']) ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Location Name</label>
                                        <?php $locValid2 = rand(0,1); ?>
                                        <input type="text" class="form-control change-info-input <?= $locValid2 ? 'is-valid' : 'is-invalid' ?>" value="<?= htmlspecialchars($loc['Loc_Name']) ?>"
                                            data-address="<?= htmlspecialchars($loc['Loc_Address_Street'] . ', ' . $loc['Loc_Address_City'] . ', ' . $loc['Loc_Address_State'] . ' ' . $loc['Loc_Address_Zip']) ?>"
                                            data-phone="<?= htmlspecialchars($loc['Loc_Address_Phone']) ?>"
                                            data-fax="<?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?>">
                                        <p class="mb-0"><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($loc['Loc_Address_Street']) ?>, <?= htmlspecialchars($loc['Loc_Address_City']) ?>, <?= htmlspecialchars($loc['Loc_Address_State']) ?> <?= htmlspecialchars($loc['Loc_Address_Zip']) ?></p>
                                        <p class="mb-0 text-muted"><i class="ti ti-phone me-1"></i><?= htmlspecialchars($loc['Loc_Address_Phone']) ?> &nbsp; <i class="ti ti-printer me-1"></i><?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?></p>
                                        <div class="valid-feedback">89%</div>
                                        <div class="invalid-feedback">Please select the correct location.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Location Submitted</label>
                                        <p class="mb-0"><i class="ti ti-briefcase me-1"></i><?= htmlspecialchars($loc['Loc_Name']) ?></p>
                                        <p class="mb-0 text-muted"><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($loc['Loc_Address_Street']) ?>, <?= htmlspecialchars($loc['Loc_Address_City']) ?>, <?= htmlspecialchars($loc['Loc_Address_State']) ?> <?= htmlspecialchars($loc['Loc_Address_Zip']) ?></p>
                                        <p class="mb-0 text-muted"><i class="ti ti-phone me-1"></i><?= htmlspecialchars($loc['Loc_Address_Phone']) ?> &nbsp; <i class="ti ti-printer me-1"></i><?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?></p>
                                        <div class="valid-feedback">Location verified.</div>
                                        <div class="invalid-feedback">Please select the correct location.</div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Special Instructions</label>
                                        <p class="mb-0"><?= htmlspecialchars($loc['Special_Instructions']) ?>Lorem Ipsum is dummy or placeholder text commonly used in graphic design, publishing, and web development to fill spaces where content will eventually appear.</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col-xl-9 -->

                <!-- Sidebar -->
                <div class="col-xl-3">
                    <div class="card">
                        <div class="card-header justify-content-between border-dashed">
                            <h4 class="card-title">Patient Details</h4>
                            <a href="#!" class="btn btn-default btn-sm btn-icon rounded-circle"><i class="ti ti-pencil fs-lg"></i></a>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-2">
                                    <div class="avatar-lg rounded-circle bg-info text-white d-flex align-items-center justify-content-center fw-bold"><?= strtoupper(substr($order['Pat_Name'], 0, 1)) ?></div>
                                </div>
                                <div>
                                    <h5 class="mb-1 d-flex align-items-center">
                                        <a href="#!" class="link-reset"><?= htmlspecialchars($order['Pat_Name']) ?></a>
                                    </h5>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($order['Pat_AKA']) ?></p>
                                </div>
                            </div>

                            <ul class="list-unstyled text-muted mb-0">
                                <li class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs avatar-img-size fs-24">
                                            <span class="avatar-title text-bg-light fs-sm rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Date of Birth">
                                                <i class="ti ti-calendar"></i>
                                            </span>
                                        </div>
                                        <h5 class="fs-base mb-0 fw-medium" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Date of Birth"><?= htmlspecialchars($order['Pat_DOB']) ?></h5>
                                    </div>
                                </li>
                                <li class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs avatar-img-size fs-24">
                                            <span class="avatar-title text-bg-light fs-sm rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="SSN">
                                                <i class="ti ti-id"></i>
                                            </span>
                                        </div>
                                        <h5 class="fs-base mb-0 fw-medium" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="SSN"><?= htmlspecialchars($order['PAT_SSN']) ?></h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs avatar-img-size fs-24">
                                            <span class="avatar-title text-bg-light fs-sm rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Address">
                                                <i class="ti ti-map-pin"></i>
                                            </span>
                                        </div>
                                        <h5 class="fs-base mb-0 fw-medium" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Address"><?= htmlspecialchars($order['Pat_Address_Street']) ?> <?= htmlspecialchars($order['Pat_Address_City']) ?>, <?= htmlspecialchars($order['Pat_Address_State']) ?> <?= htmlspecialchars($order['Pat_Address_Zip']) ?></h5>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- end card -->

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Activity</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="timeline">
                                <div class="timeline-item d-flex align-items-stretch">
                                    <div class="timeline-time pe-3 text-muted"></div>
                                    <div class="timeline-dot bg-light"></div>
                                    <div class="timeline-content ps-3 pb-5">
                                        <h5 class="mb-1">Order Delivered</h5>
                                        <p class="mb-1 text-muted">The package is out for delivery and will reach you shortly.</p>
                                        <p class="mb-1 text-muted fs-xxs">Tracking No: <a href="#!" class="link-primary fw-semibold text-decoration-underline">TRK123456789</a></p>
                                        <span class="fw-semibold fs-xxs">By Rodelaine Raro</span>
                                    </div>
                                </div>
                                <div class="timeline-item d-flex align-items-stretch">
                                    <div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="9:00 AM">Apr 14 '26</div>
                                    <div class="timeline-dot bg-success"></div>
                                    <div class="timeline-content ps-3 pb-5">
                                        <h5 class="mb-1">QA Status</h5>
                                        <p class="mb-1 text-muted">Complete Order</p>
                                        <span class="fs-xxs fw-semibold">By QA Agent1</span>
                                    </div>
                                </div>
                                <div class="timeline-item d-flex align-items-stretch">
                                    <div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="3:15 PM">Apr 13 '26</div>
                                    <div class="timeline-dot bg-success"></div>
                                    <div class="timeline-content ps-3 pb-5">
                                        <h5 class="mb-1">Order Copied</h5>
                                    </div>
                                </div>
                                <div class="timeline-item d-flex align-items-stretch">
                                    <div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="6:00 PM">Apr 7 '26</div>
                                    <div class="timeline-dot bg-success"></div>
                                    <div class="timeline-content ps-3 pb-5">
                                        <h5 class="mb-1">Order Served</h5>
                                    </div>
                                </div>
                                <div class="timeline-item d-flex align-items-stretch">
                                    <div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="5:00 PM">Apr 4 '26</div>
                                    <div class="timeline-dot bg-success"></div>
                                    <div class="timeline-content ps-3 pb-5">
                                        <h5 class="mb-1">Order Verified</h5>
                                        <p class="mb-1 text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>
                                        <span class="fw-semibold fs-xxs">By OE Agent1</span>
                                    </div>
                                </div>
                                <div class="timeline-item d-flex align-items-stretch">
                                    <div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="4:00 PM">Apr 4 '26</div>
                                    <div class="timeline-dot bg-success"></div>
                                    <div class="timeline-content ps-3 pb-5">
                                        <h5 class="mb-1">Order Received</h5>
                                        <p class="mb-1 text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col-xl-3 -->
            </div>
            <!-- end row -->
        </div>
        <!-- end col-xxl-10 -->
    </div>

</div>
<!-- container -->

<!-- API-RH Modal -->
<div class="modal fade" id="apiRhModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-api me-2"></i>Release to API-RH &mdash; Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Request Settings -->
                <div class="row g-2 mb-2">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Method</label>
                        <select class="form-select form-select-sm" id="apiRhMethod">
                            <option value="GET">GET</option>
                            <option value="POST" selected>POST</option>
                        </select>
                    </div>
                    <div class="col-md-10">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">URL</label>
                        <input type="text" class="form-control form-control-sm font-monospace" id="apiRhUrl" value="https://api.recordhost.net/v1/deployOrderToFM/?token=7b054c1e-f890-46d0-9a71-f61268f44707">
                    </div>
                </div>

                <hr class="my-3">

                <!-- Payload -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-semibold fs-xs text-uppercase text-muted mb-0">Payload (JSON Body)</label>
                    <span class="text-muted fs-xs">Edit field names to match the API</span>
                </div>
                <textarea class="form-control font-monospace" id="apiRhPayload" rows="12" style="font-size:12px"><?= htmlspecialchars(json_encode($fmPayload, JSON_PRETTY_PRINT)) ?></textarea>

                <!-- Response -->
                <div id="apiRhResponseWrap" class="mt-3" style="display:none">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted mb-0">Response</label>
                        <span class="badge" id="apiRhStatusBadge"></span>
                        <span class="text-muted fs-xs" id="apiRhElapsed"></span>
                    </div>
                    <pre class="p-3 rounded border bg-light" id="apiRhResponseBody" style="font-size:12px;max-height:150px;overflow-y:auto;margin:0;white-space:pre-wrap;word-break:break-all"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="apiRhSendBtn"><i class="ti ti-api me-1"></i> Send to API-RH</button>
            </div>
        </div>
    </div>
</div>
<!-- End API-RH Modal -->

<!-- Release to System Modal -->
<div class="modal fade" id="releaseToSystemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-send me-2"></i>Release to System &mdash; Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-xs mb-3">Test FileMaker</p>

                <!-- Connection Settings -->
                <div class="row g-2 mb-2">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Server URL</label>
                        <input type="text" class="form-control form-control-sm" id="fmServer" value="https://a048803.fmphost.com">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Database</label>
                        <input type="text" class="form-control form-control-sm" id="fmDatabase" value="DBM">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Layout</label>
                        <input type="text" class="form-control form-control-sm" id="fmLayout" placeholder="e.g. Orders_API">
                    </div>
                    <div class="col-md-2 d-flex flex-column">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Test</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="fmTestConnBtn">Test Conn.</button>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Username</label>
                        <input type="text" class="form-control form-control-sm" id="fmUser" value="garryapi">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-xs text-uppercase text-muted">Password</label>
                        <input type="password" class="form-control form-control-sm" id="fmPass" value="kBk3e3yTrmAc4WaqexdjzER73">
                    </div>
                    <div class="col-md-4 d-flex flex-column justify-content-end">
                        <div id="fmConnStatus"></div>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Payload -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-semibold fs-xs text-uppercase text-muted mb-0">Payload</label>
                </div>
                <textarea class="form-control font-monospace" id="fmPayload" rows="12" style="font-size:12px"><?= htmlspecialchars(json_encode(['fieldData' => $fmPayload], JSON_PRETTY_PRINT)) ?></textarea>

                <!-- Response -->
                <div id="fmResponseWrap" class="mt-3" style="display:none">
                    <label class="form-label fw-semibold fs-xs text-uppercase text-muted mb-1">FileMaker Response</label>
                    <pre class="p-3 rounded border bg-light" id="fmResponseBody" style="font-size:12px;max-height:150px;overflow-y:auto;margin:0"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="fmSendBtn"><i class="ti ti-send me-1"></i> Send to FileMaker</button>
            </div>
        </div>
    </div>
</div>
<!-- End Release to System Modal -->

<!-- Change Info Modal -->
<div class="modal fade" id="changeInfoModal" tabindex="-1" aria-labelledby="changeInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeInfoModalLabel">Change Insurance / Carrier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted fs-xs fw-semibold text-uppercase">Submitted Value</label>
                    <div class="p-2 bg-danger-subtle border border-danger-subtle rounded">
                        <div class="fw-semibold text-danger mb-1" id="changeInfoSubmitted"></div>
                        <div class="text-muted fs-xs" id="changeInfoAddress" style="display:none">
                            <i class="ti ti-map-pin me-1"></i><span id="changeInfoAddressText"></span>
                        </div>
                        <div class="text-muted fs-xs mt-1" id="changeInfoContact" style="display:none">
                            <i class="ti ti-phone me-1"></i><span id="changeInfoPhone"></span>
                            &nbsp;&nbsp;<i class="ti ti-printer me-1"></i><span id="changeInfoFax"></span>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="changeInfoSelect">Select Correct Insurance / Carrier</label>
                    <select class="form-select" id="changeInfoSelect">
                        <option value="">-- Select --</option>
                        <optgroup label="Insurance Carriers">
                            <option data-address="2775 Sanders Rd, Northbrook, IL 60062" data-phone="(800) 255-7828" data-fax="(800) 416-8803">Allstate Insurance</option>
                            <option data-address="1 State Farm Plaza, Bloomington, IL 61710" data-phone="(800) 732-5246" data-fax="(800) 732-5247">State Farm</option>
                            <option data-address="6301 Owensmouth Ave, Woodland Hills, CA 91367" data-phone="(800) 435-7764" data-fax="(818) 584-6001">Farmers Insurance</option>
                            <option data-address="175 Berkeley St, Boston, MA 02116" data-phone="(800) 290-7933" data-fax="(617) 357-9500">Liberty Mutual</option>
                            <option data-address="485 Lexington Ave, New York, NY 10017" data-phone="(800) 328-2189" data-fax="(860) 277-7362">Travelers Insurance</option>
                            <option data-address="1 Nationwide Plaza, Columbus, OH 43215" data-phone="(800) 882-2822" data-fax="(614) 249-7705">Nationwide</option>
                            <option data-address="9800 Fredericksburg Rd, San Antonio, TX 78288" data-phone="(800) 531-8722" data-fax="(800) 531-8951">USAA</option>
                            <option data-address="6300 Wilson Mills Rd, Mayfield Village, OH 44143" data-phone="(800) 776-4737" data-fax="(440) 395-4000">Progressive</option>
                            <option data-address="5620 Virginia Beach Blvd, Norfolk, VA 23502" data-phone="(800) 207-7847" data-fax="(757) 819-6200">Geico</option>
                            <option data-address="1 Hartford Plaza, Hartford, CT 06155" data-phone="(860) 547-5000" data-fax="(860) 547-6001">Hartford Financial Services</option>
                        </optgroup>
                        <optgroup label="Workers Comp Carriers">
                            <option data-address="1400 American Ln, Schaumburg, IL 60196" data-phone="(800) 382-2150" data-fax="(847) 605-6011">Zurich North America</option>
                            <option data-address="202 Hall's Mill Rd, Whitehouse Station, NJ 08889" data-phone="(800) 252-4670" data-fax="(908) 903-3001">Chubb</option>
                            <option data-address="175 Water St, New York, NY 10038" data-phone="(212) 770-7000" data-fax="(212) 509-9705">AIG (American International Group)</option>
                            <option data-address="3555 Farnam St, Omaha, NE 68131" data-phone="(402) 346-1400" data-fax="(402) 346-3375">Berkshire Hathaway</option>
                            <option data-address="11455 El Camino Real, San Diego, CA 92130" data-phone="(858) 350-2400" data-fax="(858) 350-2700">ICW Group</option>
                            <option data-address="10375 Professional Cir, Reno, NV 89521" data-phone="(888) 682-6671" data-fax="(775) 327-2801">EMPLOYERS Holdings</option>
                            <option data-address="800 Superior Ave E, Cleveland, OH 44114" data-phone="(216) 689-7000" data-fax="(216) 689-4236">AmTrust Financial</option>
                        </optgroup>
                    </select>
                    <div id="selectedCarrierInfo" class="mt-2 p-2 bg-light border rounded" style="display:none">
                        <div class="text-muted fs-xs">
                            <i class="ti ti-map-pin me-1"></i><span id="selectedCarrierAddress"></span>
                        </div>
                        <div class="text-muted fs-xs mt-1">
                            <i class="ti ti-phone me-1"></i><span id="selectedCarrierPhone"></span>
                            &nbsp;&nbsp;<i class="ti ti-printer me-1"></i><span id="selectedCarrierFax"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="changeInfoSave">Apply Change</button>
            </div>
        </div>
    </div>
</div>
<!-- End Change Info Modal -->
