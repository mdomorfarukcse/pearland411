<?php
/**
 * /user/my-claims — businesses this member asked to claim, and where each
 * request stands. From User::my_claims(): $claims, $tab_counts, $active_tab.
 *
 * claim_status: 0 pending review, 1 approved, 2 not approved, 3 more
 * information needed (migration 0037) — answered here, through the reply form
 * (User::claim_reply), which puts the claim back in the review queue.
 */
$claims = (isset($claims) && is_array($claims)) ? $claims : array();
$__claim_state = array(
	0 => array('Pending review', 'badge-warning', 'We usually review a claim within 1–2 business days and will email you the outcome.'),
	1 => array('Approved', 'badge-success', 'This business is now linked to your account.'),
	2 => array('Not approved', 'badge-secondary', 'We could not verify ownership. Send a new claim with more detail — a business licence, a utility bill, or access to the business email or website all help.'),
	3 => array('More information needed', 'badge-info', 'Our team needs a little more before deciding. Send it below.'),
);
$__notice = $this->session->flashdata('claim_notice');
$__error  = $this->session->flashdata('claim_error');
$__flash  = $this->session->flashdata('claim_message');
?>
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">My claims</span>
	<div class="ud-cen-s2">

		<?php include APPPATH . 'views/partials/listing_tabs.php'; ?>

		<h2>Claim requests</h2>
		<?php if (!empty($__notice) || !empty($__flash)): ?>
			<div class="alert alert-success" role="status"><?= e($__notice ?: $__flash) ?></div>
		<?php endif; ?>
		<?php if (!empty($__error)): ?>
			<div class="alert alert-danger" role="alert"><?= e($__error) ?></div>
		<?php endif; ?>
		<p style="margin:8px 0 18px;color:#5A6072;font-size:14px;">
			Businesses you have asked to take over. When a claim is approved the listing moves to
			<a href="<?= e_url(base_url('user-listing')) ?>">My listings</a>, and its enquiries come to your dashboard.
		</p>

		<?php if (empty($claims)): ?>
			<div class="alert alert-info">
				<p style="margin:0;">
					You have not claimed a business yet. Find yours in the
					<a href="<?= e_url(base_url('all-listing')) ?>">directory</a> and press <strong>Claim This Business</strong> on its page.
				</p>
			</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Business</th>
							<th>Sent</th>
							<th>Status</th>
							<th>What happens next</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($claims as $c): ?>
							<?php
							$__st   = (int) ($c->claim_status ?? 0);
							$__info = isset($__claim_state[$__st]) ? $__claim_state[$__st] : $__claim_state[0];
							$__name = !empty($c->listing_name) ? $c->listing_name : (string) ($c->enquiry_source ?? 'Listing');
							$__gone = empty($c->listing_slug) || !empty($c->is_deleted);
							$__sent = !empty($c->claim_created_at) ? $c->claim_created_at : ($c->created_at ?? '');
							?>
							<tr>
								<td>
									<?php if (!$__gone): ?>
										<a href="<?= e_url(base_url('listing-details/' . rawurlencode((string) $c->listing_slug))) ?>"><strong><?= e($__name) ?></strong></a>
									<?php else: ?>
										<strong><?= e($__name) ?></strong> <small class="text-muted">(no longer listed)</small>
									<?php endif; ?>
								</td>
								<td><?= e(us_date($__sent)) ?></td>
								<td><span class="badge <?= e_attr($__info[1]) ?>"><?= e($__info[0]) ?></span></td>
								<td style="font-size:13.5px;">
									<?= e($__info[2]) ?>
									<?php if (in_array($__st, array(2, 3), TRUE) && !empty($c->claim_admin_note)): ?>
										<div style="margin:6px 0;padding:8px 10px;background:#E8ECF6;border-radius:6px;">
											<strong><?= $__st === 3 ? 'We asked:' : 'Reason:' ?></strong> <?= e_nl2br($c->claim_admin_note) ?>
										</div>
									<?php endif; ?>
									<?php if ($__st === 3 && !$__gone): ?>
										<form method="post" enctype="multipart/form-data" action="<?= e_url(base_url('user/claim-reply/' . (int) $c->claim_listing_id)) ?>" style="margin-top:8px;">
											<textarea name="reply_message" class="form-control" rows="3" maxlength="2000" placeholder="Your answer"></textarea>
											<input type="file" name="claim_document" class="form-control" style="margin-top:6px;" accept="image/jpeg,image/png,image/webp,application/pdf,.pdf">
											<small class="text-muted">Optional document: a photo, scan or PDF up to 5 MB.</small><br>
											<button type="submit" class="btn btn-primary btn-sm" style="margin-top:6px;">Send to the Pearland411 team</button>
										</form>
									<?php endif; ?>
									<?php if ($__st === 1 && !$__gone): ?>
										<br><a href="<?= e_url(base_url('user/edit-listing/' . rawurlencode((string) $c->listing_slug))) ?>">Manage this listing</a>
										&middot; <a href="<?= e_url(base_url('user/enquiries')) ?>">Its enquiries</a>
									<?php elseif ($__st === 2 && !$__gone): ?>
										<br><a href="<?= e_url(base_url('claim-listing/' . rawurlencode((string) $c->listing_slug))) ?>">Send a new claim</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
