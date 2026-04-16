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
					<?php   $subtitle = "Order"; $title = "Order Details"; ?>
					<div class="page-title-head d-flex align-items-center">
						<div class="flex-grow-1">
							<h4 class="page-main-title m-0"><?php echo ($title); ?></h4>
						</div>

						<div class="text-end">
							<ol class="breadcrumb m-0 py-0">
								<li class="breadcrumb-item"><a href="javascript: void(0);">RTR</a></li>
								<li class="breadcrumb-item"><a href="client_reports.php"><?php echo ($subtitle); ?></a></li>
								<li class="breadcrumb-item active"><?php echo ($title); ?></li>
							</ol>
						</div>
					</div>

					<!-- 3 column -->
					<div class="row mb-2 sticky-top" >
                        <div class="col-lg-12">
							<div class="card">
								<div class="card-header align-items-start p-4">
							
									<div><?php //echo '<pre>' . print_r($order, true) . '</pre>'; ?></div>
									<div>
										<h3 class="mb-1 d-flex fs-xl align-items-center">Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></h3>
										<p class="text-muted mb-3"><i class="ti ti-calendar"></i> <?= date('d M, Y', strtotime($order['API_Input_Timestamp'])) ?> <small class="text-muted"><?= date('g:i A', strtotime($order['API_Input_Timestamp'])) ?></small></p>
										<!-- <span class="badge badge-soft-success fs-xxs badge-label"><i class="ti ti-circle-filled fs-sm align-middle"></i> Paid</span> -->
										<span class="badge badge-soft-info fs-xxs badge-label"><i class="ti ti-inbox fs-sm align-middle"></i> Order Received</span>
									</div>
								</div>
								<div class="card-body px-4">
									<!-- Case Information -->
									<h5 class="fs-sm fw-semibold mt-4 mb-2 text-muted text-uppercase">Case Information</h5>
									<div class="row mb-4">
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">SKU:</h6>
											<p class="fw-medium mb-0">SOFA-10058</p>
										</div>
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Category:</h6>
											<p class="fw-medium mb-0">Furniture</p>
										</div>
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Stock:</h6>
											<p class="fw-medium mb-0">128</p>
										</div>
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Published:</h6>
											<p class="fw-medium mb-0">
												28 Apr, 2025
												<small class="text-muted">10:15 AM</small>
											</p>
										</div>
									</div>
								</div>
								<!-- end card-body -->
							</div>
                        </div>
                    </div>
					<div class="row">
						<div class="col-xl-3 col-lg-6 order-lg-1 order-xl-1">
							<div class="card card-top-sticky">
								<div class="card-body">
									<div class="list-group list-group-flush list-custom mt-3">								
										<div class="col-12 mb-4">
											<h6 class="mb-1 text-muted text-uppercase">Created Date:</h6>
											<p class="fw-medium mb-0">March 15, 2025</p>
										</div>
										<div class="col-12 mb-4">
											<h6 class="mb-1 text-muted text-uppercase">Deadline:</h6>
											<p class="fw-medium mb-0">June 30, 2025</p>
										</div>

										<div class="mb-4">
											<!-- <h5 class="fs-base mb-2">Project Description:</h5>
											<p class="text-muted">This dashboard provides AI-powered insights and analytics for Starbucks business data. It includes sales performance, customer behavior, and predictive trends to assist in data-driven decision-making.</p>
											<p class="text-muted">
												Customizable reports and role-based dashboards ensure relevant insights for marketing teams, financial analysts, and executive decision-makers. The system is built with scalability and responsiveness in mind, supporting both
												desktop and mobile views for seamless access.
											</p> -->
											<div class="col-12 mb-4">
												<h6 class="mb-1 text-muted text-uppercase">Created Date:</h6>
												<p class="fw-medium mb-0">March 15, 2025</p>
											</div>
											<div class="col-12 mb-4">
												<h6 class="mb-1 text-muted text-uppercase">Deadline:</h6>
												<p class="fw-medium mb-0">June 30, 2025</p>
											</div>
										
											<hr />
											<div class="list-group-item mt-2">
												<span class="align-middle"><strong>Insurance Carrier</strong></span>
											</div>

											<!-- Insurance Carrier List -->
											<?php foreach ($insurance as $ins): ?>
											<a href="#!" class="list-group-item list-group-item-action">
												<i class="ti ti-shield me-1 fs-lg align-middle"></i>
												<span class="align-middle" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-html="true" title="<?= htmlspecialchars($ins['Ins_Name']) ?>" data-bs-content="<p class=&quot;mb-1&quot;><i class=&quot;ti ti-map-pin me-1&quot;></i><?= htmlspecialchars($ins['Ins_Address_Street']) ?>, <?= htmlspecialchars($ins['Ins_Address_City']) ?>, <?= htmlspecialchars($ins['Ins_Address_State']) ?> <?= htmlspecialchars($ins['Ins_Address_Zip']) ?></p><p class=&quot;mb-0 text-muted&quot;><i class=&quot;ti ti-phone me-1&quot;></i><?= htmlspecialchars($ins['Ins_Address_Phone']) ?> &nbsp; <i class=&quot;ti ti-printer me-1&quot;></i><?= htmlspecialchars($ins['Ins_Address_Phone_Fax']) ?></p>"><?= htmlspecialchars($ins['Ins_Name']) ?></span>
											</a>
											<?php endforeach; ?>
											<!-- Insurance Carrier List -->

											<hr />
											<div class="list-group-item mt-2">
												<span class="align-middle"><strong>Records Location</strong></span>
											</div>

											<!-- Records Location List -->
											<?php foreach ($locations as $loc): ?>
											<a href="#!" class="list-group-item list-group-item-action loc-trigger" data-loc-id="<?= $loc['__kp_API_Input_Order_Location_ID'] ?>">
												<i class="ti ti-tag me-1 text-primary fs-lg align-middle"></i>
												<span class="align-middle" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-html="true" title="<?= htmlspecialchars($loc['Rec_Type']) ?>" data-bs-content="<p class=&quot;mb-1&quot;><i class=&quot;ti ti-map-pin me-1&quot;></i><?= htmlspecialchars($loc['Loc_Address_Street']) ?>, <?= htmlspecialchars($loc['Loc_Address_City']) ?>, <?= htmlspecialchars($loc['Loc_Address_State']) ?> <?= htmlspecialchars($loc['Loc_Address_Zip']) ?></p><p class=&quot;mb-0 text-muted&quot;><i class=&quot;ti ti-phone me-1&quot;></i><?= htmlspecialchars($loc['Loc_Address_Phone']) ?> &nbsp; <i class=&quot;ti ti-printer me-1&quot;></i><?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?></p>"><?= htmlspecialchars($loc['Loc_Name']) ?></span>
											</a>
											<?php endforeach; ?>
											<!-- Records Location List -->
											
										</div>
									</div>
								</div>
							</div>
							<!-- end card -->
						</div>
						<!-- end col-->

						<div class="col-xl-9 recordlocationhist d-none" >
							<div class="card card-h-100 rounded-0 rounded-start">
								<div class="card-header align-items-start p-4">
									<div class="avatar-xxl me-3">
										<span class="avatar-title text-bg-light rounded">
											<img src="assets/images/logos/starbucks.svg" height="48" alt="Brand-img" />
										</span>
									</div>
									<div>
										<h3 class="mb-1 d-flex fs-xl align-items-center">Starbucks - AI Analytics Dashboard</h3>
										<p class="text-muted mb-2 fs-xxs">Updated 5 minutes ago</p>
										<span class="badge badge-soft-success fs-xxs badge-label">In Progress</span>
									</div>
									<div class="ms-auto">
										<a href="javascript: void(0);" class="btn btn-light">
											<i class="ti ti-pencil me-1"></i>
											Edit
										</a>
									</div>
								</div>
								<div class="card-body px-4">
									<div class="mb-4">
										<h5 class="fs-base mb-2">Project Description:</h5>
										<p class="text-muted">This dashboard provides AI-powered insights and analytics for Starbucks business data. It includes sales performance, customer behavior, and predictive trends to assist in data-driven decision-making.</p>
										<p class="text-muted">
											Customizable reports and role-based dashboards ensure relevant insights for marketing teams, financial analysts, and executive decision-makers. The system is built with scalability and responsiveness in mind, supporting both
											desktop and mobile views for seamless access.
										</p>
									</div>
									<div class="row mb-4">
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Created Date:</h6>
											<p class="fw-medium mb-0">March 15, 2025</p>
										</div>
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Deadline:</h6>
											<p class="fw-medium mb-0">June 30, 2025</p>
										</div>
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Created By:</h6>
											<p class="fw-medium mb-0">John Smith</p>
										</div>
										<div class="col-md-4 col-xl-3">
											<h6 class="mb-1 text-muted text-uppercase">Client Name:</h6>
											<p class="fw-medium mb-0">Starbucks Corporation</p>
										</div>
									</div>

									<!-- Tabs -->
									<ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" data-bs-toggle="tab" href="#comments" role="tab">
												<i class="ti ti-message-circle fs-lg me-md-1 align-middle"></i>
												<span class="d-none d-md-inline-block align-middle">Comments</span>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" href="#Mytasks" role="tab">
												<i class="ti ti-list-check fs-lg me-md-1 align-middle"></i>
												<span class="d-none d-md-inline-block align-middle">Task List</span>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" href="#activity" role="tab">
												<i class="ti ti-activity fs-lg me-md-1 align-middle"></i>
												<span class="d-none d-md-inline-block align-middle">Activity</span>
											</a>
										</li>
									</ul>
									<div class="tab-content">
										<div class="tab-pane fade active show" id="comments" role="tabpanel">
											<form action="#" class="mb-3">
												<div class="mb-3">
													<textarea class="form-control" id="form-control-textarea" rows="4" placeholder="Enter your messages..."></textarea>
												</div>
												<div class="text-end">
													<button type="submit" class="btn btn-secondary btn-sm">
														Comment
														<i class="ti ti-send-2 align-baseline ms-1"></i>
													</button>
												</div>
											</form>

											<h4 class="mb-3 fs-md">Comments (15)</h4>

											<div class="d-flex mb-2 border border-dashed rounded p-3">
												<div class="flex-shrink-0">
													<img src="assets/images/users/user-8.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
												</div>
												<div class="flex-grow-1 ms-2">
													<h5 class="mb-1">
														Liam Carter
														<small class="text-muted">15 Apr 2025 · 09:20AM</small>
													</h5>
													<p class="mb-2">Customers are reporting that the checkout page freezes after submitting their payment information.</p>
													<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
														<i class="ti ti-corner-up-left fs-lg"></i>
														Reply
													</a>

													<div class="d-flex mt-4">
														<div class="flex-shrink-0">
															<img src="assets/images/users/user-10.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
														</div>
														<div class="flex-grow-1 ms-2">
															<h5 class="mb-1">
																Nina Bryant
																<small class="text-muted">15 Apr 2025 · 11:47AM</small>
															</h5>
															<p class="mb-2">That might be caused by the third-party payment gateway. I recommend testing in incognito mode and checking for any JS errors in the console.</p>
															<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
																<i class="ti ti-corner-up-left fs-lg"></i>
																Reply
															</a>
														</div>
													</div>

													<div class="d-flex mt-4">
														<div class="flex-shrink-0">
															<img src="assets/images/users/user-3.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
														</div>
														<div class="flex-grow-1 ms-2">
															<h5 class="mb-1">
																Sophie Allen
																<small class="text-muted">16 Apr 2025 · 10:15AM</small>
															</h5>
															<p class="mb-2">We’ve noticed this issue before when the CDN cache hasn't been cleared properly. Try purging the cache and reloading the page.</p>
															<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
																<i class="ti ti-corner-up-left fs-lg"></i>
																Reply
															</a>
														</div>
													</div>
												</div>
											</div>

											<div class="d-flex mb-2 border border-dashed rounded p-3">
												<div class="flex-shrink-0">
													<img src="assets/images/users/user-6.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
												</div>
												<div class="flex-grow-1 ms-2">
													<h5 class="mb-1">
														Daniel West
														<small class="text-muted">14 Apr 2025 · 04:15PM</small>
													</h5>
													<p class="mb-2">You can also clear the browser cache or try a different browser. We had a similar issue with Chrome extensions interfering before.</p>
													<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
														<i class="ti ti-corner-up-left fs-lg"></i>
														Reply
													</a>
												</div>
											</div>

											<div class="d-flex mb-3 border border-dashed rounded p-3">
												<div class="flex-shrink-0">
													<img src="assets/images/users/user-10.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
												</div>
												<div class="flex-grow-1 ms-2">
													<h5 class="mb-1">
														Nina Bryant
														<small class="text-muted">16 Apr 2025 · 08:04AM</small>
													</h5>
													<p>
														The
														<a href="javascript:void(0)" class="text-decoration-underline">System Status Page</a>
														has been updated. We're actively monitoring and will release a patch within 24 hours.
													</p>

													<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
														<i class="ti ti-corner-up-left fs-lg"></i>
														Reply
													</a>

													<div class="d-flex mt-4">
														<div class="flex-shrink-0">
															<img src="assets/images/users/user-6.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
														</div>
														<div class="flex-grow-1 ms-2">
															<h5 class="mb-1">
																Daniel West
																<small class="text-muted">16 Apr 2025 · 08:30AM</small>
															</h5>
															<p>Thanks for the update! We'll notify the customers and let them know the issue is being resolved.</p>
															<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
																<i class="ti ti-corner-up-left fs-lg"></i>
																Reply
															</a>
														</div>
													</div>
												</div>
											</div>

											<ul class="pagination pagination-rounded pagination-boxed justify-content-center mb-0">
												<li class="page-item previous disabled">
													<a href="#" class="page-link">
														<i class="ti ti-chevron-left"></i>
													</a>
												</li>
												<li class="page-item active">
													<a href="#" class="page-link">1</a>
												</li>
												<li class="page-item">
													<a href="#" class="page-link">2</a>
												</li>
												<li class="page-item">
													<a href="#" class="page-link">3</a>
												</li>
												<li class="page-item">
													<a href="#" class="page-link">...</a>
												</li>
												<li class="page-item">
													<a href="#" class="page-link">5</a>
												</li>
												<li class="page-item">
													<a href="#" class="page-link">6</a>
												</li>
												<li class="page-item next">
													<a href="#" class="page-link">
														<i class="ti ti-chevron-right"></i>
													</a>
												</li>
											</ul>
										</div>

										<div class="tab-pane fade" id="Mytasks" role="tabpanel">
											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task2" />
																<a href="#!" role="button" class="link-reset fw-medium">Finalize monthly performance report</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-2.jpg" alt="avatar-2" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Liam James</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-success badge-label">Completed</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Yesterday</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">7/7</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">12</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task3" />
																<a href="#!" role="button" class="link-reset fw-medium">Design wireframes for new onboarding flow</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-4.jpg" alt="avatar-4" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Sophia Lee</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-danger badge-label">Delayed</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Tomorrow</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">2/5</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">7</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task4" />
																<a href="#!" role="button" class="link-reset fw-medium">Update customer segmentation dashboard</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-5.jpg" alt="avatar-5" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Noah Carter</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-primary badge-label">Pending</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Friday</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">0/4</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">3</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task5" />
																<a href="#!" role="button" class="link-reset fw-medium">Conduct competitor analysis report</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-6.jpg" alt="avatar-6" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Emily Davis</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-warning badge-label">In Progress</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Next Week</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">1/6</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">5</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task6" />
																<a href="#!" role="button" class="link-reset fw-medium">Implement API for mobile integration</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-7.jpg" alt="avatar-7" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Lucas White</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-info badge-label">Review</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Today</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">6/6</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">10</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task7" />
																<a href="#!" role="button" class="link-reset fw-medium">QA testing for billing module</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-8.jpg" alt="avatar-8" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Olivia Martin</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-warning badge-label">In Progress</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Monday</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">4/8</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">14</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="row g-3 align-items-center justify-content-between">
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-2">
																<input type="checkbox" class="form-check-input rounded-circle mt-0 fs-xl" id="task8" />
																<a href="#!" role="button" class="link-reset fw-medium">Schedule product roadmap presentation</a>
															</div>
														</div>
														<div class="col-md-6">
															<div class="d-flex align-items-center gap-3 justify-content-md-end">
																<div class="d-flex align-items-center gap-1">
																	<div class="avatar avatar-xs">
																		<img src="assets/images/users/user-9.jpg" alt="avatar-9" class="img-fluid rounded-circle" />
																	</div>
																	<div>
																		<h5 class="text-nowrap mb-0 lh-base">
																			<a href="#!" class="link-reset">Ethan Moore</a>
																		</h5>
																	</div>
																</div>

																<div class="flex-shrink-0">
																	<span class="badge text-bg-secondary badge-label">Planned</span>
																</div>

																<ul class="list-inline fs-base text-end flex-shrink-0 mb-0">
																	<li class="list-inline-item">
																		<i class="ti ti-calendar text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-semibold">Next Month</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-list-details text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">0/1</span>
																	</li>
																	<li class="list-inline-item ms-1">
																		<i class="ti ti-message text-muted fs-lg me-1 align-middle"></i>
																		<span class="fw-medium">0</span>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tab-pane fade" id="activity" role="tabpanel">
											<div class="d-flex gap-1 border-bottom border-dashed pb-3">
												<div class="me-2 flex-shrink-0">
													<img src="assets/images/users/user-1.jpg" class="avatar-md rounded-circle" alt="" />
												</div>
												<div class="flex-grow-1 text-muted">
													<span class="fw-medium text-body">Daniel Martinez</span>
													uploaded a revised contract file.
													<p class="fs-xs mb-0 text-body-secondary">Today 10:15 am - 24 Apr, 2025</p>
												</div>
												<p class="fs-xs text-body-secondary">5m ago</p>
											</div>

											<div class="d-flex gap-1 border-bottom border-dashed py-3">
												<div class="me-2 flex-shrink-0">
													<img src="assets/images/users/user-2.jpg" class="avatar-md rounded-circle" alt="" />
												</div>
												<div class="flex-grow-1 text-muted">
													<span class="fw-medium text-body">Nina Patel</span>
													commented on your design update.
													<p class="fs-xs mb-0 text-body-secondary">Today 8:00 am - 24 Apr, 2025</p>
												</div>
												<p class="fs-xs text-body-secondary">2h ago</p>
											</div>

											<div class="d-flex gap-1 border-bottom border-dashed py-3">
												<div class="me-2 flex-shrink-0">
													<img src="assets/images/users/user-3.jpg" class="avatar-md rounded-circle" alt="" />
												</div>
												<div class="flex-grow-1 text-muted">
													<span class="fw-medium text-body">Jason Lee</span>
													completed the feedback review.
													<p class="fs-xs mb-0 text-body-secondary">Yesterday 6:10 pm - 23 Apr, 2025</p>
												</div>
												<p class="fs-xs text-body-secondary">16h ago</p>
											</div>

											<div class="d-flex gap-1 border-bottom border-dashed py-3">
												<div class="me-2 flex-shrink-0">
													<img src="assets/images/users/user-4.jpg" class="avatar-md rounded-circle" alt="" />
												</div>
												<div class="flex-grow-1 text-muted">
													<span class="fw-medium text-body">Emma Davis</span>
													shared a link in the marketing group chat.
													<p class="fs-xs mb-2 text-body-secondary">Yesterday 3:25 pm - 23 Apr, 2025</p>
													<a href="#!" class="btn btn-default border px-1 py-0">
														<i class="ti ti-link me-1"></i>
														View
													</a>
												</div>
												<p class="fs-xs text-body-secondary">19h ago</p>
											</div>

											<div class="d-flex gap-1 border-bottom border-dashed py-3">
												<div class="me-2 flex-shrink-0">
													<img src="assets/images/users/user-5.jpg" class="avatar-md rounded-circle" alt="" />
												</div>
												<div class="flex-grow-1 text-muted position-relative">
													<span class="fw-medium text-body">Leo Zhang</span>
													sent you a private message.
													<p class="fs-xs text-body-secondary">2 days ago 11:45 am - 22 Apr, 2025</p>

													<div class="py-2 px-3 bg-light bg-opacity-50">"Lets sync up on the product roadmap tomorrow afternoon, does 2 PM work for you?"</div>
												</div>
												<p class="fs-xs flex-shrink-0 text-body-secondary">30h ago</p>
											</div>
										</div>
									</div>
								</div>
								<!-- end card-body -->
							</div>
							<!-- end card -->
						</div>
					
						<!-- end row -->
						<!-- end col-->
					</div>
					<!-- end-->
                    

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
		// Show recordlocationhist panel on location name click
		document.querySelectorAll('.loc-trigger').forEach(function (trigger) {
			trigger.addEventListener('click', function (e) {
				e.preventDefault();

				// Show the panel
				document.querySelectorAll('.recordlocationhist').forEach(function (el) {
					el.classList.remove('d-none');
				});

				// Highlight active item
				document.querySelectorAll('.loc-trigger').forEach(function (el) {
					el.classList.remove('active');
				});
				this.classList.add('active');
			});
		});

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
