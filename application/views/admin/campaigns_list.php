<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Newsletter Campaigns</span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<p style="color:#6b7280;">
						<strong><?= (int) $subscribers ?></strong> confirmed subscribers in the list.
					</p>
					<a href="<?= base_url('admin/campaigns/new') ?>" class="db-tit-btn">Compose new campaign</a>

					<table data-admin-table class="responsive-table bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>Subject</th>
								<th>Status</th>
								<th>Recipients</th>
								<th>Sent</th>
								<th>Failed</th>
								<th>Created</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($campaigns)): ?>
								<tr><td colspan="8">No campaigns yet.</td></tr>
							<?php else: foreach ($campaigns as $c): ?>
								<tr>
									<td><?= (int) $c->campaign_id ?></td>
									<td><?= e($c->subject) ?></td>
									<td><?= e($c->status) ?></td>
									<td><?= (int) $c->total_recipients ?></td>
									<td><?= (int) $c->sent_count ?></td>
									<td><?= (int) $c->failed_count ?></td>
									<td><?= e(date('Y-m-d H:i', strtotime($c->created_at))) ?></td>
									<td><?= admin_actions(array(
										'view'   => base_url('admin/campaigns/view/' . (int) $c->campaign_id),
										'edit'   => ($c->status === 'draft')
											? base_url('admin/campaigns/edit/' . (int) $c->campaign_id)
											: null,
										'delete' => base_url('admin/campaigns/delete/' . (int) $c->campaign_id),
										'delete_confirm' => 'Delete this campaign and all its send-log rows?',
									), 'campaign') ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
