<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Service Requests</span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<div style="margin-bottom:16px;">
						<?php $statuses = array('' => 'All', 'new' => 'New', 'in_review' => 'In review', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost', 'spam' => 'Spam'); ?>
						<?php foreach ($statuses as $s => $label): ?>
							<a href="<?= base_url('admin/service-requests' . ($s ? '?status=' . $s : '')) ?>"
							   class="btn btn-sm <?= (($selected_status ?? '') === $s) ? 'btn-primary' : 'btn-outline-primary' ?>"
							   style="margin:2px;">
								<?= e($label) ?>
							</a>
						<?php endforeach; ?>
					</div>

					<table data-admin-table class="responsive-table bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>When</th>
								<th>Service</th>
								<th>Name</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Budget</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($requests)): ?>
								<tr><td colspan="9">No service requests yet.</td></tr>
							<?php else: foreach ($requests as $r): ?>
								<tr>
									<td><?= (int) $r->request_id ?></td>
									<td><?= e(date('Y-m-d H:i', strtotime($r->created_at))) ?></td>
									<td><?= e($r->service_name ?? '(general)') ?></td>
									<td><?= e($r->name) ?></td>
									<td><a href="mailto:<?= e_attr($r->email) ?>"><?= e($r->email) ?></a></td>
									<td><?= e($r->phone) ?></td>
									<td><?= e($r->budget_range) ?></td>
									<td><?= e(str_replace('_', ' ', $r->status)) ?></td>
									<td><?= admin_actions(array(
										'view'   => base_url('admin/service-requests/' . (int) $r->request_id),
										'edit'   => base_url('admin/service-requests/' . (int) $r->request_id),
										'delete' => base_url('admin/service-requests/delete/' . (int) $r->request_id),
										'delete_confirm' => 'Delete this service request?',
									), 'request') ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
