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
											<div class="list-group-item mt-2" id="toggleRecordLocation" style="cursor:pointer;">
												<span class="align-middle"><strong>Records Location</strong></span>
												<i class="ti ti-chevron-down float-end align-middle" id="recordLocationChevron"></i>
											</div>

											<!-- Records Location List -->
											<?php foreach ($locations as $loc): ?>
											<a href="#!" class="list-group-item list-group-item-action">
												<i class="ti ti-tag me-1 text-primary fs-lg align-middle"></i>
												<span class="align-middle" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-html="true" title="<?= htmlspecialchars($loc['Rec_Type']) ?>" data-bs-content="<p class=&quot;mb-1&quot;><i class=&quot;ti ti-map-pin me-1&quot;></i><?= htmlspecialchars($loc['Loc_Address_Street']) ?>, <?= htmlspecialchars($loc['Loc_Address_City']) ?>, <?= htmlspecialchars($loc['Loc_Address_State']) ?> <?= htmlspecialchars($loc['Loc_Address_Zip']) ?></p><p class=&quot;mb-0 text-muted&quot;><i class=&quot;ti ti-phone me-1&quot;></i><?= htmlspecialchars($loc['Loc_Address_Phone']) ?> &nbsp; <i class=&quot;ti ti-printer me-1&quot;></i><?= htmlspecialchars($loc['Loc_Address_Phone_Fax']) ?></p>"><?= htmlspecialchars($loc['Loc_Name']) ?></span>
											</a>
											<?php endforeach; ?>
											<!-- Records Location List -->
											
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
								</div>
							</div>
							<!-- end card -->
						</div>
						<!-- end col-->

						<div class="recordlocationhist col-xl-6 col-lg-12 order-lg-2 order-xl-1 d-none">
							<div class="card">
								<div class="card-body">
									<!-- User Info -->
									<div class="d-flex align-items-center mb-3">
										<img class="me-2 avatar-sm rounded-circle" src="../source/inspinia5/assets/images/users/user-2.jpg" alt="Profile photo of Anika Roy" />
										<div class="w-100">
											<h5 class="m-0">
												<a href="#!" class="link-reset">Anika Roy</a>
											</h5>
											<p class="text-muted mb-0">
												<small>Posted 2 hours ago</small>
											</p>
										</div>
										<!-- Dropdown Menu -->
										<div class="dropdown ms-auto">
											<a href="#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
												<i class="ti ti-dots-vertical fs-lg"></i>
											</a>
											<div class="dropdown-menu dropdown-menu-end">
												<a href="#" class="dropdown-item">
													<i class="ti ti-edit me-2"></i>
													Edit Post
												</a>
												<a href="#" class="dropdown-item">
													<i class="ti ti-trash me-2"></i>
													Delete Post
												</a>
												<a href="#" class="dropdown-item">
													<i class="ti ti-share me-2"></i>
													Share
												</a>
												<a href="#" class="dropdown-item">
													<i class="ti ti-pin me-2"></i>
													Pin to Top
												</a>
												<a href="#" class="dropdown-item">
													<i class="ti ti-flag me-2"></i>
													Report Post
												</a>
											</div>
										</div>
									</div>

									<!-- Event Details -->
									<h5 class="mb-2">
										🌿 Save the Date:
										<strong>Nature Photography Workshop 2025</strong>
									</h5>
									<p class="text-muted mb-2">Join fellow creatives and outdoor enthusiasts for an inspiring weekend of nature photography tips, live field sessions, and community networking.</p>
									<ul class="list-unstyled mb-3">
										<li class="pb-2">
											<strong>Date:</strong>
											Saturday, 14th September 2025
										</li>
										<li class="pb-2">
											<strong>Time:</strong>
											10:00 AM – 4:00 PM
										</li>
										<li>
											<strong>Location:</strong>
											Green Valley National Park (Meeting point to be shared)
										</li>
									</ul>

									<!-- Call to Action -->
									<div class="d-flex gap-2">
										<button class="btn btn-sm btn-outline-primary">
											<i class="ti ti-bell me-1"></i>
											Interested
										</button>
										<button class="btn btn-sm btn-primary">
											<i class="ti ti-user-plus me-1"></i>
											Join Now
										</button>
									</div>
								</div>
							</div>
						</div>
						<!-- end col-->

						<!-- 3rd column -->
						<div class="recordlocationhist col-xl-3 col-lg-6 order-lg-1 order-xl-2 d-none">
							<div class="card">
									<div class="card-header">
										<h4 class="card-title">Activity</h4>
									</div>
									<div class="card-body p-4">
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
							<!-- end card-->

						</div>
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
		// Toggle Records Location columns
		document.getElementById('toggleRecordLocation').addEventListener('click', function () {
			var panels = document.querySelectorAll('.recordlocationhist');
			var chevron = document.getElementById('recordLocationChevron');
			panels.forEach(function (el) { el.classList.toggle('d-none'); });
			chevron.classList.toggle('ti-chevron-down');
			chevron.classList.toggle('ti-chevron-up');
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
