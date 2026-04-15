<?php require_once 'config/db.php'; ?>
<?php
// 		$stmt = $pdo->query("SELECT * FROM API_Input_Order_Locations AS iol
// INNER JOIN API_Input_Orders AS io ON (io.`__kp_API_Input_Order_ID` = iol.`_kf_API_Input_Order_ID`)")
		$stmt = $pdo->query("
			SELECT io.*,
				(SELECT COUNT(*) FROM API_Input_Order_Locations iol
				 WHERE iol._kf_API_Input_Order_ID = io.__kp_API_Input_Order_ID) AS location_count
			FROM API_Input_Orders io
		");
		$orders = $stmt->fetchAll();
		$total = count($orders);

		// --- Lookup client_name from pdo2.sys_client ---
		$clientIds = array_unique(array_filter(array_column($orders, '_kf_Client_ID')));
		$clientMap = [];
		if (!empty($clientIds)) {
			$placeholders = implode(',', array_fill(0, count($clientIds), '?'));
			$cStmt = $pdo2->prepare("SELECT client_sysid, client_name FROM sys_client WHERE client_sysid IN ($placeholders)");
			$cStmt->execute(array_values($clientIds));
			foreach ($cStmt->fetchAll() as $c) {
				$clientMap[$c['client_sysid']] = $c['client_name'];
			}
		}

		$statuses = [
			['label' => 'New Request',  'class' => 'badge-soft-secondary'],
			['label' => 'In Review',    'class' => 'badge-soft-info'],
			['label' => 'In Progress',  'class' => 'badge-soft-success'],
			['label' => 'Canceled',     'class' => 'badge-soft-danger'],
			['label' => 'Completed',    'class' => 'badge-soft-primary'],
		];

		$counts = ['New Request' => 0, 'In Review' => 0, 'In Progress' => 0, 'Canceled' => 0, 'Completed' => 0];

		foreach ($orders as &$row) {
			$s = $statuses[array_rand($statuses)];
			$row['_status'] = $s;
			$counts[$s['label']]++;
			$row['_client_name'] = $clientMap[$row['_kf_Client_ID']] ?? '—';
		}
		unset($row);
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
											<span class="avatar-title text-bg-secondary rounded-circle fs-22">
												<i class="ti ti-file-plus"></i>
											</span>
										</div>
										<h3 class="mb-0"><?= $counts['New Request'] ?></h3>
									</div>
									<p class="mb-0">New Request</p>
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-info rounded-circle fs-22">
												<i class="ti ti-eye"></i>
											</span>
										</div>
										<h3 class="mb-0"><?= $counts['In Review'] ?></h3>
									</div>
									<p class="mb-0">In Review</p>
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-success rounded-circle fs-22">
												<i class="ti ti-alarm-snooze"></i>
											</span>
										</div>
										<h3 class="mb-0"><?= $counts['In Progress'] ?></h3>
									</div>
									<p class="mb-0">In Progress</p>
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
										<h3 class="mb-0"><?= $counts['Canceled'] ?></h3>
									</div>
									<p class="mb-0">Canceled</p>
								</div>
							</div>
						</div>

						<div class="col">
							<div class="card mb-1">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar-md flex-shrink-0">
											<span class="avatar-title text-bg-primary rounded-circle fs-22">
												<i class="ti ti-check"></i>
											</span>
										</div>
										<h3 class="mb-0"><?= $counts['Completed'] ?></h3>
									</div>
									<p class="mb-0">Completed</p>
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

										<!-- Client Filter -->
										<?php
											$clientOptions = [];
											foreach ($orders as $o) {
												if (!empty($o['_kf_Client_ID']) && !empty($o['_client_name']) && $o['_client_name'] !== '—') {
													$clientOptions[$o['_kf_Client_ID']] = $o['_client_name'];
												}
											}
											asort($clientOptions);
										?>
										<div class="app-search">
											<select data-table-filter="client" class="form-select form-control my-1 my-md-0">
												<option value="All">All Clients</option>
												<?php foreach ($clientOptions as $clientId => $clientName): ?>
												<option value="<?= htmlspecialchars($clientId) ?>">
													<?= htmlspecialchars($clientName) ?>
												</option>
												<?php endforeach; ?>
											</select>
											<i class="ti ti-truck app-search-icon text-muted"></i>
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
												<th>Requester</th>
												<th>Location(s)</th>
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
												<td><h5 class="fs-sm mb-0 fw-medium"><a href="order_details.php?order_id=<?= htmlspecialchars($row['__kp_API_Input_Order_ID']) ?>" class="link-reset">#<?= htmlspecialchars($row['__kp_API_Input_Order_ID']) ?></a></h5></td>
												<td><?= htmlspecialchars(date('Y-m-d', strtotime($row['API_Input_Timestamp']))) ?></td>
												<td><?= htmlspecialchars($row['Pat_Name']) ?></td>
												<td><?= htmlspecialchars($row['_client_name']) ?></td>
												<td><?= (int)$row['location_count'] ?></td>
												<td><?= htmlspecialchars($row['_kf_Service_Type_ID_Str']) ?></td>
												<td><span class="badge <?= $row['_status']['class'] ?> fs-xxs badge-label"><?= $row['_status']['label'] ?></span></td>
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
