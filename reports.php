<?php require_once 'config/db.php'; ?>
<?php
// 		$stmt = $pdo->query("SELECT * FROM API_Input_Order_Locations AS iol
// INNER JOIN API_Input_Orders AS io ON (io.`__kp_API_Input_Order_ID` = iol.`_kf_API_Input_Order_ID`)")
		$stmt = $pdo->prepare("SELECT * FROM API_Input_Orders");
		$orders = $stmt->fetchAll();
		$total = $pdo->query("SELECT COUNT(*) FROM API_Input_Order_Locations")->fetchColumn();
	?>
<!doctype html>
<html lang="en">
	<head>
		<?php $title = "API Reports"; include('partials/title-meta.php'); ?> <?php include('partials/head-css.php'); ?>
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
					<?php $subtitle = "Reports"; $title = "Orders"; include('partials/page-title.php'); ?>

					<div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 align-items-center g-1">
						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-success rounded-circle fs-22">
												<i class="ti ti-check"></i>
											</span>
										</div>
										<h3 class="mb-0"><?= $total; ?></h3>
									</div>
									<p class="mb-0">
										New Request
										<span class="float-end badge badge-soft-success">+3.34%</span>
									</p>
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-warning rounded-circle fs-22">
												<i class="ti ti-alarm-snooze"></i>
											</span>
										</div>
										<h3 class="mb-0">0</h3>
									</div>
									<p class="mb-0">
										Pending Orders
										<!-- <span class="float-end badge badge-soft-warning">-1.12%</span> -->
									</p>
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-danger rounded-circle fs-22">
												<i class="ti ti-x"></i>
											</span>
										</div>
										<h3 class="mb-0">0</h3>
									</div>
									<p class="mb-0">
										Canceled Orders
										<!-- <span class="float-end badge badge-soft-danger">-0.75%</span> -->
									</p>
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-info rounded-circle fs-22">
												<i class="ti ti-shopping-cart"></i>
											</span>
										</div>
										<h3 class="mb-0">0</h3>
									</div>
									<p class="mb-0">
										Completed Orders
										<!-- <span class="float-end badge badge-soft-info">+4.22%</span>
									</p> -->
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-primary rounded-circle fs-22">
												<i class="ti ti-refresh"></i>
											</span>
										</div>
										<h3 class="mb-0">0</h3>
									</div>
									<p class="mb-0">
										Returned Orders
										<!-- <span class="float-end badge badge-soft-primary">+0.56%</span> -->
									</p>
								</div>
							</div>
						</div>
					</div>
					<!-- end row -->

					<div class="row">
						<div class="col-12">
							<div data-table data-table-rows-per-page="10" class="card">
								<div class="card-header border-light justify-content-between">
									<div class="d-flex gap-2">
										<div class="app-search">
											<input data-table-search type="search" class="form-control" placeholder="Search order..." />
											<i class="ti ti-search app-search-icon text-muted"></i>
										</div>

										<button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
									</div>

									<div class="d-flex align-items-center gap-2">
										<span class="me-2 fw-semibold">Filter By:</span>

										<!-- Payment Status Filter -->
										<!-- <div class="app-search">
											<select data-table-filter="payment-status" class="form-select form-control my-1 my-md-0">
												<option value="All">Payment Status</option>
												<option value="Paid">Paid</option>
												<option value="Pending">Pending</option>
												<option value="Failed">Failed</option>
												<option value="Refunded">Refunded</option>
											</select>
											<i class="ti ti-credit-card app-search-icon text-muted"></i>
										</div> -->

										<!-- Delivery Status Filter -->
										<div class="app-search">
											<select data-table-filter="order-status" class="form-select form-control my-1 my-md-0">
												<option value="All">Order Status</option>
												<option value="New">New</option>
												<option value="Processing">Processing</option>
												<option value="Delivered">Delivered</option>
												<option value="Cancelled">Cancelled</option>
											</select>
											<i class="ti ti-truck app-search-icon text-muted"></i>
										</div>

										<!-- Date Range Filter -->
										<div class="app-search">
											<select data-table-range-filter="date" class="form-select form-control my-1 my-md-0">
												<option value="All">Date Range</option>
												<option value="Today">Today</option>
												<option value="Last 7 Days">Last 7 Days</option>
												<option value="Last 30 Days">Last 30 Days</option>
												<option value="This Year">This Year</option>
											</select>
											<i class="ti ti-calendar app-search-icon text-muted"></i>
										</div>

										<!-- Records Per Page -->
										<div>
											<select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
												<option value="5">5</option>
												<option value="10">10</option>
												<option value="15">15</option>
												<option value="20">20</option>
											</select>
										</div>
									</div>

									<div class="d-flex gap-1">
										<a href="#" class="btn btn-primary ms-1"> <i class="ti ti-plus fs-sm me-2"></i> Add Order </a>
									</div>
								</div>
								
								<div class="table-responsive">
									<table class="table table-custom table-centered table-select table-hover w-100 mb-0" id="api-orders">
										<thead class="bg-light align-middle bg-opacity-25 thead-sm">
											<tr class="text-uppercase fs-xxs">
												<th class="ps-3" style="width: 1%">
													<input data-table-select-all class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option" />
												</th>
												<th>Order ID</th>
												<th>Order Date</th>
												<th>Patient Name</th>
												<th>Record Type</th>
												<th>Location Name</th>
												<th>Service</th>
												<th>Status</th>
												<th class="text-center" style="width: 1%">Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($orders as $row): ?>
											<tr>
												<td class="ps-3">
													<input class="form-check-input form-check-input-light fs-14 product-item-check mt-0" type="checkbox" value="option" />
												</td>
												<td><h5 class="fs-sm mb-0 fw-medium"><a href="order_details.php?order_id=<?= htmlspecialchars($row['__kp_API_Input_Order_ID']) ?>" class="link-reset">#<?= htmlspecialchars($row['__kp_API_Input_Order_Location_ID']) ?></a></h5></td>
												<td><?= htmlspecialchars(date('Y-m-d', strtotime($row['API_Input_Timestamp']))) ?></td>
												<td><?= htmlspecialchars($row['Pat_Name']) ?></td>
												<td><?= htmlspecialchars($row['Rec_Type']) ?></td>
												<td><?= htmlspecialchars($row['Loc_Name']) ?></td>
												<td><?= htmlspecialchars($row['_kf_Service_Type_ID_Str']) ?></td>
												<td>Status</td>
												<td>
													<div class="d-flex justify-content-center gap-1">
														<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
														<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
														<a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
													</div>
												</td>
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
								<div class="card-footer border-0">
									<div class="d-flex justify-content-between align-items-center">
										<div data-table-pagination-info="orders"></div>
										<div data-table-pagination></div>
									</div>
								</div>
							</div>
						</div>
						<!-- end col -->
					</div>
					<!-- end row -->

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

		<!-- Custom table -->
		<script src="../source/inspinia5/assets/js/pages/custom-table.js"></script>
	</body>
</html>
