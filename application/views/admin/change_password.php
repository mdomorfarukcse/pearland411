<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Change My Password</span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<p style="color:#6b7280;">
						Signed in as <strong><?= e($admin_row->admin_email) ?></strong>
						(<?= e($admin_row->role ?? 'editor') ?>).
					</p>

					<form method="post" action="<?= base_url('admin/change-password') ?>" autocomplete="off"
						  style="max-width:480px;">
						<div class="form-group">
							<label>Current password</label>
							<input type="password" name="current_password" class="form-control" required autofocus>
						</div>
						<div class="form-group">
							<label>New password</label>
							<input type="password" name="new_password" class="form-control" minlength="8" required>
							<small style="color:#6b7280;">Minimum 8 characters.</small>
						</div>
						<div class="form-group">
							<label>Confirm new password</label>
							<input type="password" name="new_password_confirm" class="form-control" minlength="8" required>
						</div>

						<button type="submit" class="btn btn-primary">Update password</button>
						<a href="<?= base_url('admin/dashboard') ?>" class="btn btn-secondary">Cancel</a>
					</form>

					<p style="color:#6b7280;margin-top:24px;font-size:0.9rem;">
						After saving you&rsquo;ll be signed out and must log back in
						with the new password.
					</p>
				</div>
			</div>
		</div>
	</div>
</section>
