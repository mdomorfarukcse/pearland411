<?php
$is_edit = !empty($campaign);
$action  = $is_edit
	? base_url('admin/campaigns/edit/' . (int) $campaign->campaign_id)
	: base_url('admin/campaigns/new');
$val = function ($f, $default = '') use ($campaign) {
	if (!empty($campaign) && property_exists($campaign, $f)) return $campaign->$f;
	return $default;
};
?>
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst"><?= $is_edit ? 'Edit Draft Campaign' : 'Compose Campaign' ?></span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<form method="post" action="<?= $action ?>">
						<div class="form-group">
							<label>Subject *</label>
							<input type="text" name="subject" maxlength="255" class="form-control"
								   value="<?= e_attr($val('subject')) ?>" required>
						</div>
						<div class="row">
							<div class="col-md-6 form-group">
								<label>From name (optional)</label>
								<input type="text" name="from_name" maxlength="160" class="form-control"
									   value="<?= e_attr($val('from_name')) ?>"
									   placeholder="Defaults to MAIL_FROM_NAME">
							</div>
							<div class="col-md-6 form-group">
								<label>From address (optional)</label>
								<input type="email" name="from_address" maxlength="255" class="form-control"
									   value="<?= e_attr($val('from_address')) ?>"
									   placeholder="Defaults to MAIL_FROM_ADDRESS">
							</div>
						</div>
						<div class="form-group">
							<label>Message *</label>
							<textarea name="body_html" rows="14" class="form-control js-ckeditor" required><?= e($val('body_html')) ?></textarea>
							<small style="color:#6b7280;">
								Rich text — output as the email body. Recipient list
								is the confirmed newsletter subscribers
								(is_confirmed = 1).
							</small>
						</div>

						<button type="submit" class="btn btn-primary">Save draft</button>

						<?php if ($is_edit): ?>
							<a href="<?= base_url('admin/campaigns/queue/' . (int) $campaign->campaign_id) ?>"
							   onclick="return confirm('Queue this campaign for sending? After queuing it cannot be edited.');"
							   class="btn btn-success">Queue for sending</a>

							<a href="<?= base_url('admin/campaigns/delete/' . (int) $campaign->campaign_id) ?>"
							   onclick="return confirm('Delete this draft?');"
							   class="btn btn-link" style="color:#c00;">Delete draft</a>
						<?php endif; ?>

						<a href="<?= base_url('admin/campaigns') ?>" class="btn btn-secondary">Back</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
