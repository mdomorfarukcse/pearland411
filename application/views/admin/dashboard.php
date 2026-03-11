<style>
	.dashboard-container {
		padding: 24px;
		max-width: 100%;
		margin: 0 auto;
		padding-left: 290px;
		padding-top: 95px;
	}

	.dashboard-container .dashboard-header {
		margin-bottom: 32px;
	}

	.dashboard-container .dashboard-title {
		font-size: 28px;
		font-weight: 700;
		color: #1e293b;
		margin-bottom: 8px;
	}

	.dashboard-container .dashboard-subtitle {
		color: #64748b;
		font-size: 16px;
	}

	.dashboard-container .stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
		gap: 24px;
		margin-bottom: 32px;
	}

	.dashboard-container .stat-card {
		background: white;
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		border: 1px solid #e2e8f0;
	}

	.dashboard-container .stat-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 16px;
	}

	.dashboard-container .stat-card-title {
		font-size: 14px;
		font-weight: 600;
		color: #64748b;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.dashboard-container .stat-card-icon {
		width: 40px;
		height: 40px;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 18px;
	}

	.dashboard-container .stat-card-icon.blue {
		background-color: #dbeafe;
		color: #2563eb;
	}

	.dashboard-container .stat-card-icon.green {
		background-color: #dcfce7;
		color: #16a34a;
	}

	.dashboard-container .stat-card-icon.pink {
		background-color: #fce7f3;
		color: #ec4899;
	}

	.dashboard-container .stat-card-icon.purple {
		background-color: #f3e8ff;
		color: #9333ea;
	}

	.dashboard-container .stat-card-icon.orange {
		background-color: #fed7aa;
		color: #ea580c;
	}

	.dashboard-container .stat-card-number {
		font-size: 36px;
		font-weight: 700;
		color: #1e293b;
		margin-bottom: 8px;
	}

	.dashboard-container .stat-card-description {
		font-size: 14px;
		color: #64748b;
		margin-bottom: 12px;
	}

	.dashboard-container .stat-card-change {
		display: flex;
		align-items: center;
		font-size: 12px;
		font-weight: 600;
	}

	.dashboard-container .stat-card-change.positive {
		color: #16a34a;
	}

	.dashboard-container .stat-card-change.negative {
		color: #dc2626;
	}

	.dashboard-container .content-grid {
		display: grid;
		grid-template-columns: 2fr 1fr;
		gap: 24px;
		margin-bottom: 32px;
	}

	.dashboard-container .chart-card {
		background: white;
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		border: 1px solid #e2e8f0;
		height: 450px;
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
	}

	.dashboard-container .chart-card-title {
		font-size: 18px;
		font-weight: 600;
		color: #1e293b;
		margin-bottom: 20px;
	}

	.dashboard-container .chart-placeholder {
		height: 300px;
		background-color: #f8fafc;
		border: 2px dashed #cbd5e1;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		color: #64748b;
	}

	.dashboard-container .chart-placeholder-icon {
		font-size: 48px;
		margin-bottom: 12px;
	}

	.dashboard-container .recent-activity {
		background: white;
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		border: 1px solid #e2e8f0;
	}

	.dashboard-container .activity-item {
		display: flex;
		align-items: center;
		padding: 12px 0;
		border-bottom: 1px solid #f1f5f9;
	}

	.dashboard-container .activity-item:last-child {
		border-bottom: none;
	}

	.dashboard-container .activity-icon {
		width: 32px;
		height: 32px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 12px;
		font-size: 14px;
	}

	.dashboard-container .activity-icon.user {
		background-color: #dbeafe;
		color: #2563eb;
	}

	.dashboard-container .activity-icon.listing {
		background-color: #dcfce7;
		color: #16a34a;
	}

	.dashboard-container .activity-icon.event {
		background-color: #fef3c7;
		color: #d97706;
	}

	.dashboard-container .activity-content {
		flex: 1;
	}

	.dashboard-container .activity-text {
		font-size: 14px;
		color: #334155;
		margin-bottom: 4px;
	}

	.dashboard-container .activity-time {
		font-size: 12px;
		color: #64748b;
	}

	.dashboard-container .bottom-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 24px;
	}

	.dashboard-container .info-card {
		background: white;
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		border: 1px solid #e2e8f0;
	}

	.dashboard-container .info-card-title {
		font-size: 16px;
		font-weight: 600;
		color: #1e293b;
		margin-bottom: 16px;
	}

	.dashboard-container .info-list {
		list-style: none;
	}

	.dashboard-container .info-list-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 8px 0;
		border-bottom: 1px solid #f1f5f9;
	}

	.dashboard-container .info-list-item:last-child {
		border-bottom: none;
	}

	.dashboard-container .info-list-label {
		font-size: 14px;
		color: #64748b;
	}

	.dashboard-container .info-list-value {
		font-size: 14px;
		font-weight: 600;
		color: #1e293b;
	}

	.dashboard-container .progress-bar {
		width: 100%;
		height: 8px;
		background-color: #f1f5f9;
		border-radius: 4px;
		overflow: hidden;
		margin-top: 12px;
	}

	.dashboard-container .progress-fill {
		height: 100%;
		background-color: #3b82f6;
		border-radius: 4px;
		transition: width 0.3s ease;
	}

	@media (max-width: 768px) {
		.dashboard-container {
			padding: 16px;
		}

		.dashboard-container .content-grid {
			grid-template-columns: 1fr;
		}

		.dashboard-container .stats-grid {
			grid-template-columns: 1fr;
		}
	}
</style>



<div class="dashboard-container">
	<div class="dashboard-header">
		<h1 class="dashboard-title">DASHBOARD</h1>
		<p class="dashboard-subtitle">Overview of your platform performance and statistics</p>
	</div>

	<div class="stats-grid">
		<div class="stat-card">
			<div class="stat-card-header">
				<span class="stat-card-title">New Users</span>
				<div class="stat-card-icon blue">👤</div>
			</div>
			<div class="stat-card-number"><?php echo isset($user_count) ? $user_count : 0; ?></div>
			<div class="stat-card-description">User requests</div>

		</div>

		<div class="stat-card">
			<div class="stat-card-header">
				<span class="stat-card-title">Leads & Enquiry</span>
				<div class="stat-card-icon green">💰</div>
			</div>
			<div class="stat-card-number"><?php echo isset($ads_count) ? $ads_count : 0; ?></div>
			<div class="stat-card-description">Get Quote</div>

		</div>

		<div class="stat-card">
			<div class="stat-card-header">
				<span class="stat-card-title">Enquiry</span>
				<div class="stat-card-icon pink">🤝</div>
			</div>
			<div class="stat-card-number"><?php echo isset($active_ads_count) ? $active_ads_count : 0; ?></div>
			<div class="stat-card-description">Ad Request</div>

		</div>

		<div class="stat-card">
			<div class="stat-card-header">
				<span class="stat-card-title">Revenue</span>
				<div class="stat-card-icon purple">💎</div>
			</div>
			<div class="stat-card-number"><?php echo isset($revenue) ? ('$' . number_format((float)$revenue, 2)) : '$0.00'; ?></div>
			<div class="stat-card-description">Total earnings</div>

		</div>
	</div>

	<div class="content-grid">
		<div class="chart-card">
			<h3 class="chart-card-title">Monthly Analytics</h3>
			<canvas id="userChart" style="width:100%;height:100%;"></canvas>
			<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
			<script>
				var ctx = document.getElementById('userChart').getContext('2d');
				var userStats = <?php echo json_encode(array_values(isset($monthly_user_stats) ? $monthly_user_stats : [])); ?>;
				var revenueStats = <?php echo json_encode(array_values(isset($monthly_revenue_stats) ? $monthly_revenue_stats : [])); ?>;
				var listingStats = <?php echo json_encode(array_values(isset($monthly_listing_stats) ? $monthly_listing_stats : [])); ?>;
				var productStats = <?php echo json_encode(array_values(isset($monthly_product_stats) ? $monthly_product_stats : [])); ?>;

				var userChart = new Chart(ctx, {
					type: 'bar',
					data: {
						labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
						datasets: [{
								label: 'New Users',
								data: userStats,
								backgroundColor: 'rgba(37, 99, 235, 0.2)',
								borderColor: 'rgba(37, 99, 235, 1)',
								borderWidth: 1
							},
							{
								label: 'Revenue',
								data: revenueStats,
								backgroundColor: 'rgba(16, 163, 74, 0.2)',
								borderColor: 'rgba(16, 163, 74, 1)',
								borderWidth: 1
							},
							{
								label: 'Listings',
								data: listingStats,
								backgroundColor: 'rgba(234, 88, 9, 0.2)',
								borderColor: 'rgba(234, 88, 9, 1)',
								borderWidth: 1
							},
							{
								label: 'Products',
								data: productStats,
								backgroundColor: 'rgba(147, 51, 234, 0.2)',
								borderColor: 'rgba(147, 51, 234, 1)',
								borderWidth: 1
							}
						]
					},
					options: {
						indexAxis: 'y', // This makes the chart horizontal
						scales: {
							x: {
								beginAtZero: true
							}
						}
					}
				});
			</script>
		</div>

		<div class="recent-activity">
			<h3 class="chart-card-title">Recent Activity</h3>
			<?php if (!empty($recent_activity)) : ?>
				<?php foreach ($recent_activity as $activity) : ?>
					<div class="activity-item">
						<div class="activity-icon <?php echo $activity['type']; ?>">
							<?php echo $activity['icon']; ?>
						</div>
						<div class="activity-content">
							<div class="activity-text"><?php echo $activity['text']; ?></div>
							<div class="activity-time"><?php echo $activity['time']; ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="activity-item">
					<div class="activity-content">No recent activity found.</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="bottom-grid">
		<div class="info-card">
			<h3 class="info-card-title">Platform Statistics</h3>
			<ul class="info-list">
				<li class="info-list-item">
					<span class="info-list-label">All Users</span>
					<span class="info-list-value"><?php echo isset($user_count) ? $user_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">All Listings</span>
					<span class="info-list-value"><?php echo isset($listing_count) ? $listing_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">All Events</span>
					<span class="info-list-value"><?php echo isset($event_count) ? $event_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">All Products</span>
					<span class="info-list-value"><?php echo isset($product_count) ? $product_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">Houses For Sale</span>
					<span class="info-list-value"><?php echo isset($house_count) ? $house_count : 0; ?></span>
				</li>
			</ul>
		</div>

		<div class="info-card">
			<h3 class="info-card-title">Category Breakdown</h3>
			<ul class="info-list">
				<li class="info-list-item">
					<span class="info-list-label">Apartments & Rentals</span>
					<span class="info-list-value"><?php echo isset($rental_count) ? $rental_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">All Classifieds</span>
					<span class="info-list-value"><?php echo isset($classified_count) ? $classified_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">Active Ads</span>
					<span class="info-list-value"><?php echo isset($active_ads_count) ? $active_ads_count : 0; ?></span>
				</li>
				<li class="info-list-item">
					<span class="info-list-label">Pending Reviews</span>
					<span class="info-list-value"><?php echo isset($pending_review_count) ? $pending_review_count : 0; ?></span>
				</li>
			</ul>
		</div>

		<div class="info-card">
			<h3 class="info-card-title">System Performance</h3>
			<div style="margin-bottom: 16px;">
				<div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
					<span class="info-list-label">Server Load</span>
					<span class="info-list-value"><?php echo isset($system_performance['server_load']) ? $system_performance['server_load'] : 'N/A'; ?>%</span>
				</div>
				<div class="progress-bar">
					<div class="progress-fill" style="width: <?php echo isset($system_performance['server_load']) ? $system_performance['server_load'] : 0; ?>%;"></div>
				</div>
			</div>
			<div style="margin-bottom: 16px;">
				<div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
					<span class="info-list-label">Database Usage</span>
					<span class="info-list-value"><?php echo isset($system_performance['db_usage']) ? $system_performance['db_usage'] : 'N/A'; ?>%</span>
				</div>
				<div class="progress-bar">
					<div class="progress-fill" style="width: <?php echo isset($system_performance['db_usage']) ? $system_performance['db_usage'] : 0; ?>%;"></div>
				</div>
			</div>
			<div>
				<div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
					<span class="info-list-label">Storage Used</span>
					<span class="info-list-value"><?php echo isset($system_performance['storage_used']) ? $system_performance['storage_used'] : 'N/A'; ?>%</span>
				</div>
				<div class="progress-bar">
					<div class="progress-fill" style="width: <?php echo isset($system_performance['storage_used']) ? $system_performance['storage_used'] : 0; ?>%;"></div>
				</div>
			</div>
		</div>
	</div>
</div>