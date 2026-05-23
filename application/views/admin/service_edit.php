<?php
$is_edit = !empty($service);
$action  = $is_edit
	? base_url('admin/services/edit/' . (int) $service->service_id)
	: base_url('admin/services/add');
$val = function ($field, $default = '') use ($service) {
	if (!empty($service) && property_exists($service, $field)) {
		return $service->$field;
	}
	return $default;
};
?>
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst"><?= $is_edit ? 'Edit Service' : 'Add Service' ?></span>

				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<form method="post" action="<?= $action ?>">
						<div class="form-group">
							<label>Name *</label>
							<input type="text" name="name" maxlength="160" class="form-control"
								   value="<?= e_attr($val('name')) ?>" required>
						</div>
						<div class="form-group">
							<label>Slug *</label>
							<input type="text" name="slug" maxlength="160" class="form-control"
								   value="<?= e_attr($val('slug')) ?>" required
								   pattern="[A-Za-z0-9\-_]+">
							<small style="color:#6b7280;">URL-safe: letters, numbers, dashes only. Used as /services/&lt;slug&gt;.</small>
						</div>
						<div class="form-group">
							<label>Category</label>
							<input type="text" name="category" maxlength="80" class="form-control"
								   value="<?= e_attr($val('category')) ?>"
								   placeholder="e.g. Web Development, Hosting, Marketing">
						</div>
						<div class="form-group">
							<label>Icon (Material icon name)</label>
							<input type="text" name="icon" maxlength="100" class="form-control"
								   value="<?= e_attr($val('icon')) ?>"
								   placeholder="e.g. web, cloud, support_agent">
						</div>
						<div class="form-group">
							<label>Short description *</label>
							<textarea name="short_description" maxlength="500" rows="2" class="form-control" required><?= e($val('short_description')) ?></textarea>
						</div>
						<div class="form-group">
							<label>Body (HTML allowed: p, strong, em, ul/ol/li, a, h2-h4)</label>
							<textarea name="body" rows="8" class="form-control"><?= e($val('body')) ?></textarea>
						</div>
						<div class="row">
							<div class="col-md-4 form-group">
								<label>Base price (USD)</label>
								<input type="number" step="0.01" min="0" name="base_price" class="form-control"
									   value="<?= e_attr($val('base_price')) ?>"
									   placeholder="Leave blank for custom quote">
							</div>
							<div class="col-md-4 form-group">
								<label>Price unit</label>
								<?php $unit = $val('price_unit', 'custom'); ?>
								<select name="price_unit" class="form-control">
									<?php foreach (array('one_time','monthly','annual','project','hourly','custom') as $u): ?>
										<option value="<?= $u ?>" <?= ($u === $unit) ? 'selected' : '' ?>>
											<?= e(str_replace('_', ' ', $u)) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 form-group">
								<label>Sort order</label>
								<input type="number" step="1" name="sort_order" class="form-control"
									   value="<?= e_attr($val('sort_order', 0)) ?>">
							</div>
						</div>
						<div class="form-group">
							<label>
								<input type="checkbox" name="is_active" value="1"
									   <?= ((int) $val('is_active', 1) === 1) ? 'checked' : '' ?>>
								Active (visible on the public services page)
							</label>
						</div>

						<button type="submit" class="btn btn-primary"><?= $is_edit ? 'Save changes' : 'Create service' ?></button>
						<a href="<?= base_url('admin/services') ?>" class="btn btn-secondary">Cancel</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
