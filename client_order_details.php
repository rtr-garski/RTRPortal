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

					<!-- <div class="row justify-content-center"> -->
					<div class="row">
						<div class="col-12">
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
								</div>
								<!-- end col-xl-3 -->
							</div>
							<!-- end row -->
						</div>
						<!-- end col-12 -->
					</div>

					<!-- detail part -->
					<div class="row">
						<!-- Cart Items -->
						<div class="col-lg-8">
							<div class="card">
								<div class="card-body">
									<div class="ins-wizard" data-wizard>
										<!-- Navigation Tabs -->
										<ul class="nav nav-tabs nav-justified wizard-tabs" data-wizard-nav role="tablist">
											<li class="nav-item">
												<a class="nav-link py-2 active" data-bs-toggle="tab" href="#stuInfo">
													<span class="d-flex align-items-center justify-content-center">
														<i class="ti ti-user-circle fs-24"></i>
														<span class="ms-2 text-truncate">
															<span class="mb-0 lh-base d-block fw-semibold text-body fs-md">Billing Info</span>
														</span>
													</span>
												</a>
											</li>

											<li class="nav-item">
												<a class="nav-link py-2" data-bs-toggle="tab" href="#addrInfo">
													<span class="d-flex align-items-center justify-content-center">
														<i class="ti ti-truck fs-24"></i>
														<span class="ms-2 text-truncate">
															<span class="mb-0 lh-base d-block fw-semibold text-body fs-md">Shipping Info</span>
														</span>
													</span>
												</a>
											</li>

											<li class="nav-item">
												<a class="nav-link py-2" data-bs-toggle="tab" href="#courseInfo">
													<span class="d-flex align-items-center justify-content-center">
														<i class="ti ti-credit-card fs-24"></i>
														<span class="ms-2 text-truncate">
															<span class="mb-0 lh-base d-block fw-semibold text-body fs-md">Payment Info</span>
														</span>
													</span>
												</a>
											</li>

											<li class="nav-item">
												<a class="nav-link py-2" data-bs-toggle="tab" href="#parentInfo">
													<span class="d-flex align-items-center justify-content-center">
														<i class="ti ti-checks fs-24"></i>
														<span class="ms-2 text-truncate">
															<span class="mb-0 lh-base d-block fw-semibold text-body fs-md">Finish</span>
														</span>
													</span>
												</a>
											</li>
										</ul>

										<!-- Content -->
										<div class="tab-content pt-3" data-wizard-content>
											<!-- Step 1: Billing Info -->
											<div class="tab-pane fade show active" id="stuInfo">
												<h5 class="my-1 fs-md">Billing Information</h5>

												<p class="text-muted fs-sm mb-4">Fill the form below in order to send you the order's invoice.</p>
												<form>
													<div class="row">
														<div class="col-md-6">
															<div class="mb-3">
																<label for="billing-first-name" class="form-label">First Name</label>
																<input class="form-control" type="text" placeholder="Enter your first name" id="billing-first-name" />
															</div>
														</div>
														<div class="col-md-6">
															<div class="mb-3">
																<label for="billing-last-name" class="form-label">Last Name</label>
																<input class="form-control" type="text" placeholder="Enter your last name" id="billing-last-name" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-md-6">
															<div class="mb-3">
																<label for="billing-email-address" class="form-label">Email <span class="text-danger">*</span></label>
																<input class="form-control" type="email" placeholder="Enter your email" id="billing-email-address" />
															</div>
														</div>
														<div class="col-md-6">
															<div class="mb-3">
																<label for="billing-phone" class="form-label">Phone <span class="text-danger">*</span></label>
																<input class="form-control" type="text" placeholder="(xx) xxx xxxx xxx" id="billing-phone" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-12">
															<div class="mb-3">
																<label for="billing-address" class="form-label">Address <span class="text-danger">*</span></label>
																<textarea class="form-control" id="billing-address" rows="2" placeholder="Enter your address"></textarea>
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-md-4">
															<div class="mb-3">
																<label for="billing-town-city" class="form-label">Town / City</label>
																<input class="form-control" type="text" placeholder="Enter your city name" id="billing-town-city" />
															</div>
														</div>
														<div class="col-md-4">
															<div class="mb-3">
																<label for="billing-state" class="form-label">State</label>
																<input class="form-control" type="text" placeholder="Enter your state" id="billing-state" />
															</div>
														</div>
														<div class="col-md-4">
															<div class="mb-3">
																<label for="billing-zip-postal" class="form-label">Zip / Postal Code</label>
																<input class="form-control" type="text" placeholder="Enter your zip code" id="billing-zip-postal" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-12">
															<div class="mb-3">
																<label class="form-label">Country</label>
																<select class="form-select">
																	<option value="0">Select Country</option>
																	<option value="AF">Afghanistan</option>
																	<option value="AL">Albania</option>
																	<option value="DZ">Algeria</option>
																	<option value="AS">American Samoa</option>
																	<option value="AD">Andorra</option>
																	<option value="AO">Angola</option>
																	<option value="AI">Anguilla</option>
																	<option value="AQ">Antarctica</option>
																	<option value="AR">Argentina</option>
																	<option value="AM">Armenia</option>
																	<option value="AW">Aruba</option>
																	<option value="AU">Australia</option>
																	<option value="AT">Austria</option>
																	<option value="AZ">Azerbaijan</option>
																	<option value="BS">Bahamas</option>
																	<option value="BH">Bahrain</option>
																	<option value="BD">Bangladesh</option>
																	<option value="BB">Barbados</option>
																	<option value="BY">Belarus</option>
																	<option value="BE">Belgium</option>
																	<option value="BZ">Belize</option>
																	<option value="BJ">Benin</option>
																	<option value="BM">Bermuda</option>
																	<option value="BT">Bhutan</option>
																	<option value="BO">Bolivia</option>
																	<option value="BW">Botswana</option>
																	<option value="BV">Bouvet Island</option>
																	<option value="BR">Brazil</option>
																	<option value="BN">Brunei Darussalam</option>
																	<option value="BG">Bulgaria</option>
																	<option value="BF">Burkina Faso</option>
																	<option value="BI">Burundi</option>
																	<option value="KH">Cambodia</option>
																	<option value="CM">Cameroon</option>
																	<option value="CA">Canada</option>
																	<option value="CV">Cape Verde</option>
																	<option value="KY">Cayman Islands</option>
																	<option value="CF">Central African Republic</option>
																	<option value="TD">Chad</option>
																	<option value="CL">Chile</option>
																	<option value="CN">China</option>
																	<option value="CX">Christmas Island</option>
																	<option value="CC">Cocos (Keeling) Islands</option>
																	<option value="CO">Colombia</option>
																	<option value="KM">Comoros</option>
																	<option value="CG">Congo</option>
																	<option value="CK">Cook Islands</option>
																	<option value="CR">Costa Rica</option>
																	<option value="CI">Cote d'Ivoire</option>
																	<option value="HR">Croatia (Hrvatska)</option>
																	<option value="CU">Cuba</option>
																	<option value="CY">Cyprus</option>
																	<option value="CZ">Czech Republic</option>
																	<option value="DK">Denmark</option>
																	<option value="DJ">Djibouti</option>
																	<option value="DM">Dominica</option>
																	<option value="DO">Dominican Republic</option>
																	<option value="EC">Ecuador</option>
																	<option value="EG">Egypt</option>
																	<option value="SV">El Salvador</option>
																	<option value="GQ">Equatorial Guinea</option>
																	<option value="ER">Eritrea</option>
																	<option value="EE">Estonia</option>
																	<option value="ET">Ethiopia</option>
																	<option value="FK">Falkland Islands (Malvinas)</option>
																	<option value="FO">Faroe Islands</option>
																	<option value="FJ">Fiji</option>
																	<option value="FI">Finland</option>
																	<option value="FR">France</option>
																	<option value="GF">French Guiana</option>
																	<option value="PF">French Polynesia</option>
																	<option value="GA">Gabon</option>
																	<option value="GM">Gambia</option>
																	<option value="GE">Georgia</option>
																	<option value="DE">Germany</option>
																	<option value="GH">Ghana</option>
																	<option value="GI">Gibraltar</option>
																	<option value="GR">Greece</option>
																	<option value="GL">Greenland</option>
																	<option value="GD">Grenada</option>
																	<option value="GP">Guadeloupe</option>
																	<option value="GU">Guam</option>
																	<option value="GT">Guatemala</option>
																	<option value="GN">Guinea</option>
																	<option value="GW">Guinea-Bissau</option>
																	<option value="GY">Guyana</option>
																	<option value="HT">Haiti</option>
																	<option value="HN">Honduras</option>
																	<option value="HK">Hong Kong</option>
																	<option value="HU">Hungary</option>
																	<option value="IS">Iceland</option>
																	<option value="IN">India</option>
																	<option value="ID">Indonesia</option>
																	<option value="IQ">Iraq</option>
																	<option value="IE">Ireland</option>
																	<option value="IL">Israel</option>
																	<option value="IT">Italy</option>
																	<option value="JM">Jamaica</option>
																	<option value="JP">Japan</option>
																	<option value="JO">Jordan</option>
																	<option value="KZ">Kazakhstan</option>
																	<option value="KE">Kenya</option>
																	<option value="KI">Kiribati</option>
																	<option value="KR">Korea, Republic of</option>
																	<option value="KW">Kuwait</option>
																	<option value="KG">Kyrgyzstan</option>
																	<option value="LV">Latvia</option>
																	<option value="LB">Lebanon</option>
																	<option value="LS">Lesotho</option>
																	<option value="LR">Liberia</option>
																	<option value="LY">Libyan Arab Jamahiriya</option>
																	<option value="LI">Liechtenstein</option>
																	<option value="LT">Lithuania</option>
																	<option value="LU">Luxembourg</option>
																	<option value="MO">Macau</option>
																	<option value="MG">Madagascar</option>
																	<option value="MW">Malawi</option>
																	<option value="MY">Malaysia</option>
																	<option value="MV">Maldives</option>
																	<option value="ML">Mali</option>
																	<option value="MT">Malta</option>
																	<option value="MH">Marshall Islands</option>
																	<option value="MQ">Martinique</option>
																	<option value="MR">Mauritania</option>
																	<option value="MU">Mauritius</option>
																	<option value="YT">Mayotte</option>
																	<option value="MX">Mexico</option>
																	<option value="MD">Moldova, Republic of</option>
																	<option value="MC">Monaco</option>
																	<option value="MN">Mongolia</option>
																	<option value="MS">Montserrat</option>
																	<option value="MA">Morocco</option>
																	<option value="MZ">Mozambique</option>
																	<option value="MM">Myanmar</option>
																	<option value="NA">Namibia</option>
																	<option value="NR">Nauru</option>
																	<option value="NP">Nepal</option>
																	<option value="NL">Netherlands</option>
																	<option value="AN">Netherlands Antilles</option>
																	<option value="NC">New Caledonia</option>
																	<option value="NZ">New Zealand</option>
																	<option value="NI">Nicaragua</option>
																	<option value="NE">Niger</option>
																	<option value="NG">Nigeria</option>
																	<option value="NU">Niue</option>
																	<option value="NF">Norfolk Island</option>
																	<option value="MP">Northern Mariana Islands</option>
																	<option value="NO">Norway</option>
																	<option value="OM">Oman</option>
																	<option value="PW">Palau</option>
																	<option value="PA">Panama</option>
																	<option value="PG">Papua New Guinea</option>
																	<option value="PY">Paraguay</option>
																	<option value="PE">Peru</option>
																	<option value="PH">Philippines</option>
																	<option value="PN">Pitcairn</option>
																	<option value="PL">Poland</option>
																	<option value="PT">Portugal</option>
																	<option value="PR">Puerto Rico</option>
																	<option value="QA">Qatar</option>
																	<option value="RE">Reunion</option>
																	<option value="RO">Romania</option>
																	<option value="RU">Russian Federation</option>
																	<option value="RW">Rwanda</option>
																	<option value="KN">Saint Kitts and Nevis</option>
																	<option value="LC">Saint LUCIA</option>
																	<option value="WS">Samoa</option>
																	<option value="SM">San Marino</option>
																	<option value="ST">Sao Tome and Principe</option>
																	<option value="SA">Saudi Arabia</option>
																	<option value="SN">Senegal</option>
																	<option value="SC">Seychelles</option>
																	<option value="SL">Sierra Leone</option>
																	<option value="SG">Singapore</option>
																	<option value="SK">Slovakia (Slovak Republic)</option>
																	<option value="SI">Slovenia</option>
																	<option value="SB">Solomon Islands</option>
																	<option value="SO">Somalia</option>
																	<option value="ZA">South Africa</option>
																	<option value="ES">Spain</option>
																	<option value="LK">Sri Lanka</option>
																	<option value="SH">St. Helena</option>
																	<option value="PM">St. Pierre and Miquelon</option>
																	<option value="SD">Sudan</option>
																	<option value="SR">Suriname</option>
																	<option value="SZ">Swaziland</option>
																	<option value="SE">Sweden</option>
																	<option value="CH">Switzerland</option>
																	<option value="SY">Syrian Arab Republic</option>
																	<option value="TW">Taiwan, Province of China</option>
																	<option value="TJ">Tajikistan</option>
																	<option value="TZ">Tanzania, United Republic of</option>
																	<option value="TH">Thailand</option>
																	<option value="TG">Togo</option>
																	<option value="TK">Tokelau</option>
																	<option value="TO">Tonga</option>
																	<option value="TT">Trinidad and Tobago</option>
																	<option value="TN">Tunisia</option>
																	<option value="TR">Turkey</option>
																	<option value="TM">Turkmenistan</option>
																	<option value="TC">Turks and Caicos Islands</option>
																	<option value="TV">Tuvalu</option>
																	<option value="UG">Uganda</option>
																	<option value="UA">Ukraine</option>
																	<option value="AE">United Arab Emirates</option>
																	<option value="GB">United Kingdom</option>
																	<option value="US">United States</option>
																	<option value="UY">Uruguay</option>
																	<option value="UZ">Uzbekistan</option>
																	<option value="VU">Vanuatu</option>
																	<option value="VE">Venezuela</option>
																	<option value="VN">Viet Nam</option>
																	<option value="VG">Virgin Islands (British)</option>
																	<option value="VI">Virgin Islands (U.S.)</option>
																	<option value="WF">Wallis and Futuna Islands</option>
																	<option value="EH">Western Sahara</option>
																	<option value="YE">Yemen</option>
																	<option value="ZM">Zambia</option>
																	<option value="ZW">Zimbabwe</option>
																</select>
															</div>
														</div>
													</div>
													<!-- end row -->

													<div class="row">
														<div class="col-12">
															<div class="mb-3">
																<div class="form-check">
																	<input type="checkbox" class="form-check-input" id="customCheck2" />
																	<label class="form-check-label" for="customCheck2">Ship to different address ?</label>
																</div>
															</div>

															<div class="mb-3 mt-3">
																<label for="example-textarea" class="form-label">Order Notes:</label>
																<textarea class="form-control" id="example-textarea" rows="3" placeholder="Write some note.."></textarea>
															</div>
														</div>
													</div>
													<!-- end row -->
												</form>
												<div class="d-flex justify-content-end mt-3">
													<button type="button" class="btn btn-primary" data-wizard-next>Proceed to Shipping <i class="ti ti-truck ms-1 fs-lg"></i></button>
												</div>
											</div>

											<!-- Step 2: Address Info -->
											<div class="tab-pane fade" id="addrInfo">
												<h5 class="my-1 fs-md">Saved Address</h5>
												<p class="text-muted fs-sm mb-4">Provide your address details to receive the order invoice.</p>

												<div class="row">
													<div class="col-xl-6">
														<div class="mb-3">
															<div class="form-check card-radio card-radio-bordered">
																<input class="form-check-input" type="radio" name="deli-address" id="add-home" checked />
																<label class="form-check-label p-3 w-100" for="add-home">
																	<span class="fw-bold text-muted mb-1 d-block text-uppercase">Home</span>
																	<span class="fw-semibold d-block">Evelyn Carter</span>
																	2418 Maple Street, Apt 12B<br />
																	Brooklyn, NY 11215<br />
																	<abbr title="Phone">P:</abbr> (917) 432-7784 <br />
																</label>
															</div>
														</div>
													</div>
													<div class="col-xl-6">
														<div class="mb-3">
															<div class="form-check card-radio card-radio-bordered">
																<input class="form-check-input" type="radio" name="deli-address" id="add-office" />
																<label class="form-check-label p-3 w-100" for="add-office">
																	<span class="fw-bold text-muted mb-1 d-block text-uppercase">Office</span>
																	<span class="fw-semibold d-block">Marcus Reynolds</span>
																	500 Howard Street, Floor 8<br />
																	San Francisco, CA 94105<br />
																	<abbr title="Phone">P:</abbr> (415) 392-6400 <br />
																</label>
															</div>
														</div>
													</div>
												</div>

												<h5 class="my-1 fs-md">Add New Address</h5>
												<p class="text-muted fs-sm mb-4">Provide your address details to receive the order invoice.</p>

												<form>
													<div class="row">
														<div class="col-md-6">
															<div class="mb-3">
																<label for="shipping-add-first-name" class="form-label">First Name</label>
																<input class="form-control" type="text" placeholder="Enter your first name" id="shipping-add-first-name" />
															</div>
														</div>
														<div class="col-md-6">
															<div class="mb-3">
																<label for="shipping-add-last-name" class="form-label">Last Name</label>
																<input class="form-control" type="text" placeholder="Enter your last name" id="shipping-add-last-name" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-md-6">
															<div class="mb-3">
																<label for="shipping-add-email-address" class="form-label">Email <span class="text-danger">*</span></label>
																<input class="form-control" type="email" placeholder="Enter your email" id="shipping-add-email-address" />
															</div>
														</div>
														<div class="col-md-6">
															<div class="mb-3">
																<label for="shipping-add-phone" class="form-label">Phone <span class="text-danger">*</span></label>
																<input class="form-control" type="text" placeholder="(xx) xxx xxxx xxx" id="shipping-add-phone" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-12">
															<div class="mb-3">
																<label for="shipping-add-address" class="form-label">Address <span class="text-danger">*</span></label>
																<textarea class="form-control" id="shipping-add-address" rows="2" placeholder="Enter your address"></textarea>
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-md-4">
															<div class="mb-3">
																<label for="shipping-add-town-city" class="form-label">Town / City</label>
																<input class="form-control" type="text" placeholder="Enter your city name" id="shipping-add-town-city" />
															</div>
														</div>
														<div class="col-md-4">
															<div class="mb-3">
																<label for="shipping-add-state" class="form-label">State</label>
																<input class="form-control" type="text" placeholder="Enter your state" id="shipping-add-state" />
															</div>
														</div>
														<div class="col-md-4">
															<div class="mb-3">
																<label for="shipping-add-zip-postal" class="form-label">Zip / Postal Code</label>
																<input class="form-control" type="text" placeholder="Enter your zip code" id="shipping-add-zip-postal" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-12">
															<div class="mb-3">
																<label class="form-label">Country</label>
																<select class="form-select">
																	<option value="0">Select Country</option>
																	<option value="AF">Afghanistan</option>
																	<option value="AL">Albania</option>
																	<option value="DZ">Algeria</option>
																	<option value="AS">American Samoa</option>
																	<option value="AD">Andorra</option>
																	<option value="AO">Angola</option>
																	<option value="AI">Anguilla</option>
																	<option value="AQ">Antarctica</option>
																	<option value="AR">Argentina</option>
																	<option value="AM">Armenia</option>
																	<option value="AW">Aruba</option>
																	<option value="AU">Australia</option>
																	<option value="AT">Austria</option>
																	<option value="AZ">Azerbaijan</option>
																	<option value="BS">Bahamas</option>
																	<option value="BH">Bahrain</option>
																	<option value="BD">Bangladesh</option>
																	<option value="BB">Barbados</option>
																	<option value="BY">Belarus</option>
																	<option value="BE">Belgium</option>
																	<option value="BZ">Belize</option>
																	<option value="BJ">Benin</option>
																	<option value="BM">Bermuda</option>
																	<option value="BT">Bhutan</option>
																	<option value="BO">Bolivia</option>
																	<option value="BW">Botswana</option>
																	<option value="BV">Bouvet Island</option>
																	<option value="BR">Brazil</option>
																	<option value="BN">Brunei Darussalam</option>
																	<option value="BG">Bulgaria</option>
																	<option value="BF">Burkina Faso</option>
																	<option value="BI">Burundi</option>
																	<option value="KH">Cambodia</option>
																	<option value="CM">Cameroon</option>
																	<option value="CA">Canada</option>
																	<option value="CV">Cape Verde</option>
																	<option value="KY">Cayman Islands</option>
																	<option value="CF">Central African Republic</option>
																	<option value="TD">Chad</option>
																	<option value="CL">Chile</option>
																	<option value="CN">China</option>
																	<option value="CX">Christmas Island</option>
																	<option value="CC">Cocos (Keeling) Islands</option>
																	<option value="CO">Colombia</option>
																	<option value="KM">Comoros</option>
																	<option value="CG">Congo</option>
																	<option value="CK">Cook Islands</option>
																	<option value="CR">Costa Rica</option>
																	<option value="CI">Cote d'Ivoire</option>
																	<option value="HR">Croatia (Hrvatska)</option>
																	<option value="CU">Cuba</option>
																	<option value="CY">Cyprus</option>
																	<option value="CZ">Czech Republic</option>
																	<option value="DK">Denmark</option>
																	<option value="DJ">Djibouti</option>
																	<option value="DM">Dominica</option>
																	<option value="DO">Dominican Republic</option>
																	<option value="EC">Ecuador</option>
																	<option value="EG">Egypt</option>
																	<option value="SV">El Salvador</option>
																	<option value="GQ">Equatorial Guinea</option>
																	<option value="ER">Eritrea</option>
																	<option value="EE">Estonia</option>
																	<option value="ET">Ethiopia</option>
																	<option value="FK">Falkland Islands (Malvinas)</option>
																	<option value="FO">Faroe Islands</option>
																	<option value="FJ">Fiji</option>
																	<option value="FI">Finland</option>
																	<option value="FR">France</option>
																	<option value="GF">French Guiana</option>
																	<option value="PF">French Polynesia</option>
																	<option value="GA">Gabon</option>
																	<option value="GM">Gambia</option>
																	<option value="GE">Georgia</option>
																	<option value="DE">Germany</option>
																	<option value="GH">Ghana</option>
																	<option value="GI">Gibraltar</option>
																	<option value="GR">Greece</option>
																	<option value="GL">Greenland</option>
																	<option value="GD">Grenada</option>
																	<option value="GP">Guadeloupe</option>
																	<option value="GU">Guam</option>
																	<option value="GT">Guatemala</option>
																	<option value="GN">Guinea</option>
																	<option value="GW">Guinea-Bissau</option>
																	<option value="GY">Guyana</option>
																	<option value="HT">Haiti</option>
																	<option value="HN">Honduras</option>
																	<option value="HK">Hong Kong</option>
																	<option value="HU">Hungary</option>
																	<option value="IS">Iceland</option>
																	<option value="IN">India</option>
																	<option value="ID">Indonesia</option>
																	<option value="IQ">Iraq</option>
																	<option value="IE">Ireland</option>
																	<option value="IL">Israel</option>
																	<option value="IT">Italy</option>
																	<option value="JM">Jamaica</option>
																	<option value="JP">Japan</option>
																	<option value="JO">Jordan</option>
																	<option value="KZ">Kazakhstan</option>
																	<option value="KE">Kenya</option>
																	<option value="KI">Kiribati</option>
																	<option value="KR">Korea, Republic of</option>
																	<option value="KW">Kuwait</option>
																	<option value="KG">Kyrgyzstan</option>
																	<option value="LV">Latvia</option>
																	<option value="LB">Lebanon</option>
																	<option value="LS">Lesotho</option>
																	<option value="LR">Liberia</option>
																	<option value="LY">Libyan Arab Jamahiriya</option>
																	<option value="LI">Liechtenstein</option>
																	<option value="LT">Lithuania</option>
																	<option value="LU">Luxembourg</option>
																	<option value="MO">Macau</option>
																	<option value="MG">Madagascar</option>
																	<option value="MW">Malawi</option>
																	<option value="MY">Malaysia</option>
																	<option value="MV">Maldives</option>
																	<option value="ML">Mali</option>
																	<option value="MT">Malta</option>
																	<option value="MH">Marshall Islands</option>
																	<option value="MQ">Martinique</option>
																	<option value="MR">Mauritania</option>
																	<option value="MU">Mauritius</option>
																	<option value="YT">Mayotte</option>
																	<option value="MX">Mexico</option>
																	<option value="MD">Moldova, Republic of</option>
																	<option value="MC">Monaco</option>
																	<option value="MN">Mongolia</option>
																	<option value="MS">Montserrat</option>
																	<option value="MA">Morocco</option>
																	<option value="MZ">Mozambique</option>
																	<option value="MM">Myanmar</option>
																	<option value="NA">Namibia</option>
																	<option value="NR">Nauru</option>
																	<option value="NP">Nepal</option>
																	<option value="NL">Netherlands</option>
																	<option value="AN">Netherlands Antilles</option>
																	<option value="NC">New Caledonia</option>
																	<option value="NZ">New Zealand</option>
																	<option value="NI">Nicaragua</option>
																	<option value="NE">Niger</option>
																	<option value="NG">Nigeria</option>
																	<option value="NU">Niue</option>
																	<option value="NF">Norfolk Island</option>
																	<option value="MP">Northern Mariana Islands</option>
																	<option value="NO">Norway</option>
																	<option value="OM">Oman</option>
																	<option value="PW">Palau</option>
																	<option value="PA">Panama</option>
																	<option value="PG">Papua New Guinea</option>
																	<option value="PY">Paraguay</option>
																	<option value="PE">Peru</option>
																	<option value="PH">Philippines</option>
																	<option value="PN">Pitcairn</option>
																	<option value="PL">Poland</option>
																	<option value="PT">Portugal</option>
																	<option value="PR">Puerto Rico</option>
																	<option value="QA">Qatar</option>
																	<option value="RE">Reunion</option>
																	<option value="RO">Romania</option>
																	<option value="RU">Russian Federation</option>
																	<option value="RW">Rwanda</option>
																	<option value="KN">Saint Kitts and Nevis</option>
																	<option value="LC">Saint LUCIA</option>
																	<option value="WS">Samoa</option>
																	<option value="SM">San Marino</option>
																	<option value="ST">Sao Tome and Principe</option>
																	<option value="SA">Saudi Arabia</option>
																	<option value="SN">Senegal</option>
																	<option value="SC">Seychelles</option>
																	<option value="SL">Sierra Leone</option>
																	<option value="SG">Singapore</option>
																	<option value="SK">Slovakia (Slovak Republic)</option>
																	<option value="SI">Slovenia</option>
																	<option value="SB">Solomon Islands</option>
																	<option value="SO">Somalia</option>
																	<option value="ZA">South Africa</option>
																	<option value="ES">Spain</option>
																	<option value="LK">Sri Lanka</option>
																	<option value="SH">St. Helena</option>
																	<option value="PM">St. Pierre and Miquelon</option>
																	<option value="SD">Sudan</option>
																	<option value="SR">Suriname</option>
																	<option value="SZ">Swaziland</option>
																	<option value="SE">Sweden</option>
																	<option value="CH">Switzerland</option>
																	<option value="SY">Syrian Arab Republic</option>
																	<option value="TW">Taiwan, Province of China</option>
																	<option value="TJ">Tajikistan</option>
																	<option value="TZ">Tanzania, United Republic of</option>
																	<option value="TH">Thailand</option>
																	<option value="TG">Togo</option>
																	<option value="TK">Tokelau</option>
																	<option value="TO">Tonga</option>
																	<option value="TT">Trinidad and Tobago</option>
																	<option value="TN">Tunisia</option>
																	<option value="TR">Turkey</option>
																	<option value="TM">Turkmenistan</option>
																	<option value="TC">Turks and Caicos Islands</option>
																	<option value="TV">Tuvalu</option>
																	<option value="UG">Uganda</option>
																	<option value="UA">Ukraine</option>
																	<option value="AE">United Arab Emirates</option>
																	<option value="GB">United Kingdom</option>
																	<option value="US">United States</option>
																	<option value="UY">Uruguay</option>
																	<option value="UZ">Uzbekistan</option>
																	<option value="VU">Vanuatu</option>
																	<option value="VE">Venezuela</option>
																	<option value="VN">Viet Nam</option>
																	<option value="VG">Virgin Islands (British)</option>
																	<option value="VI">Virgin Islands (U.S.)</option>
																	<option value="WF">Wallis and Futuna Islands</option>
																	<option value="EH">Western Sahara</option>
																	<option value="YE">Yemen</option>
																	<option value="ZM">Zambia</option>
																	<option value="ZW">Zimbabwe</option>
																</select>
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="d-flex justify-content-end">
														<button type="button" class="btn btn-sm btn-success">Save</button>
													</div>
												</form>

												<h5 class="my-1 fs-md">Shipping Method</h5>
												<p class="text-muted fs-sm mb-3">Choose your preferred shipping method to receive your order on time.</p>

												<div class="row">
													<div class="col-md-6">
														<div class="border p-3 rounded mb-3 mb-md-0">
															<div class="form-check">
																<input type="radio" id="shippingMethodRadio1" name="shippingOptions" class="form-check-input" checked />
																<label class="form-check-label font-16 fw-bold" for="shippingMethodRadio1">Standard Delivery - FREE</label>
															</div>
															<p class="mb-0 ps-3 pt-1 text-muted">Estimated 5-7 days shipping (Duties and tax may be due upon delivery)</p>
														</div>
													</div>
													<div class="col-md-6">
														<div class="border p-3 rounded">
															<div class="form-check">
																<input type="radio" id="shippingMethodRadio2" name="shippingOptions" class="form-check-input" />
																<label class="form-check-label font-16 fw-bold" for="shippingMethodRadio2">Fast Delivery - $25</label>
															</div>
															<p class="mb-0 ps-3 pt-1 text-muted">Estimated 1-2 days shipping (Duties and tax may be due upon delivery)</p>
														</div>
													</div>
												</div>
												<!-- end row-->

												<div class="d-flex justify-content-between mt-3">
													<button type="button" class="btn btn-secondary" data-wizard-prev>← Billing Info</button>
													<button type="button" class="btn btn-primary" data-wizard-next>Payment Info →</button>
												</div>
											</div>

											<!-- Step 3: Course Info -->
											<div class="tab-pane fade" id="courseInfo">
												<h5 class="my-1 fs-md">Payment Method</h5>
												<p class="text-muted fs-sm mb-3">Select your preferred payment method to complete your purchase securely.</p>

												<!-- Pay with Paypal box-->
												<div class="border p-3 mb-3 rounded">
													<div class="row">
														<div class="col-sm-8">
															<div class="form-check">
																<input type="radio" id="BillingOptRadio2" name="billingOptions" class="form-check-input" />
																<label class="form-check-label font-16 fw-bold" for="BillingOptRadio2">Pay with Paypal</label>
															</div>
															<p class="mb-0 ps-3 pt-1 text-muted">You will be redirected to PayPal website to complete your purchase securely.</p>
														</div>
														<div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
															<img src="assets/images/cards/paypal.svg" height="32" alt="paypal-img" />
														</div>
													</div>
												</div>
												<!-- end Pay with Paypal box-->

												<!-- Credit/Debit Card box-->
												<div class="border p-3 mb-3 rounded">
													<div class="row">
														<div class="col-sm-8">
															<div class="form-check">
																<input type="radio" id="BillingOptRadio1" name="billingOptions" class="form-check-input" checked />
																<label class="form-check-label font-16 fw-bold" for="BillingOptRadio1">Credit / Debit Card</label>
															</div>
															<p class="mb-0 ps-3 pt-1 text-muted">Safe money transfer using your bank account. We support Mastercard, Visa, Discover and Stripe.</p>
														</div>
														<div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
															<img src="assets/images/cards/mastercard.svg" height="32" alt="master-card-img" />
															<img src="assets/images/cards/discover-card.svg" height="32" alt="discover-card-img" />
															<img src="assets/images/cards/visa.svg" height="32" alt="visa-card-img" />
															<img src="assets/images/cards/stripe.svg" height="32" alt="stripe-card-img" />
														</div>
													</div>
													<!-- end row -->
													<div class="row mt-4">
														<div class="col-md-12">
															<div class="alert alert-warning fs-xs py-2">Enjoy an extra <span class="fw-bold">10% discount</span> when you pay with your <span class="fw-bold">Credit Card</span>.</div>

															<div class="mb-3">
																<label for="card-number" class="form-label">Card Number</label>
																<input type="text" id="card-number" class="form-control" data-toggle="input-mask" data-mask-format="0000 0000 0000 0000" placeholder="4242 4242 4242 4242" />
															</div>
														</div>
													</div>
													<!-- end row -->
													<div class="row">
														<div class="col-md-6">
															<div class="mb-3 mb-md-0">
																<label for="card-name-on" class="form-label">Name on card</label>
																<input type="text" id="card-name-on" class="form-control" placeholder="Master Dhanu" />
															</div>
														</div>
														<div class="col-md-3">
															<div class="mb-3 mb-md-0">
																<label for="card-expiry-date" class="form-label">Expiry date</label>
																<input type="text" id="card-expiry-date" class="form-control" data-toggle="input-mask" data-mask-format="00/00" placeholder="MM/YY" />
															</div>
														</div>
														<div class="col-md-3">
															<div class="mb-0">
																<label for="card-cvv" class="form-label">CVV code</label>
																<input type="text" id="card-cvv" class="form-control" data-toggle="input-mask" data-mask-format="000" placeholder="012" />
															</div>
														</div>
													</div>
													<!-- end row -->
												</div>
												<!-- end Credit/Debit Card box-->

												<!-- Pay with Payoneer box-->
												<div class="border p-3 mb-3 rounded">
													<div class="row">
														<div class="col-sm-8">
															<div class="form-check">
																<input type="radio" id="BillingOptRadio3" name="billingOptions" class="form-check-input" />
																<label class="form-check-label font-16 fw-bold" for="BillingOptRadio3">Pay with Payoneer</label>
															</div>
															<p class="mb-0 ps-3 pt-1 text-muted">You will be redirected to Payoneer website to complete your purchase securely.</p>
														</div>
														<div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
															<img src="assets/images/cards/payoneer.svg" height="32" alt="payoneer" />
														</div>
													</div>
												</div>
												<!-- end Pay with Payoneer box-->

												<!-- Cash on Delivery box-->
												<div class="border p-3 mb-3 rounded">
													<div class="row">
														<div class="col-sm-8">
															<div class="form-check">
																<input type="radio" id="BillingOptRadio4" name="billingOptions" class="form-check-input" />
																<label class="form-check-label font-16 fw-bold" for="BillingOptRadio4">Cash on Delivery</label>
															</div>
															<p class="mb-0 ps-3 pt-1 text-muted">Pay with cash when your order is delivered.</p>
														</div>
														<div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
															<img src="assets/images/cards/cod.png" height="24" alt="COD-img" />
														</div>
													</div>
												</div>
												<!-- end Cash on Delivery box-->

												<div class="d-flex justify-content-between mt-3">
													<button type="button" class="btn btn-secondary" data-wizard-prev>← Shipping Info</button>
													<button type="button" class="btn btn-primary" data-wizard-next>Proceed →</button>
												</div>
											</div>

											<!-- Step 4: Parent Info -->
											<div class="tab-pane fade" id="parentInfo">
												<div class="p-4">
													<div class="d-flex align-items-center gap-3 mb-3">
														<div class="avatar-md flex-shrink-0">
															<span class="avatar-title text-bg-success rounded-circle fs-22">
																<i class="ti ti-check"></i>
															</span>
														</div>
														<div>
															<p class="text-muted mb-0">Order #234000</p>
															<h4 class="m-0">Thank you for your order!</h4>
														</div>
														<a href="#" class="link-reset text-decoration-underline link-offset-2 fw-semibold ms-auto">Track Order</a>
													</div>

													<hr class="border-top border-dashed" />

													<div class="mt-4">
														<h6 class="text-uppercase text-muted fw-bold">Delivery Address</h6>
														<span class="fw-semibold d-block mb-1">Marcus Reynolds</span>
														500 Howard Street, Floor 8<br />
														San Francisco, CA 94105<br />
														<abbr title="Phone">P:</abbr> (415) 392-6400 <br />
													</div>

													<div class="mt-4">
														<h6 class="text-uppercase text-muted fw-bold">Payment Info</h6>
														<p>Credit card: xxxx xxxx xxxx 8521</p>
													</div>

													<div class="mt-4">
														<a href="#!" class="btn btn-success"><i class="ti ti-download me-1"></i> Download Invoice</a>
														<a href="apps-ecommerce-products-grid.html" class="btn btn-link fw-semibold text-muted"><i class="ti ti-arrow-left me-1"></i> Continue Shopping</a>
													</div>

													<div class="p-4 alert alert-info mt-4 mb-0">
														<h4 class="text-center pb-2 mb-1 text-dark">🎁 Great News! You’ve unlocked 25% off your next order!</h4>
														<p class="text-center fst-italic mb-4">Apply the code below at checkout or find it anytime in your account.</p>
														<div class="d-flex gap-2 mx-auto">
															<input type="text" class="form-control border-0" value="SAVE25NOW" readonly />
															<button type="button" class="btn btn-dark text-nowrap">Copy Code</button>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!-- tab-content -->
									</div>
									<!-- ins-wizard -->
								</div>
							</div>
						</div>

						<!-- Order Summary -->
						<div class="col-lg-4">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">Order Summary</h4>
									<span class="badge badge-soft-success ms-auto">03 Items</span>
								</div>
								<div class="card-body">
									<div class="d-flex align-items-center gap-3 mb-3">
										<img src="assets/images/products/2.png" class="me-1 rounded" width="42" alt="MacBook Air" />
										<div>
											<p class="mb-1 fw-semibold">
												<a href="apps-ecommerce-product-details.html" class="link-reset">Apple MacBook Air M3 13”</a>
											</p>
											<p class="text-muted d-block mb-0">1 x $1,199</p>
										</div>
										<h5 class="mb-0 ms-auto">$1,199</h5>
									</div>

									<div class="d-flex align-items-center gap-3 mb-3">
										<img src="assets/images/products/5.png" class="me-1 rounded" width="42" alt="Sony Headphones" />
										<div>
											<p class="mb-1 fw-semibold">
												<a href="apps-ecommerce-product-details.html" class="link-reset">Sony WH-1000XM5 Wireless Headphones</a>
											</p>
											<p class="text-muted d-block mb-0">1 x $349</p>
										</div>
										<h5 class="mb-0 ms-auto">$349</h5>
									</div>

									<div class="d-flex align-items-center gap-3">
										<img src="assets/images/products/7.png" class="me-1 rounded" width="42" alt="Apple Watch" />
										<div>
											<p class="mb-1 fw-semibold">
												<a href="apps-ecommerce-product-details.html" class="link-reset">Apple Watch Series 9 GPS</a>
											</p>
											<p class="text-muted d-block mb-0">1 x $399</p>
										</div>
										<h5 class="mb-0 ms-auto">$399</h5>
									</div>

									<hr />

									<ul class="list-unstyled mb-0">
										<li class="d-flex justify-content-between mb-2">
											<span class="text-muted">Subtotal:</span>
											<span>$1,947.00</span>
										</li>
										<li class="d-flex justify-content-between mb-2">
											<span class="text-muted">Discount:</span>
											<span class="text-danger">- $120.00</span>
										</li>
										<li class="d-flex justify-content-between mb-2">
											<span class="text-muted">Tax collected:</span>
											<span>$65.85</span>
										</li>
										<li class="d-flex justify-content-between border-bottom pb-3 mb-3">
											<span class="text-muted">Shipping:</span>
											<span>Free</span>
										</li>
										<li class="d-flex justify-content-between align-items-center">
											<h6 class="text-uppercase text-muted mb-0">Estimated total:</h6>
											<h4 class="fw-bold mb-0">$1,892.85</h4>
										</li>
									</ul>
								</div>
							</div>
						</div>
					
					</div>
					
					<!-- 3 column -->
					<div class="container-xxl">
                        <div class="row">
                            <div class="col-xl-3 col-lg-6 order-lg-1 order-xl-1">
                                <div class="card card-top-sticky">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-2 position-relative">
                                                <img src="assets/images/users/user-3.jpg" alt="avatar" class="rounded" width="42" height="42" />
                                            </div>
                                            <div>
                                                <h5 class="mb-0 d-flex align-items-center">
                                                    <a href="#!" class="link-reset">Damian D.</a>
                                                    <img src="assets/images/flags/us.svg" alt="US" class="ms-2 rounded-circle" height="16" />
                                                </h5>
                                                <p class="text-muted mb-0">Content Creator</p>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="dropdown">
                                                    <a href="#" class="btn btn-icon btn-ghost-light text-muted" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical fs-24"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="#">View Profile</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#">Send Message</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#">Copy Profile Link</a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider" />
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#">Edit Profile</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#">Block User</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#">Report User</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="list-group list-group-flush list-custom mt-3">
                                            <a href="#!" class="list-group-item list-group-item-action active">
                                                <i class="ti ti-smart-home me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">News Feed</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-message-circle me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Messages</span>
                                                <span class="badge align-middle bg-danger-subtle fs-xxs text-danger float-end">5</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-users me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Friends</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-bell me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Notifications</span>
                                                <span class="badge align-middle bg-warning-subtle text-warning fs-xxs float-end">12</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-category me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Groups</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-book me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Pages</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-calendar-event me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Events</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-settings me-1 opacity-75 fs-lg align-middle"></i>
                                                <span class="align-middle">Settings</span>
                                            </a>

                                            <div class="list-group-item mt-2">
                                                <span class="align-middle">Categories</span>
                                            </div>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-tag me-1 text-primary fs-lg align-middle"></i>
                                                <span class="align-middle">Technology</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-tag me-1 text-success fs-lg align-middle"></i>
                                                <span class="align-middle">Travel</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-tag me-1 text-danger fs-lg align-middle"></i>
                                                <span class="align-middle">Lifestyle</span>
                                            </a>

                                            <a href="#!" class="list-group-item list-group-item-action">
                                                <i class="ti ti-tag me-1 fs-lg align-middle text-info"></i>
                                                <span class="align-middle">Photography</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card-->
                            </div>
                            <!-- end col-->
                            <div class="col-xl-6 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="mb-2">What's on your mind?</h5>

                                        <!-- Post Form -->
                                        <form action="#">
                                            <textarea rows="3" class="form-control" placeholder="Share your thoughts..."></textarea>

                                            <div class="d-flex pt-2 justify-content-between align-items-center">
                                                <div class="d-flex gap-1">
                                                    <a href="#" class="btn btn-sm btn-icon btn-light">
                                                        <i class="ti ti-user fs-md"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-icon btn-light">
                                                        <i class="ti ti-map-pin fs-md"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-icon btn-light">
                                                        <i class="ti ti-camera fs-md"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-icon btn-light">
                                                        <i class="ti ti-mood-smile fs-md"></i>
                                                    </a>
                                                </div>

                                                <button type="submit" class="btn btn-dark btn-sm">Post</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- end card-body-->
                                </div>
                                <!-- end card-->

                                <div class="card">
                                    <div class="card-body text-center">
                                        <!-- Icon and Title -->
                                        <h1 class="mb-2">🏆</h1>
                                        <h4 class="mb-1 fw-semibold">Congratulations, Damian D.! 🎉</h4>

                                        <!-- Achievement Message -->
                                        <p class="text-muted fst-italic mb-3">
                                            Congratulations! You’ve reached
                                            <strong>5,000 subscribers</strong>
                                            ! Your community is growing fast!
                                        </p>

                                        <!-- Stats (optional) -->
                                        <div class="d-flex justify-content-center mb-3">
                                            <div class="me-4 text-center">
                                                <h6 class="mb-0">Posts</h6>
                                                <span class="fw-bold">250</span>
                                            </div>
                                            <div class="me-4 text-center">
                                                <h6 class="mb-0">Likes</h6>
                                                <span class="fw-bold">15,200</span>
                                            </div>
                                            <div class="text-center">
                                                <h6 class="mb-0">Subscribers</h6>
                                                <span class="fw-bold">5,000</span>
                                            </div>
                                        </div>

                                        <!-- Call to Action -->
                                        <button class="btn btn-sm btn-outline-success me-2">
                                            <i class="ti ti-share me-1"></i>
                                            Share Achievement
                                        </button>
                                        <a href="#!" class="btn btn-sm btn-primary">
                                            <i class="ti ti-user me-1"></i>
                                            View Profile
                                        </a>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body pb-2">
                                        <div class="d-flex align-items-center mb-2">
                                            <img class="me-2 avatar-md rounded-circle" src="assets/images/users/user-10.jpg" alt="Generic placeholder image" />
                                            <div class="w-100">
                                                <h5 class="m-0">
                                                    <a href="#!" class="link-reset">Sophia Martinez</a>
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    <small>about 5 minutes ago</small>
                                                </p>
                                            </div>
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
                                        <p>Story inspired by the beauty of changing seasons — a nature-themed animation coming soon!</p>

                                        <div class="row g-1">
                                            <!-- Left tall image -->
                                            <div class="col-md-6">
                                                <img src="assets/images/gallery/10.jpg" class="img-fluid w-100 h-100 rounded" style="aspect-ratio: 3/4; object-fit: cover" alt="Tall Image" />
                                            </div>

                                            <!-- Right column with two stacked images -->
                                            <div class="col-md-6 d-flex flex-column gap-1">
                                                <img src="assets/images/gallery/2.jpg" class="img-fluid w-100 rounded" style="aspect-ratio: 4/3; object-fit: cover" alt="Top Right" />
                                                <img src="assets/images/gallery/3.jpg" class="img-fluid w-100 rounded" style="aspect-ratio: 4/3; object-fit: cover" alt="Bottom Right" />
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <a href="javascript: void(0);" class="btn btn-sm fs-sm btn-link text-muted">
                                                <i class="ti ti-corner-up-left me-1"></i>
                                                Reply
                                            </a>
                                            <span class="btn btn-sm fs-sm btn-link text-muted" data-toggler="on">
                                                <span data-toggler-on class="align-middle">
                                                    <i class="ti ti-heart-filled text-danger"></i>
                                                    Liked!
                                                </span>
                                                <span data-toggler-off class="d-none align-middle">
                                                    <i class="ti ti-heart text-muted"></i>
                                                    Like
                                                </span>
                                            </span>
                                            <a href="javascript: void(0);" class="btn btn-sm fs-sm btn-link text-muted">
                                                <i class="ti ti-share me-1"></i>
                                                Share
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body pb-2">
                                        <div class="d-flex align-items-center mb-2">
                                            <img class="me-2 avatar-sm rounded-circle" src="assets/images/users/user-4.jpg" alt="Generic placeholder image" />
                                            <div class="w-100">
                                                <h5 class="m-0">
                                                    <a href="#!" class="link-reset">Liam Anderson</a>
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    <small>about 30 minutes ago</small>
                                                </p>
                                            </div>
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

                                        <div class="fs-16 text-center mt-3 mb-4 fst-italic">
                                            <i class="ti ti-quote fs-20"></i>
                                            Spent the weekend exploring the local trails! Captured some amazing nature shots and can’t wait to post them soon. 🌿📸
                                        </div>

                                        <div class="bg-light-subtle mx-n3 p-3 border-top border-bottom border-dashed">
                                            <div class="d-flex align-items-start">
                                                <img class="me-2 avatar-sm rounded-circle" src="assets/images/users/user-5.jpg" alt="Generic placeholder image" />
                                                <div class="w-100">
                                                    <h5 class="mt-0 mb-1">
                                                        <a href="#!" class="link-reset">Ethan Reynolds</a>
                                                        <small class="text-muted fw-normal float-end">20 minutes ago</small>
                                                    </h5>
                                                    Loved your recent project! Really curious to see how you implemented the animations.
                                                    <br />
                                                    <a href="javascript:void(0);" class="text-muted font-13 d-inline-block mt-2">
                                                        <i class="ti ti-corner-up-left"></i>
                                                        Reply
                                                    </a>

                                                    <div class="d-flex align-items-start mt-3">
                                                        <a class="pe-2" href="#">
                                                            <img src="assets/images/users/user-6.jpg" class="avatar-sm rounded-circle" alt="Generic placeholder image" />
                                                        </a>
                                                        <div class="w-100">
                                                            <h5 class="mt-0 mb-1">
                                                                <a href="#!" class="link-reset">Mia Thompson</a>
                                                                <small class="text-muted fw-normal float-end">12 minutes ago</small>
                                                            </h5>
                                                            I created something similar in Angular last month — would love to swap tips!
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start mt-3">
                                                <a class="pe-2" href="#">
                                                    <img src="assets/images/users/user-3.jpg" class="rounded-circle" alt="Generic placeholder image" height="31" />
                                                </a>
                                                <div class="w-100">
                                                    <input type="text" id="simpleinput" class="form-control form-control-sm" placeholder="Add a comment..." />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <a href="javascript: void(0);" class="btn btn-sm fs-sm btn-link text-muted">
                                                <i class="ti ti-corner-up-left me-1"></i>
                                                Reply
                                            </a>
                                            <span class="btn btn-sm fs-sm btn-link text-muted" data-toggler="off">
                                                <span data-toggler-on class="d-none align-middle">
                                                    <i class="ti ti-heart-filled text-danger"></i>
                                                    Liked!
                                                </span>
                                                <span data-toggler-off class="align-middle">
                                                    <i class="ti ti-heart text-muted"></i>
                                                    Likes (45)
                                                </span>
                                            </span>
                                            <a href="javascript: void(0);" class="btn btn-sm fs-sm btn-link text-muted">
                                                <i class="ti ti-share me-1"></i>
                                                Share
                                            </a>
                                        </div>
                                    </div>
                                    <!-- end card-body-->
                                </div>
                                <!-- end card-->

                                <div class="card">
                                    <div class="card-body">
                                        <!-- User Info -->
                                        <div class="d-flex align-items-center mb-3">
                                            <img class="me-2 avatar-sm rounded-circle" src="assets/images/users/user-2.jpg" alt="Profile photo of Anika Roy" />
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

                                <div class="card">
                                    <div class="card-body pb-2">
                                        <div class="d-flex align-items-center mb-2">
                                            <img class="me-2 avatar-sm rounded-circle" src="assets/images/users/user-1.jpg" alt="Profile photo of Anika Roy" />
                                            <div class="w-100">
                                                <h5 class="m-0">
                                                    <a href="#!" class="link-reset">Damian D.</a>
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    <small>Posted 2 hours ago</small>
                                                </p>
                                            </div>
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

                                        <p>Sharing a couple of timelapses from my recent Iceland trip. Let me know which one you like most!</p>

                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                                    <iframe src="https://player.vimeo.com/video/1084537" allowfullscreen></iframe>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                                    <iframe src="https://player.vimeo.com/video/76979871" allowfullscreen></iframe>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <a href="javascript: void(0);" class="btn btn-sm fs-sm btn-link text-muted">
                                                <i class="ti ti-corner-up-left me-1"></i>
                                                Reply
                                            </a>
                                            <span class="btn btn-sm fs-sm btn-link text-muted" data-toggler="on">
                                                <span data-toggler-on class="align-middle">
                                                    <i class="ti ti-heart-filled text-danger"></i>
                                                    Liked!
                                                </span>
                                                <span data-toggler-off class="d-none align-middle">
                                                    <i class="ti ti-heart text-muted"></i>
                                                    Like
                                                </span>
                                            </span>
                                            <a href="javascript: void(0);" class="btn btn-sm fs-sm btn-link text-muted">
                                                <i class="ti ti-share me-1"></i>
                                                Share
                                            </a>
                                        </div>
                                    </div>
                                    <!-- end card-body-->
                                </div>
                                <!-- end card-->

                                <div class="card">
                                    <div class="card-body">
                                        <!-- User Info -->
                                        <div class="d-flex align-items-center mb-2">
                                            <img class="me-2 avatar-sm rounded-circle" src="assets/images/users/user-6.jpg" alt="Profile photo of David Kim" />
                                            <div class="w-100">
                                                <h5 class="m-0">
                                                    <a href="#!" class="link-reset">David Kim</a>
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    <small>Posted 10 hours ago</small>
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

                                        <!-- Poll Content -->
                                        <h5 class="mb-3">🔥 Quick Poll: What’s your go-to front-end framework in 2025?</h5>
                                        <p class="text-muted">We’re gathering developer preferences for our next project. Cast your vote below! 💻</p>

                                        <!-- Poll Form -->
                                        <form>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="radio" name="framework_poll" id="optionReact" />
                                                <label class="form-check-label" for="optionReact">React (Meta)</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="radio" name="framework_poll" id="optionVue" />
                                                <label class="form-check-label" for="optionVue">Vue.js (Evan You)</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="radio" name="framework_poll" id="optionAngular" />
                                                <label class="form-check-label" for="optionAngular">Angular (Google)</label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="radio" name="framework_poll" id="optionSvelte" />
                                                <label class="form-check-label" for="optionSvelte">Svelte (Emerging Favorite)</label>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Submit Vote</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-center gap-2 p-3 mb-3">
                                    <strong>Loading...</strong>
                                    <div class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"></div>
                                </div>
                            </div>
                            <!-- end col-->
                            <div class="col-xl-3 col-lg-6 order-lg-1 order-xl-2">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Activity</h5>
                                            <a href="#" class="link-reset fs-sm">See all</a>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted text-uppercase">Stories About You</small>
                                            <div class="d-flex align-items-center mt-2">
                                                <img src="assets/images/users/user-7.jpg" class="rounded-circle me-2" width="32" height="32" alt="mention" />
                                                <div>
                                                    <strong>Mentions</strong>
                                                    <br />
                                                    <span class="text-muted fs-xs">3 stories mention you</span>
                                                </div>
                                            </div>
                                        </div>

                                        <span class="text-muted fs-xs fw-bold text-uppercase">New</span>
                                        <ul class="list-unstyled mt-2 mb-0">
                                            <li class="d-flex align-items-center py-1">
                                                <img src="assets/images/users/user-8.jpg" class="rounded-circle me-2" width="36" height="36" alt="jenny.w" />
                                                <div class="flex-grow-1">
                                                    <strong>jenny.w</strong>
                                                    started following you
                                                    <br />
                                                    <span class="text-muted fs-xs">2m ago</span>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ti ti-user-plus fs-lg"></i>
                                                </div>
                                            </li>

                                            <li class="d-flex align-items-center py-1">
                                                <img src="assets/images/users/user-9.jpg" class="rounded-circle me-2" width="36" height="36" alt="daniel92" />
                                                <div class="flex-grow-1">
                                                    <strong>daniel92</strong>
                                                    commented on your post
                                                    <br />
                                                    <span class="text-muted fs-xs">3m ago</span>
                                                </div>
                                                <div>
                                                    <img src="assets/images/gallery/1.jpg" class="rounded" width="32" height="32" alt="commented" />
                                                </div>
                                            </li>

                                            <li class="d-flex align-items-center py-1">
                                                <img src="assets/images/users/user-10.jpg" class="rounded-circle me-2" width="36" height="36" alt="amelie.design" />
                                                <div class="flex-grow-1">
                                                    <strong>amelie.design</strong>
                                                    liked your story
                                                    <br />
                                                    <span class="text-muted fs-xs">4m ago</span>
                                                </div>
                                                <div>
                                                    <img src="assets/images/gallery/2.jpg" class="rounded" width="32" height="32" alt="liked" />
                                                </div>
                                            </li>

                                            <li class="d-flex align-items-center py-1">
                                                <img src="assets/images/users/user-5.jpg" class="rounded-circle me-2" width="36" height="36" alt="johnny_dev" />
                                                <div class="flex-grow-1">
                                                    <strong>johnny_dev</strong>
                                                    started following you
                                                    <br />
                                                    <span class="text-muted fs-xs">6m ago</span>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ti ti-user-plus fs-lg"></i>
                                                </div>
                                            </li>

                                            <li class="d-flex align-items-center py-1">
                                                <img src="assets/images/users/user-6.jpg" class="rounded-circle me-2" width="36" height="36" alt="art.gal" />
                                                <div class="flex-grow-1">
                                                    <strong>art.gal</strong>
                                                    liked your post
                                                    <br />
                                                    <span class="text-muted fs-xs">8m ago</span>
                                                </div>
                                                <div>
                                                    <img src="assets/images/gallery/3.jpg" class="rounded" width="32" height="32" alt="liked" />
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header justify-content-between align-items-center border-dashed">
                                        <h4 class="card-title mb-0">Trending</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical fs-lg"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="#" class="dropdown-item">
                                                    <i class="ti ti-refresh me-2"></i>
                                                    Refresh Feed
                                                </a>
                                                <a href="#" class="dropdown-item">
                                                    <i class="ti ti-settings me-2"></i>
                                                    Manage Topics
                                                </a>
                                                <a href="#" class="dropdown-item">
                                                    <i class="ti ti-alert-circle me-2"></i>
                                                    Report Issue
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <!-- Trending Items -->
                                        <div class="d-flex mb-3">
                                            <i class="ti ti-trending-up text-primary me-2 mt-1"></i>
                                            <a href="#!" class="link-reset text-decoration-none">
                                                <strong>Golden Globes:</strong>
                                                The 27 Best moments from the Golden Globe Awards
                                            </a>
                                        </div>

                                        <div class="d-flex mb-3">
                                            <i class="ti ti-trending-up text-primary me-2 mt-1"></i>
                                            <a href="#!" class="link-reset text-decoration-none">
                                                <strong>World Cricket:</strong>
                                                India has won ICC T20 World Cup Yesterday
                                            </a>
                                        </div>

                                        <div class="d-flex mb-3">
                                            <i class="ti ti-trending-up text-primary me-2 mt-1"></i>
                                            <a href="#!" class="link-reset text-decoration-none">
                                                <strong>Antarctica:</strong>
                                                Melting of Totten Glacier could cause high risk to areas near by sea
                                            </a>
                                        </div>

                                        <div class="d-flex">
                                            <i class="ti ti-trending-up text-primary me-2 mt-1"></i>
                                            <a href="#!" class="link-reset text-decoration-none">
                                                <strong>Global Tournament:</strong>
                                                America has won Football match Yesterday
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card-->

                                <div class="card">
                                    <!-- Card Header -->
                                    <div class="card-header justify-content-between align-items-center border-dashed">
                                        <h4 class="card-title mb-0">Requests</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical fs-lg"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="#" class="dropdown-item">
                                                    <i class="ti ti-check me-2"></i>
                                                    Mark All as Read
                                                </a>
                                                <a href="#" class="dropdown-item">
                                                    <i class="ti ti-trash me-2"></i>
                                                    Clear All
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="card-body">
                                        <!-- Request 1: Collaboration -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-start">
                                                <img src="assets/images/users/user-3.jpg" alt="Emily Zhang" class="avatar-xs rounded-circle me-2" />
                                                <div>
                                                    <p class="mb-1">
                                                        <strong>Emily Zhang</strong>
                                                        requested to collaborate on your design project.
                                                        <span class="badge bg-primary ms-1">New</span>
                                                    </p>
                                                    <small class="text-muted">2 minutes ago</small>
                                                </div>
                                            </div>
                                            <button class="btn btn-sm py-0 px-1 btn-default">Accept</button>
                                        </div>

                                        <!-- Request 2: Feature Suggestion -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-start gap-2">
                                                <div class="avatar-xs flex-shrink-0">
                                                    <span class="avatar-title text-bg-info rounded-circle">
                                                        <i class="ti ti-rocket"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="mb-1">
                                                        <strong>New Feature:</strong>
                                                        Suggestion for dark mode support.
                                                        <span class="badge bg-warning ms-1">Pending</span>
                                                    </p>
                                                    <small class="text-muted">10 minutes ago</small>
                                                </div>
                                            </div>
                                            <button class="btn btn-sm py-0 px-1 btn-default">Review</button>
                                        </div>

                                        <!-- Request 3: Feedback -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-start">
                                                <img src="assets/images/users/user-6.jpg" alt="John Doe" class="avatar-xs rounded-circle me-2" />
                                                <div>
                                                    <p class="mb-1">
                                                        <strong>Client Feedback:</strong>
                                                        John Doe left a review on your dashboard.
                                                        <span class="badge bg-secondary ms-1">Feedback</span>
                                                    </p>
                                                    <small class="text-muted">30 minutes ago</small>
                                                </div>
                                            </div>
                                            <button class="btn btn-sm py-0 px-1 btn-default">Respond</button>
                                        </div>

                                        <!-- Request 4: Bug Report -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-start gap-2">
                                                <div class="avatar-xs flex-shrink-0">
                                                    <span class="avatar-title text-bg-primary rounded-circle">
                                                        <i class="ti ti-bug"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="mb-1">
                                                        <strong>Bug Report:</strong>
                                                        Login form issue on Safari mobile.
                                                        <span class="badge bg-danger ms-1">Urgent</span>
                                                    </p>
                                                    <small class="text-muted">1 hour ago</small>
                                                </div>
                                            </div>
                                            <button class="btn btn-sm py-0 px-1 btn-default">View</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header justify-content-between align-items-center border-dashed">
                                        <h4 class="card-title mb-0">Featured Video For You</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical fs-lg"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="#" class="dropdown-item">Watch Later</a>
                                                <a href="#" class="dropdown-item">Report Video</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="ratio ratio-16x9 rounded overflow-hidden">
                                            <iframe src="https://player.vimeo.com/video/357274789" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                    <!-- end card-body-->
                                </div>
                                <!-- end card-->
                            </div>
                            <!-- end col-->
                        </div>
                        <!-- end-->
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
