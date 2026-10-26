<?php
/**
 * /admin/claimed-listings — the Claim This Business review queue.
 *
 * From Admin::claimed_listings(): $listing_requests (Adminmodel::
 * getClaimsForReview — each claim with its listing, the listing's current
 * owner and the claimant's account), $claim_counts (status => n), $filter.
 *
 * Each claim can be approved, rejected with a reason, or sent back with a
 * question ("request more information", claim_status 3). All three are POST
 * forms — they used to be GET links — and the claimant is emailed each time
 * (libraries/Notifications.php).
 */
$claim_counts = isset($claim_counts) ? $claim_counts : array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
$filter       = isset($filter) ? $filter : 'open';
$roles        = claim_roles();
$methods      = claim_verification_methods();
$tabs = array(
	'open'      => array('Needs action', $claim_counts[0] + $claim_counts[3]),
	'pending'   => array('Pending review', $claim_counts[0]),
	'more_info' => array('Waiting on claimant', $claim_counts[3]),
	'approved'  => array('Approved', $claim_counts[1]),
	'rejected'  => array('Rejected', $claim_counts[2]),
	'all'       => array('All', array_sum($claim_counts)),
);
$status_style = array(
	0 => 'background:#FFF4DB;color:#7A5200;',
	1 => 'background:#E6F4D7;color:#3C6A10;',
	2 => 'background:#FDE2E7;color:#591A2D;',
	3 => 'background:#E8ECF6;color:#20245B;',
);
?>
<style>
	.p411-claims-tabs { display:flex; flex-wrap:wrap; gap:6px; margin:4px 0 16px; }
	.p411-claims-tabs a { padding:6px 12px; border:1px solid #E4E6EC; border-radius:999px; font-size:13px; color:#20245B; background:#fff; }
	.p411-claims-tabs a.is-on { background:#20245B; color:#fff; border-color:#20245B; }
	.p411-claim-card { border:1px solid #E4E6EC; border-radius:10px; padding:16px 18px; margin:0 0 14px; background:#fff; }
	.p411-claim-card__top { display:flex; flex-wrap:wrap; justify-content:space-between; gap:8px; align-items:baseline; }
	.p411-claim-card h3 { margin:0; font-size:17px; color:#20245B; }
	.p411-claim-badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; }
	.p411-claim-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:4px 18px; margin:12px 0; font-size:13.5px; }
	.p411-claim-grid span { color:#6B7280; }
	.p411-claim-msg { background:#F7F5F0; border-radius:8px; padding:10px 12px; font-size:13.5px; white-space:normal; margin:0 0 12px; }
	.p411-claim-note { background:#E8ECF6; border-radius:8px; padding:10px 12px; font-size:13.5px; margin:0 0 12px; }
	.p411-claim-warn { background:#FDE2E7; color:#591A2D; border-radius:8px; padding:8px 12px; font-size:13px; margin:0 0 12px; }
	.p411-claim-actions { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-start; }
	.p411-claim-actions form { margin:0; }
	.p411-claim-actions details { border:1px solid #E4E6EC; border-radius:8px; padding:6px 10px; }
	.p411-claim-actions summary { cursor:pointer; font-size:13px; font-weight:600; color:#20245B; }
	.p411-claim-actions textarea { width:100%; min-width:260px; margin:8px 0; font-size:13px; }
	.p411-claim-btn { border:0; border-radius:6px; padding:7px 14px; font-size:13px; font-weight:700; cursor:pointer; }
	.p411-claim-btn--ok { background:#79B82A; color:#fff; }
	.p411-claim-btn--ask { background:#20245B; color:#fff; }
	.p411-claim-btn--no { background:#6D263D; color:#fff; }
</style>

<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Claim This Business requests</span>

        <?php if ($this->session->flashdata('message')) { ?>
          <div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
        <?php } ?>

        <div class="ud-cen-s2">
          <h2>Listing claims</h2>
          <p style="margin:4px 0 10px;color:#6B7280;font-size:13.5px;">
            Check the claimant against the verification method they chose, then approve (the listing moves to their
            account and they are emailed a link to edit it), ask for more information, or reject with a reason.
          </p>

          <nav class="p411-claims-tabs" aria-label="Filter claims">
            <?php foreach ($tabs as $key => $tab): ?>
              <a href="<?= base_url('admin/claimed-listings?status=' . $key) ?>" class="<?= $filter === $key ? 'is-on' : '' ?>">
                <?= e($tab[0]) ?> (<?= (int) $tab[1] ?>)
              </a>
            <?php endforeach; ?>
          </nav>

          <?php if (empty($listing_requests)): ?>
            <p><em>No claims here.</em></p>
          <?php endif; ?>

          <?php foreach ((array) $listing_requests as $c):
            $st       = (int) $c->claim_status;
            $open     = in_array($st, array(0, 3), TRUE);
            $other    = !empty($c->is_claimed) && (int) $c->owner_id !== (int) $c->enquiry_sender_id && (int) $c->owner_id > 0;
            $slug     = (string) ($c->listing_slug ?? '');
          ?>
            <article class="p411-claim-card">
              <div class="p411-claim-card__top">
                <h3>
                  <?php if ($slug !== ''): ?>
                    <a href="<?= e_url(base_url('listing-details/' . rawurlencode($slug))) ?>" target="_blank" rel="noopener"><?= e($c->listing_name ?: $c->enquiry_source) ?></a>
                  <?php else: ?>
                    <?= e($c->enquiry_source) ?> <small>(listing deleted)</small>
                  <?php endif; ?>
                </h3>
                <span class="p411-claim-badge" style="<?= $status_style[$st] ?? '' ?>"><?= e(claim_status_label($st)) ?></span>
              </div>

              <div class="p411-claim-grid">
                <div><span>Business name given:</span> <?= e($c->claim_business_name ?? '') ?: '—' ?></div>
                <div><span>Claimant:</span> <?= e($c->enquiry_name) ?></div>
                <div><span>Role:</span> <?= e($roles[$c->claim_role ?? ''] ?? ($c->claim_role ?? '—')) ?></div>
                <div><span>Business email:</span> <a href="mailto:<?= e_attr($c->enquiry_email) ?>"><?= e($c->enquiry_email) ?></a></div>
                <div><span>Phone:</span> <?= e($c->enquiry_mobile) ?: '—' ?></div>
                <div><span>Account:</span> <?= e($c->account_name ?? '') ?> &lt;<?= e($c->account_email ?? '') ?>&gt;</div>
                <div><span>Verify by:</span> <?= e($methods[$c->claim_verification ?? ''] ?? ($c->claim_verification ?? '—')) ?></div>
                <div><span>Submitted:</span> <?= e(us_date($c->claim_created_at)) ?><?= !empty($c->claim_updated_at) ? ' · replied ' . e(us_date($c->claim_updated_at)) : '' ?></div>
                <div><span>Listing now:</span>
                  <?= !empty($c->is_claimed)
                    ? 'claimed by ' . e($c->owner_name ?: 'account #' . (int) $c->owner_id)
                    : 'unclaimed (held by ' . e($c->owner_name ?: 'no account') . ')' ?>
                </div>
                <?php if (!empty($c->enquiry_image)): ?>
                  <div><span>Document:</span> <a href="<?= e_url(base_url($c->enquiry_image)) ?>" target="_blank" rel="noopener">open the upload</a></div>
                <?php endif; ?>
              </div>

              <?php if (trim((string) $c->enquiry_message) !== ''): ?>
                <div class="p411-claim-msg"><?= e_nl2br($c->enquiry_message) ?></div>
              <?php endif; ?>
              <?php if (!empty($c->claim_admin_note)): ?>
                <div class="p411-claim-note"><strong><?= $st === 2 ? 'Reason given' : 'We asked' ?>:</strong> <?= e_nl2br($c->claim_admin_note) ?></div>
              <?php endif; ?>

              <?php if ($open && $slug !== ''): ?>
                <?php if ($other): ?>
                  <div class="p411-claim-warn">This listing is already claimed by another account. Approving moves it to this claimant.</div>
                <?php endif; ?>
                <div class="p411-claim-actions">
                  <form method="post" action="<?= base_url('admin/claim-approve/' . (int) $c->claim_listing_id) ?>"
                        onsubmit="return confirm(<?= e_attr(json_encode('Approve this claim? The listing moves to the account of ' . (string) $c->enquiry_name . '.')) ?>);">
                    <?php if ($other): ?>
                      <label style="font-size:12.5px;display:block;margin-bottom:6px;"><input type="checkbox" name="confirm_transfer" value="1"> transfer anyway</label>
                    <?php endif; ?>
                    <button type="submit" class="p411-claim-btn p411-claim-btn--ok">Approve</button>
                  </form>

                  <details>
                    <summary>Ask for more information</summary>
                    <form method="post" action="<?= base_url('admin/claim-more-info/' . (int) $c->claim_listing_id) ?>">
                      <textarea name="admin_note" rows="3" required placeholder="e.g. Please upload a utility bill or business license in the business's name."></textarea>
                      <button type="submit" class="p411-claim-btn p411-claim-btn--ask">Send the question</button>
                    </form>
                  </details>

                  <details>
                    <summary>Reject</summary>
                    <form method="post" action="<?= base_url('admin/claim-reject/' . (int) $c->claim_listing_id) ?>">
                      <textarea name="admin_note" rows="3" placeholder="Reason (shown to the claimant) — e.g. We could not reach anyone at the business number."></textarea>
                      <button type="submit" class="p411-claim-btn p411-claim-btn--no">Reject the claim</button>
                    </form>
                  </details>
                </div>
              <?php endif; ?>

              <div style="margin-top:10px;font-size:12.5px;">
                <?= admin_actions(array(
                  'delete'         => base_url('admin/delete_claim/' . (int) $c->claim_listing_id),
                  'delete_confirm' => 'Delete this claim permanently? Rejecting keeps a record; deleting does not.',
                ), 'claim') ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
