<?php require_once 'config/db.php'; ?>
<?php
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$order_id) {
    header('Location: reports.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM API_Input_Orders WHERE __kp_API_Input_Order_ID = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: reports.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
	<head>
		<?php $title = "Order Details"; include('partials/title-meta.php'); ?> <?php include('partials/head-css.php'); ?>
	</head>

	<body>
		<!-- Begin page -->
		<div class="wrapper">
			<?php include('partials/topbar.php'); ?> <?php include('partials/sidenav.php'); ?>

			<!-- ============================================================== -->
			<!-- Start Main Content -->
			<!-- ============================================================== -->

			<div class="content-page">
				<div class="container-fluid">
					<?php   $subtitle = "Order"; $title = "Order Details"; include('partials/page-title.php'); ?>

					<div class="row justify-content-center">
						<div class="col-xxl-10">
							<div class="row">
								<!-- Project Main Details -->
								<div class="col-xl-9">
									<div class="card">
										<div class="card-header align-items-start p-4">
											<div>
												<h3 class="mb-1 d-flex fs-xl align-items-center">Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></h3>
												<p class="text-muted mb-3"><i class="ti ti-calendar"></i> 24 Apr, 2025 <small class="text-muted">10:10 AM</small></p>
												<span class="badge badge-soft-success fs-xxs badge-label"><i class="ti ti-circle-filled fs-sm align-middle"></i> Paid</span>
												<span class="badge badge-soft-info fs-xxs badge-label"><i class="ti ti-truck fs-sm align-middle"></i> Shipped</span>
											</div>
											<div class="ms-auto">
												<a href="javascript: void(0);" class="btn btn-light"><i class="ti ti-pencil me-1"></i> Modify</a>
												<a href="javascript: void(0);" class="btn btn-success"><i class="ti ti-send me-1"></i> Release to FileMaker</a>
											</div>
										</div>
										<div class="card-body px-4">
											<!-- Case Information -->
											<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Case Information</h5>
											<div class="row g-3 mb-4">
												<div class="col-md-3">
													<label class="form-label fw-semibold">Subtype</label>
													<input type="text" class="form-control" value="IMR" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Case No.</label>
													<input type="text" class="form-control" value="ADJ1234567" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">DOI Start</label>
													<input type="text" class="form-control" value="2023-01-15" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">DOI End</label>
													<input type="text" class="form-control" value="2023-06-30" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Letter of Rep Date</label>
													<input type="text" class="form-control" value="2023-02-01" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Employer</label>
													<input type="text" class="form-control" value="ABC Company Inc." readonly>
												</div>
											</div>

											<hr>

											<!-- Court Venue -->
											<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Court Venue</h5>
											<div class="row g-3 mb-4">
												<div class="col-md-3">
													<label class="form-label fw-semibold">Name</label>
													<input type="text" class="form-control" value="WCAB Los Angeles" readonly>
												</div>
												<div class="col-md-4">
													<label class="form-label fw-semibold">Address</label>
													<input type="text" class="form-control" value="320 W 4th St" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">City</label>
													<input type="text" class="form-control" value="Los Angeles" readonly>
												</div>
												<div class="col-md-1">
													<label class="form-label fw-semibold">State</label>
													<input type="text" class="form-control" value="CA" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Phone</label>
													<input type="text" class="form-control" value="213-555-0100" readonly>
												</div>
											</div>

											<hr>
											<!-- Insurance Carrier -->
											<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Insurance Carrier</h5>
											<div class="row g-3 mb-4">
												<div class="col-md-4">
													<label class="form-label fw-semibold">Carrier Name</label>
													<input type="text" class="form-control" value="State Fund Insurance" readonly>
												</div>
												<div class="col-md-4">
													<label class="form-label fw-semibold">Address</label>
													<input type="text" class="form-control" value="123 Insurance Blvd" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">City</label>
													<input type="text" class="form-control" value="Sacramento" readonly>
												</div>
												<div class="col-md-1">
													<label class="form-label fw-semibold">State</label>
													<input type="text" class="form-control" value="CA" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">ZIP</label>
													<input type="text" class="form-control" value="95814" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Phone</label>
													<input type="text" class="form-control" value="916-555-0200" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Claim No.</label>
													<input type="text" class="form-control" value="SF-2023-001" readonly>
												</div>
												
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Name</label>
													<input type="text" class="form-control" value="John Smith" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Phone</label>
													<input type="text" class="form-control" value="916-555-0201" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Fax</label>
													<input type="text" class="form-control" value="916-555-0202" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Email</label>
													<input type="text" class="form-control" value="john.smith@statefund.com" readonly>
												</div>

												<div class="col-md-12"><hr class="my-1"></div>

												<div class="col-md-4">
													<label class="form-label fw-semibold">Carrier Name</label>
													<input type="text" class="form-control" value="Inter Fund Insurance" readonly>
												</div>
												<div class="col-md-4">
													<label class="form-label fw-semibold">Address</label>
													<input type="text" class="form-control" value="456 Insurance Blvd" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">City</label>
													<input type="text" class="form-control" value="Sacramento" readonly>
												</div>
												<div class="col-md-1">
													<label class="form-label fw-semibold">State</label>
													<input type="text" class="form-control" value="CA" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">ZIP</label>
													<input type="text" class="form-control" value="95814" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Phone</label>
													<input type="text" class="form-control" value="916-555-0200" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Claim No.</label>
													<input type="text" class="form-control" value="SF-2023-001" readonly>
												</div>
												
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Name</label>
													<input type="text" class="form-control" value="John Smith" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Phone</label>
													<input type="text" class="form-control" value="916-555-0201" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Fax</label>
													<input type="text" class="form-control" value="916-555-0202" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Adjuster Email</label>
													<input type="text" class="form-control" value="john.smith@statefund.com" readonly>
												</div>
											</div>

											<hr>

											<!-- Opposing Counsel -->
											<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Opposing Counsel</h5>
											<div class="row g-3 mb-4">
												<div class="col-md-4">
													<label class="form-label fw-semibold">Name</label>
													<input type="text" class="form-control" value="Jane Doe" readonly>
												</div>
												<div class="col-md-4">
													<label class="form-label fw-semibold">Address</label>
													<input type="text" class="form-control" value="456 Law Ave" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">City</label>
													<input type="text" class="form-control" value="Los Angeles" readonly>
												</div>
												<div class="col-md-1">
													<label class="form-label fw-semibold">State</label>
													<input type="text" class="form-control" value="CA" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">ZIP</label>
													<input type="text" class="form-control" value="90001" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Phone</label>
													<input type="text" class="form-control" value="213-555-0300" readonly>
												</div>
											</div>

											<hr>

											<!-- Records Location -->
											<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Records Location</h5>
											<div class="row g-3">
												<div class="col-md-2">
													<label class="form-label fw-semibold">Priority</label>
													<input type="text" class="form-control" value="Standard" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Record Type</label>
													<input type="text" class="form-control" value="Medical" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Date Needed</label>
													<input type="text" class="form-control" value="2024-03-01" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Location Name</label>
													<input type="text" class="form-control" value="UCLA Medical Center" readonly>
												</div>
												<div class="col-md-3">
													<label class="form-label fw-semibold">Address</label>
													<input type="text" class="form-control" value="100 Medical Plaza Dr" readonly>
												</div>
												<div class="col-md-2">
													<label class="form-label fw-semibold">Phone</label>
													<input type="text" class="form-control" value="310-555-0400" readonly>
												</div>
												<div class="col-md-10">
													<label class="form-label fw-semibold">Special Instructions</label>
													<input type="text" class="form-control" value="Please call ahead before arrival" readonly>
												</div>
											</div>

										</div>
										<!-- end card-body -->
									</div>
									<!-- end card -->

									<div class="card">
										<div class="card-header">
											<h4 class="card-title">Order Activity</h4>
										</div>
										<div class="card-body p-4">
											<div class="timeline">
												<!-- Event 1 -->
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

												<!-- Event 2 -->
												<div class="timeline-item d-flex align-items-stretch">
													<div class="timeline-time pe-3 text-muted">Today, 9:00 AM</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">QA Status</h5>
														<p class="mb-1 text-muted">Complete Order</p>
														<span class="fs-xxs fw-semibold">By QA Agent1</span>
													</div>
												</div>

												<!-- Event 3 -->
												<div class="timeline-item d-flex align-items-stretch">
													<div class="timeline-time pe-3 text-muted">Yesterday, 3:15 PM</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">Order Copied</h5>
													</div>
												</div>

												<!-- Event 4 -->
												<div class="timeline-item d-flex align-items-stretch">
													<div class="timeline-time pe-3 text-muted">Monday, 6:00 PM</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">Order Served</h5>
													</div>
												</div>

												<div class="timeline-item d-flex align-items-stretch">
													<div class="timeline-time pe-3 text-muted">Last Friday, 5:00 PM</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">Order Verified</h5>
														<p class="mb-1 text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>
														<span class="fw-semibold fs-xxs">By OE Agent1</span>
													</div>
												</div>

												<div class="timeline-item d-flex align-items-stretch">
													<div class="timeline-time pe-3 text-muted">Last Friday, 4:00 PM</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">Order Received</h5>
														<p class="mb-1 text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													</div>
												</div>

											</div>
										</div>
									</div>
								</div>
								<!-- end col-xl-9 -->

								<!-- Sidebar -->
								<div class="col-xl-3">
									<div class="card">
										<div class="card-header justify-content-between border-dashed">
											<h4 class="card-title">Patient Details</h4>
											<a href="#!" class="btn btn-default btn-sm btn-icon rounded-circle"><i class="ti ti-pencil fs-lg"></i></a>
										</div>
										<!-- end card-header-->

										<div class="card-body">
											<div class="d-flex align-items-center mb-4">
												<div class="me-2">
													<!-- <img src="../source/inspinia5/assets/images/users/user-5.jpg" alt="avatar" class="rounded-circle avatar-lg" /> -->
													<div class="avatar-lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"><?= strtoupper(substr($order['Pat_Name'], 0, 1)) ?></div> 
												</div>
												<div>
													<h5 class="mb-1 d-flex align-items-center">
														<a href="#!" class="link-reset"><?= htmlspecialchars($order['Pat_Name']) ?></a>
													</h5>
													<p class="text-muted mb-0"><?= htmlspecialchars($order['Pat_AKA']) ?></p>
												</div>
												<div class="ms-auto">
													<div class="dropdown">
														<a href="#" class="btn btn-icon btn-ghost-light text-muted" data-bs-toggle="dropdown">
															<i class="ti ti-dots-vertical fs-xl"></i>
														</a>
														<ul class="dropdown-menu dropdown-menu-end">
															<li>
																<a class="dropdown-item" href="#"><i class="ti ti-share me-2"></i> Share</a>
															</li>
															<li>
																<a class="dropdown-item" href="#"><i class="ti ti-edit me-2"></i> Edit</a>
															</li>
															<li>
																<a class="dropdown-item" href="#"><i class="ti ti-ban me-2"></i> Block</a>
															</li>
															<li>
																<a class="dropdown-item text-danger" href="#"><i class="ti ti-trash me-2"></i> Delete</a>
															</li>
														</ul>
													</div>
												</div>
											</div>

											<ul class="list-unstyled text-muted mb-0">
												<li class="mb-2">
													<div class="d-flex align-items-center gap-2">
														<div class="avatar-xs avatar-img-size fs-24">
															<span class="avatar-title text-bg-light fs-sm rounded-circle">
																<i class="ti ti-calendar"></i>
															</span>
														</div>
														<h5 class="fs-base mb-0 fw-medium"><?= htmlspecialchars($order['Pat_DOB']) ?></h5>
													</div>
												</li>
												<li class="mb-2">
													<div class="d-flex align-items-center gap-2">
														<div class="avatar-xs avatar-img-size fs-24">
															<span class="avatar-title text-bg-light fs-sm rounded-circle"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="SSN">
																<i class="ti ti-id"></i>
															</span>
														</div>
														<h5 class="fs-base mb-0 fw-medium"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="SSN" ><?= htmlspecialchars($order['PAT_SSN']) ?></h5>
													</div>
												</li>
												<li>
													<div class="d-flex align-items-center gap-2">
														<div class="avatar-xs avatar-img-size fs-24">
															<span class="avatar-title text-bg-light fs-sm rounded-circle"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Address">
																<i class="ti ti-map-pin"></i>
															</span>
														</div>
														<h5 class="fs-base mb-0 fw-medium"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Address" ><?= htmlspecialchars($order['Pat_Address_Street']) ?> <?= htmlspecialchars($order['Pat_Address_City']) ?>, <?= htmlspecialchars($order['Pat_Address_State']) ?> <?= htmlspecialchars($order['Pat_Address_Zip']) ?></h5>
													</div>
												</li>
											</ul>
										</div>
										<!-- end card-body-->
									</div>
									<!-- end card-->

									<div class="card">
										<div class="card-header justify-content-between border-dashed">
											<h4 class="card-title">Employer Details</h4>
											<a href="#!" class="btn btn-default btn-sm btn-icon rounded-circle"><i class="ti ti-pencil fs-lg"></i></a>
										</div>
										<!-- end card-header -->

										<div class="card-body">
											<div class="d-flex align-items-start my-3">
												<div class="flex-grow-1">
													<h5 class="mb-2">John Doe</h5>
													<p class="text-muted mb-1">
														1234 Elm Street,<br />
														Apt 567,<br />
														Springfield, IL 62704,<br />
														United States
													</p>
													<p class="mb-0 text-muted">
														<strong>Phone:</strong> (123) 456-7890<br />
														<strong>Email:</strong> john.doe@example.com
													</p>
												</div>
												<div class="ms-auto">
													<span class="badge bg-success-subtle text-success">Primary Address</span>
												</div>
											</div>

											<div class="alert alert-warning mb-0">
												<h6 class="mb-2">Delivery Instructions:</h6>
												<p class="fst-italic mb-0">Please leave the package at the front door if no one is home. Call upon arrival.</p>
											</div>
										</div>
										<!-- end card-body -->
									</div>
									<!-- end card -->

									<div class="card">
										<div class="card-header justify-content-between border-dashed">
											<h4 class="card-title">Billing Details</h4>
											<a href="#!" class="btn btn-default btn-sm btn-icon rounded-circle"><i class="ti ti-pencil fs-lg"></i></a>
										</div>
										<!-- end card-header -->

										<div class="card-body">
											<!-- Billing Address -->
											<div class="d-flex align-items-start mb-0">
												<div class="flex-grow-1">
													<h5 class="mb-2">John Doe</h5>
													<p class="text-muted mb-0">
														5678 Oak Avenue,<br />
														Suite 101,<br />
														Chicago, IL 60611,<br />
														United States
													</p>
												</div>
												<div class="ms-auto">
													<span class="badge bg-primary-subtle text-primary">Billing Address</span>
												</div>
											</div>

											<hr />

											<!-- Card Details -->
											<div class="d-flex align-items-center">
												<div class="avatar-sm me-2">
													<img src="../source/inspinia5/assets/images/cards/mastercard.svg" alt="Mastercard" class="img-fluid rounded" />
												</div>
												<div>
													<h5 class="fs-xs mb-1">Mastercard Ending in 4242</h5>
													<p class="text-muted mb-0 fs-xs">Expiry: 08/26</p>
												</div>
												<div class="ms-auto">
													<span class="badge bg-success-subtle text-success">Paid</span>
												</div>
											</div>
										</div>
										<!-- end card-body -->
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

				<?php include('partials/footer.php'); ?>
			</div>

			<!-- ============================================================== -->
			<!-- End of Main Content -->
			<!-- ============================================================== -->
		</div>
		<!-- END wrapper -->

		<?php include('partials/customizer.php'); ?> <?php include('partials/footer-scripts.php'); ?>
	</body>
</html>
