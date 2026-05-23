<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Services Catalog</span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<h2>Services</h2>
					<a href="<?= base_url('admin/services/add') ?>" class="db-tit-btn">Add new service</a>

					<table class="responsive-table bordered">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Category</th>
								<th>Price</th>
								<th>Active</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($services)): ?>
								<tr><td colspan="6">No services yet. <a href="<?= base_url('admin/services/add') ?>">Add one</a>.</td></tr>
							<?php else: foreach ($services as $i => $s): ?>
								<tr>
									<td><?= $i + 1 ?></td>
									<td>
										<strong><?= e($s->name) ?></strong>
										<div style="color:#6b7280;font-size:0.85rem;"><?= e($s->slug) ?></div>
									</td>
									<td><?= e($s->category) ?></td>
									<td>
										<?php if ($s->base_price !== null): ?>
											$<?= number_format((float) $s->base_price, 2) ?>
											<small><?= e(str_replace('_', ' ', $s->price_unit)) ?></small>
										<?php else: ?>
											<em>custom</em>
										<?php endif; ?>
									</td>
									<td>
										<?= ((int) $s->is_active === 1) ? '<span style="color:green;">Yes</span>' : '<span style="color:#999;">No</span>' ?>
									</td>
									<td>
										<a href="<?= base_url('services/' . rawurlencode($s->slug)) ?>" target="_blank">View</a> |
										<a href="<?= base_url('admin/services/edit/' . (int) $s->service_id) ?>">Edit</a> |
										<a href="<?= base_url('admin/services/delete/' . (int) $s->service_id) ?>"
										   onclick="return confirm('Delete this service?');"
										   style="color:#c00;">Delete</a>
									</td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
