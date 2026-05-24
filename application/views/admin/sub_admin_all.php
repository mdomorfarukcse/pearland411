<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Sub-Admins</span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<h2>Sub-Admins</h2>
					<a href="<?= base_url('admin/sub-admins/new') ?>" class="db-tit-btn">Add new sub-admin</a>

					<table class="responsive-table bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Email</th>
								<th>Role</th>
								<th>Active</th>
								<th>Created</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($admins as $a):
								$is_self = ((int) ($current['logged_id'] ?? 0) === (int) $a->admin_id);
							?>
								<tr>
									<td><?= (int) $a->admin_id ?></td>
									<td>
										<?= e($a->admin_name) ?>
										<?php if ($is_self): ?>
											<small style="color:#888;">(you)</small>
										<?php endif; ?>
									</td>
									<td><?= e($a->admin_email) ?></td>
									<td><?= e($a->role ?? 'editor') ?></td>
									<td><?= ((int) ($a->is_active ?? 1) === 1) ? '<span style="color:green;">Yes</span>' : '<span style="color:#999;">No</span>' ?></td>
									<td><?= e(date('Y-m-d', strtotime($a->admin_created))) ?></td>
									<td>
										<a href="<?= base_url('admin/sub-admins/edit/' . (int) $a->admin_id) ?>">Edit</a>
										<?php if (!$is_self): ?>
											| <a href="<?= base_url('admin/sub-admins/delete/' . (int) $a->admin_id) ?>"
												onclick="return confirm('Delete this sub-admin?');"
												style="color:#c00;">Delete</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
