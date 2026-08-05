<?php
/**
 * "You can't add another one" modal, shared by every user-dashboard list page
 * (listings, events, blogs, news, products, videos, ads, classifieds, rentals,
 * houses, enquiries).
 *
 * It covers two different situations that the dashboards used to run together:
 *
 *   1. No active plan at all. The page has no limits to report, so the old
 *      copy read "You've reached your current plan's Listings limit" above
 *      "Usage: ∞ Unlimited" — the limit variables default to -1 (= unlimited)
 *      when there is no subscription to read them from. Nothing was actually
 *      unlimited; the user simply had no plan.
 *   2. An active plan whose numeric cap is used up. That is the real
 *      "upgrade" case, and it is the only one that has a usage count worth
 *      printing.
 *
 * Every including view already computes these before the include (all are
 * optional here — the defaults keep the modal harmless if one is missing):
 *   $label            string  what is being capped, e.g. "Listings"
 *   $has_subscription bool    is there an active plan
 *   $limit            int     the plan's cap; -1 (and 0, by the dashboards'
 *                             convention) means unlimited
 *   $used_count       int     how many the user already has
 */
$pl_label  = isset($label) ? (string) $label : 'items';
$pl_active = !empty($has_subscription);
$pl_limit  = isset($limit) ? (int) $limit : -1;
$pl_used   = isset($used_count) ? (int) $used_count : 0;

// A usage line only means something when the plan has a real, countable cap.
$pl_show_usage = $pl_active && $pl_limit > 0;
?>
<div class="modal fade" id="upgradeModal" tabindex="-1" role="dialog" aria-labelledby="upgradeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;">
				<h5 class="modal-title" id="upgradeModalLabel"><?= $pl_active ? 'Upgrade your plan' : 'Choose a plan' ?></h5>
				<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<?php if (!$pl_active): ?>
					<p style="margin-bottom:0.75rem;color:#495057;">
						You don't have an active plan yet. Choose a plan to start adding
						<?= htmlspecialchars(strtolower($pl_label), ENT_QUOTES, 'UTF-8') ?>.
					</p>
				<?php else: ?>
					<p style="margin-bottom:0.75rem;color:#495057;">
						You've reached your current plan's <?= htmlspecialchars($pl_label, ENT_QUOTES, 'UTF-8') ?> limit.
					</p>
					<?php if ($pl_show_usage): ?>
						<div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px;">
							<strong>Usage:</strong>
							<span class="text-danger"><?= $pl_used ?> / <?= $pl_limit ?></span>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<a href="<?= site_url('pricing-plan') ?>" class="btn btn-primary"><?= $pl_active ? 'View upgrade options' : 'View plans' ?></a>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
