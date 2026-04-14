<?php require_once 'config/db.php'; ?>
<?php
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$order_id) {
    header('Location: reports.php');
    exit;
}

//featch the order first
$stmt = $pdo->prepare("SELECT * FROM API_Input_Orders WHERE __kp_API_Input_Order_ID = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: reports.php');
    exit;
}

// 2nd - locations
// $stmt2 = $pdo->prepare("                                                                                                                                                                            
//       SELECT iol.* FROM API_Input_Order_Locations AS iol
//       INNER JOIN API_Input_Orders AS io ON io.`__kp_API_Input_Order_ID` = iol.`_kf_API_Input_Order_ID`                                                                                                
//       WHERE io.`__kp_API_Input_Order_ID` = ?                                                                                                                                                          
//   ");    
$stmt2 = $pdo->prepare("SELECT * FROM API_Input_Order_Locations WHERE _kf_API_Input_Order_ID = ?");
$stmt2->execute([$order_id]);                                                                                                                                                                        
$locations = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
// 3rd - Insurance Carriers
$stmt3 = $pdo->prepare("SELECT * FROM API_Input_Insurance_Carriers WHERE _kf_API_Input_Order_ID = ?");
$stmt3->execute([$order_id]);                                                                                                                                                                        
$insurance = $stmt3->fetchAll(PDO::FETCH_ASSOC);


// loop inside the oredr	
$order['locations'] = $locations;   
$order['insurance'] = $insurance;  
//$order['locations'] = array_column($locations, null, '__kp_API_Input_Order_Location_ID'); 
                                                                                                                                                                                                      

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
									
											<div><?php //echo '<pre>' . print_r($order, true) . '</pre>'; ?></div>
											<div>
												<h3 class="mb-1 d-flex fs-xl align-items-center">Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></h3>
												<p class="text-muted mb-3"><i class="ti ti-calendar"></i> <?= date('d M, Y', strtotime($order['API_Input_Timestamp'])) ?> <small class="text-muted"><?= date('g:i A', strtotime($order['API_Input_Timestamp'])) ?></small></p>
												<!-- <span class="badge badge-soft-success fs-xxs badge-label"><i class="ti ti-circle-filled fs-sm align-middle"></i> Paid</span> -->
												<span class="badge badge-soft-info fs-xxs badge-label"><i class="ti ti-inbox fs-sm align-middle"></i> Order Received</span>
											</div>
											<div class="ms-auto">
												<!-- <a href="javascript: void(0);" class="btn btn-light"><i class="ti ti-pencil me-1"></i> Modify</a> -->
												<a href="javascript: void(0);" class="btn btn-success"><i class="ti ti-send me-1"></i> Release to FileMaker</a>
											</div>
										</div>
										<div class="card-body px-4">
											<!-- Case Information -->
											<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Case Information</h5>
											<div class="row g-3 mb-4">
												<div class="col-md-3">
													<label class="form-label fw-semibold">Service Type</label>
														<input type="text" id="<?= rand(0,1) ? 'validInput' : 'inValidationInput' ?>" class="form-control is-valid" value="<?= htmlspecialchars($order['_kf_Service_Type_ID_Str']) ?>" />
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
										<!-- end card-body -->
									</div>
									<!-- end card -->

									<!-- Insurance Carriers -->
									<div class="card">
										<div class="card-header">
											<h4 class="card-title">Insurance Carriers</h4>
										</div>
										<div class="card-body px-4">
											<?php foreach ($order['insurance'] as $i => $ins): ?>
												<?php if ($i > 0): ?>
													<hr class="my-3">
												<?php endif; ?>
												<div class="row g-3">
													<div class="col-md-4">
														<label class="form-label fw-semibold">Carrier Name</label>
															<?php $insValid = rand(0,1); ?>
															<input type="text" id="<?= $insValid ? 'validInput' : 'inValidationInput' ?>" class="form-control <?= $insValid ? 'is-valid' : 'is-invalid' ?>" value="<?= htmlspecialchars($ins['Ins_Name']) ?>"
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
										<!-- end card-body -->
									</div>
									<!-- end card -->

									<!-- Records Locations -->
									<div class="card">
										<div class="card-header">
											<h4 class="card-title">Records Locations</h4>
										</div>
										<div class="card-body px-4">
											<?php foreach ($order['locations'] as $i => $loc): ?>
												<?php if ($i > 0): ?><hr class="my-3"><?php endif; ?>
												<div class="row g-3">
													<div class="col-md-2">
														<label class="form-label fw-semibold">Record Type</label>
														<select class="form-select">
															<option value="">-- Select --</option>
															<?php
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
															foreach ($recTypeOptions as $opt):
															?>
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
														<?php $insValid = rand(0,1); ?>
															<input type="text" id="<?= $insValid ? 'validInput' : 'inValidationInput' ?>" class="form-control <?= $insValid ? 'is-valid' : 'is-invalid' ?>" value="<?= htmlspecialchars($loc['Loc_Name']) ?>"
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
										<!-- end card-body -->
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
										<!-- end card-header-->

										<div class="card-body">
											<div class="d-flex align-items-center mb-4">
												<div class="me-2">
													<!-- <img src="../source/inspinia5/assets/images/users/user-5.jpg" alt="avatar" class="rounded-circle avatar-lg" /> -->
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
															<span class="avatar-title text-bg-light fs-sm rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Date of Birth" >
																<i class="ti ti-calendar"></i>
															</span>
														</div>
														<h5 class="fs-base mb-0 fw-medium" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Date of Birth" ><?= htmlspecialchars($order['Pat_DOB']) ?></h5>
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
										<div class="card-header">
											<h4 class="card-title">Activity</h4>
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
													<div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="9:00 AM">Apr 14 '26</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">QA Status</h5>
														<p class="mb-1 text-muted">Complete Order</p>
														<span class="fs-xxs fw-semibold">By QA Agent1</span>
													</div>
												</div>

												<!-- Event 3 -->
												<div class="timeline-item d-flex align-items-stretch">
													<div class="timeline-time pe-3 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="3:15 PM">Apr 13 '26</div>
													<div class="timeline-dot bg-success"></div>
													<div class="timeline-content ps-3 pb-5">
														<h5 class="mb-1">Order Copied</h5>
													</div>
												</div>

												<!-- Event 4 -->
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

									<!-- <div class="card">
										<div class="card-header justify-content-between border-dashed">
											<h4 class="card-title">Requesting Party</h4>
											<a href="#!" class="btn btn-default btn-sm btn-icon rounded-circle"><i class="ti ti-pencil fs-lg"></i></a>
										</div> 

										<div class="card-body">
											<div class="d-flex align-items-start my-3">
												<div class="flex-grow-1">
													<h5 class="mb-2">Mercury Drug</h5>
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
										
									</div>-->
									<!-- end card -->

									<!-- <div class="card">
										<div class="card-header justify-content-between border-dashed">
											<h4 class="card-title">Billing Details</h4>
											<a href="#!" class="btn btn-default btn-sm btn-icon rounded-circle"><i class="ti ti-pencil fs-lg"></i></a>
										</div>
							
										<div class="card-body">
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
									</div> -->
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

		<?php include('partials/customizer.php'); ?> <?php include('partials/footer-scripts.php'); ?>

		<script>
		(function () {
			var modal = new bootstrap.Modal(document.getElementById('changeInfoModal'));
			var activeInput = null;

			document.querySelectorAll('input.is-invalid').forEach(function (input) {
				input.style.cursor = 'pointer';
				input.addEventListener('click', function () {
					activeInput = input;
					document.getElementById('changeInfoSubmitted').textContent = input.value || '(empty)';
					document.getElementById('changeInfoSelect').value = '';
					document.getElementById('selectedCarrierInfo').style.display = 'none';

					var address = input.dataset.address || '';
					var phone   = input.dataset.phone   || '';
					var fax     = input.dataset.fax     || '';

					var addrRow    = document.getElementById('changeInfoAddress');
					var contactRow = document.getElementById('changeInfoContact');

					if (address) {
						document.getElementById('changeInfoAddressText').textContent = address;
						addrRow.style.display = '';
					} else {
						addrRow.style.display = 'none';
					}

					if (phone || fax) {
						document.getElementById('changeInfoPhone').textContent = phone || '—';
						document.getElementById('changeInfoFax').textContent   = fax   || '—';
						contactRow.style.display = '';
					} else {
						contactRow.style.display = 'none';
					}

					modal.show();
				});
			});

			document.getElementById('changeInfoSelect').addEventListener('change', function () {
				var opt = this.options[this.selectedIndex];
				var info = document.getElementById('selectedCarrierInfo');
				if (opt && opt.dataset.address) {
					document.getElementById('selectedCarrierAddress').textContent = opt.dataset.address;
					document.getElementById('selectedCarrierPhone').textContent   = opt.dataset.phone || '—';
					document.getElementById('selectedCarrierFax').textContent     = opt.dataset.fax   || '—';
					info.style.display = '';
				} else {
					info.style.display = 'none';
				}
			});

			document.getElementById('changeInfoSave').addEventListener('click', function () {
				var selected = document.getElementById('changeInfoSelect').value;
				if (activeInput && selected) {
					activeInput.value = selected;
					activeInput.classList.remove('is-invalid');
					activeInput.classList.add('is-valid');
					activeInput.style.cursor = '';
				}
				modal.hide();
			});
		})();
		</script>
	</body>
</html>
