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
								<div class="card-header d-flex justify-content-between align-items-start p-4">
									<div>
										<h3 class="mb-1 d-flex fs-xl align-items-center">Order #<?= htmlspecialchars($order['__kp_API_Input_Order_ID']) ?></h3>
										<p class="text-muted mb-0"><i class="ti ti-calendar"></i> <?= date('d M, Y', strtotime($order['API_Input_Timestamp'])) ?> <small class="text-muted"><?= date('g:i A', strtotime($order['API_Input_Timestamp'])) ?></small></p>
									</div>
									<div class="d-flex gap-1">
										<a href="#" class="btn btn-soft-primary btn-icon active">
                                            <i class="ti ti-category fs-lg me-1"></i> Workers's Camp
                                        </a>
                                        <a href="#" class="btn btn-soft-primary btn-icon">
                                            <i class="ti ti-list-check fs-lg me-1"></i> 2nd Status
                                        </a>
										<!-- <span class="badge badge-soft-success fs-xxs badge-label"><i class="ti ti-circle-filled fs-sm align-middle"></i> Paid</span> -->
										<!-- <span class="badge badge-soft-info fs-xxs badge-label"><i class="ti ti-inbox fs-sm align-middle"></i> Order Received</span> -->
										<button type="button" class="btn btn-secondary"><i class="ti ti-inbox fs-lg me-1"></i> Order Received</button>
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
										<div class="mb-4">
											<div class="col-12 mb-4">
												<h6 class="mb-1 text-muted text-uppercase">Patient Name:</h6>
												<p class="fw-medium mb-0"><?= htmlspecialchars($order['Pat_Name']) ?></p>
											</div>
											<div class="col-12 mb-4">
												<h6 class="mb-1 text-muted text-uppercase">Deadline:</h6>
												<p class="fw-medium mb-0">June 30, 2025</p>
											</div>

											<hr />
											<div class="list-group-item mt-2">
												<span class="align-middle"><strong>Opposing Counsel</strong></span>
											</div>

											<!-- Opposing Counsel List -->
											<a href="#!" class="list-group-item list-group-item-action">
												<i class="ti ti-scale me-1 fs-lg align-middle"></i>
												<span class="align-middle">Opposing Counsel 1</span>
											</a>
											<a href="#!" class="list-group-item list-group-item-action">
												<i class="ti ti-scale me-1 fs-lg align-middle"></i>
												<span class="align-middle">Opposing Counsel 2</span>
											</a>
											
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
											<a href="#!" class="list-group-item list-group-item-action loc-trigger"
												data-loc-id="<?= $loc['__kp_API_Input_Order_Location_ID'] ?>"
												data-loc-name="<?= htmlspecialchars($loc['Loc_Name']) ?>"
												data-loc-address="<?= htmlspecialchars($loc['Loc_Address_Street'] . ', ' . $loc['Loc_Address_City'] . ', ' . $loc['Loc_Address_State'] . ' ' . $loc['Loc_Address_Zip']) ?>"
												data-loc-phone="<?= htmlspecialchars($loc['Loc_Address_Phone']) ?>"
												data-loc-fax="<?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?>">
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

						<div class="col-xl-9 order-xl-2 recordlocationhist d-none">
							<div class="card card-h-100 rounded-0 rounded-start">
								<div class="card-header align-items-start p-4">
									<div>
										<h3 class="mb-1 d-flex fs-xl align-items-center" id="locDetailName"></h3>
										<p class="text-muted mb-2 fs-xxs">Updated 5 minutes ago</p>
										<span class="badge badge-soft-success fs-xxs badge-label">In Progress</span>
									</div>
									<div class="ms-auto">
										<h5 class="fs-base mb-2">Location Information:</h5>
										<p class="mb-0"><i class="ti ti-map-pin me-1"></i><span id="locDetailAddress"></span></p>
														<p class="mb-0 text-muted"><i class="ti ti-phone me-1"></i><span id="locDetailPhone"></span> &nbsp; <i class="ti ti-printer me-1"></i><span id="locDetailFax"></span></p>
									</div>
								</div>
								<!-- Loading Spinner -->
								<div id="locSpinner" class="d-none text-center py-5">
									<div class="spinner-border text-primary" role="status">
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>

								<div id="locDetailContent" class="d-none">
								<div class="card-body px-4">
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
									</div>

									<div class="mb-4">
										<h5 class="fs-base mb-2">Special Instruction:</h5>
										<p class="text-muted"><?= htmlspecialchars($loc['Special_Instructions']) ?>Lorem Ipsum is dummy or placeholder text commonly used in graphic design, publishing, and web development to fill spaces where content will eventually appear.</p>
									</div>

									<!-- Tabs -->
									<ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" data-bs-toggle="tab" href="#notes" role="tab">
												<i class="ti ti-message-circle fs-lg me-md-1 align-middle"></i>
												<span class="d-none d-md-inline-block align-middle">Notes</span>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" href="#files" role="tab">
												<i class="ti ti-paperclip fs-lg me-md-1 align-middle"></i>
												<span class="d-none d-md-inline-block align-middle">Files</span>
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
										<div class="tab-pane fade active show" id="notes" role="tabpanel">
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

											<h4 class="mb-3 fs-md">Comments (2)</h4>

											<div class="d-flex mb-2 border border-dashed rounded p-3">
												<div class="flex-shrink-0">
													<span class="avatar-sm rounded-circle shadow-sm bg-secondary d-flex align-items-center justify-content-center text-white fw-semibold">C</span>
												</div>
												<div class="flex-grow-1 ms-2">
													<h5 class="mb-1">
														Client 1
														<small class="text-muted">14 Apr 2025 · 04:15PM</small>
													</h5>
													<p class="mb-2">You may try reaching the insurance provider at the following phone number: 888-888-8888.</p>
													<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
														<i class="ti ti-corner-up-left fs-lg"></i>
														Reply
													</a>
												</div>
											</div>

											<div class="d-flex mb-2 border border-dashed rounded p-3">
												<div class="flex-shrink-0">
													<img src="../source/inspinia5/assets/images/users/user-6.jpg" alt="" class="avatar-sm rounded-circle shadow-sm" />
												</div>
												<div class="flex-grow-1 ms-2">
													<h5 class="mb-1">
														Daniel West
														<small class="text-muted">12 Apr 2025 · 02:11PM</small>
													</h5>
													<p class="mb-2">Insurance information is incomplete due to a missing contact phone number.</p>
													<a href="javascript:void(0);" class="badge bg-light text-muted d-inline-flex align-items-center gap-1">
														<i class="ti ti-corner-up-left fs-lg"></i>
														Reply
													</a>
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
												<li class="page-item next">
													<a href="#" class="page-link">
														<i class="ti ti-chevron-right"></i>
													</a>
												</li>
											</ul>
										</div>

										<div class="tab-pane fade" id="files" role="tabpanel">
											<div class="card mb-1">
												<div class="card-body p-2">
													<div class="table-responsive-sm">
														<table class="table table-custom table-select mb-0">
															<thead class="bg-light bg-opacity-25 thead-sm border-top border-light">
																<tr class="text-uppercase align-middle fs-xxs">
																	<th class="ps-3" style="width: 1%">
																		<input data-table-select-all class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" id="select-all-files" value="option" />
																	</th>
																	<th data-table-sort="name">Name</th>
																	<th data-table-sort data-column="type">Type</th>
																	<th data-table-sort>Modified</th>
														
																	<th class="text-end pe-3">Action</th>
																</tr>
															</thead>
															<!-- end table -->
															<tbody>
																<tr>
																	<td class="ps-3">
																		<input class="form-check-input form-check-input-light fs-14 file-item-check" type="checkbox" value="option" />
																	</td>
																	<td>
																		<div class="d-flex align-items-center gap-2">
																			<div class="flex-shrink-0 avatar-md d-flex justify-content-center align-items-center bg-light bg-opacity-50 text-muted rounded-2">
																				<i class="ti ti-file-type-pdf fs-xl avatar-title"></i>
																			</div>
																			<div class="flex-grow-1">
																				<h5 class="mb-1 fs-base">
																					<a data-sort="name" href="#!" class="link-reset">Client Proposal PDF</a>
																				</h5>
																				<p class="text-muted mb-0 fs-xs">45MB</p>
																			</div>
																		</div>
																	</td>
																	<td>PDF</td>
																	<td>May 5, 2025</td>
																	
																	<td class="text-end pe-3">
																		<div class="d-flex align-items-center justify-content-end gap-2">
																			<span data-toggler="off">
																				<a href="#" data-toggler-on class="d-none">
																					<i class="ti ti-star-filled text-warning fs-lg"></i>
																				</a>
																				<a href="#" data-toggler-off>
																					<i class="ti ti-star-filled text-muted fs-lg"></i>
																				</a>
																			</span>
																			<div class="dropdown flex-shrink-0 text-muted">
																				<a href="#" class="dropdown-toggle drop-arrow-none fs-xxl link-reset p-0" data-bs-toggle="dropdown" aria-expanded="false">
																					<i class="ti ti-dots-vertical"></i>
																				</a>
																				<div class="dropdown-menu dropdown-menu-end">
																					<a href="javascript:void(0);" class="dropdown-item">
																						<i class="ti ti-share me-1"></i>
																						Share
																					</a>
																					<a href="javascript:void(0);" class="dropdown-item">
																						<i class="ti ti-link me-1"></i>
																						Get Sharable Link
																					</a>
																					<a href="assets/files/proposal.pdf" download class="dropdown-item">
																						<i class="ti ti-download me-1"></i>
																						Download
																					</a>
																					<a href="javascript:void(0);" class="dropdown-item">
																						<i class="ti ti-pin me-1"></i>
																						Pin
																					</a>
																					<a href="javascript:void(0);" class="dropdown-item">
																						<i class="ti ti-edit me-1"></i>
																						Edit
																					</a>
																					<a href="#" data-table-delete-row class="dropdown-item">
																						<i class="ti ti-trash me-1"></i>
																						Delete
																					</a>
																				</div>
																			</div>
																		</div>
																	</td>
																</tr>
															</tbody>
															<!-- end tbody -->
														</table>
														<!-- end table -->
													</div>
												</div>
											</div>
										</div>

										<div class="tab-pane fade" id="activity" role="tabpanel">
											<div class="timeline">
											
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
								</div>
								<!-- end card-body -->
							</div>
							</div><!-- end locDetailContent -->
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
				var self = this;

				// Show the panel
				document.querySelectorAll('.recordlocationhist').forEach(function (el) {
					el.classList.remove('d-none');
				});

				// Show spinner, hide content
				document.getElementById('locSpinner').classList.remove('d-none');
				document.getElementById('locDetailContent').classList.add('d-none');

				// Highlight active item
				document.querySelectorAll('.loc-trigger').forEach(function (el) {
					el.classList.remove('active');
				});
				self.classList.add('active');

				// After delay, hide spinner and populate content
				setTimeout(function () {
					document.getElementById('locDetailName').textContent    = self.dataset.locName;
					document.getElementById('locDetailAddress').textContent = self.dataset.locAddress;
					document.getElementById('locDetailPhone').textContent   = self.dataset.locPhone;
					document.getElementById('locDetailFax').textContent     = self.dataset.locFax;

					document.getElementById('locSpinner').classList.add('d-none');
					document.getElementById('locDetailContent').classList.remove('d-none');
				}, 100);
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
