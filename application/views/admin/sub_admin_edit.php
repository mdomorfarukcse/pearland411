<?php
$is_edit = !empty($admin_row);
$action  = $is_edit
	? base_url('admin/sub-admins/edit/' . (int) $admin_row->admin_id)
	: base_url('admin/sub-admins/new');
$val = function ($f, $default = '') use ($admin_row) {
	if (!empty($admin_row) && property_exists($admin_row, $f)) return $admin_row->$f;
	return $default;
};
?>
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst"><?= $is_edit ? 'Edit Sub-Admin' : 'Add Sub-Admin' ?></span>

				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<form method="post" action="<?= $action ?>">
						<div class="form-group">
							<label>Name *</label>
							<input type="text" name="admin_name" maxlength="50" class="form-control"
								   value="<?= e_attr($val('admin_name')) ?>" required>
						</div>
						<div class="form-group">
							<label>Email *</label>
							<input type="email" name="admin_email" maxlength="50" class="form-control"
								   value="<?= e_attr($val('admin_email')) ?>" required>
						</div>
						<div class="form-group">
							<label>Password <?= $is_edit ? '(leave blank to keep current)' : '*' ?></label>
							<input type="password" name="password" class="form-control" minlength="8"
								   <?= $is_edit ? '' : 'required' ?>>
							<small style="color:#6b7280;">Minimum 8 characters.</small>
						</div>
						<div class="form-group">
							<label>Role</label>
							<?php $role = $val('role', 'editor'); ?>
							<select name="role" class="form-control">
								<option value="editor"      <?= ($role === 'editor')      ? 'selected' : '' ?>>Editor — content management</option>
								<option value="super_admin" <?= ($role === 'super_admin') ? 'selected' : '' ?>>Super-admin — full access</option>
							</select>
						</div>
						<div class="form-group">
							<label>
								<input type="checkbox" name="is_active" value="1"
									   <?= ((int) $val('is_active', 1) === 1) ? 'checked' : '' ?>>
								Active (can log in)
							</label>
						</div>

						<button type="submit" class="btn btn-primary">
							<?= $is_edit ? 'Save changes' : 'Create sub-admin' ?>
						</button>
						<a href="<?= base_url('admin/sub-admins') ?>" class="btn btn-secondary">Cancel</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
