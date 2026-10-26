<?php
/**
 * /admin/analytics — listing, lead, coupon and banner activity for a period
 * (checklist §10). From Admin::analytics(): $days, $since, $ready, $totals,
 * $top, $listing_stats, $leads, $ads, $ad_stats, $coupons, $coupon_stats.
 */
$n = function (array $stats, $id, $event) {
	return (int) ($stats[(int) $id][$event] ?? 0);
};
?>
<style>
	.p411-an-tiles { display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:10px; margin:0 0 18px; }
	.p411-an-tile { border:1px solid #E4E6EC; border-radius:10px; padding:12px 14px; background:#fff; }
	.p411-an-tile b { display:block; font-size:24px; color:#20245B; line-height:1.2; }
	.p411-an-tile span { font-size:12.5px; color:#6B7280; }
	.p411-an-table { width:100%; font-size:13.5px; border-collapse:collapse; margin:0 0 18px; }
	.p411-an-table th, .p411-an-table td { padding:7px 8px; border-top:1px solid #EEF0F4; text-align:left; }
	.p411-an-table th { color:#6B7280; font-weight:600; }
</style>
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Analytics</span>
        <div class="ud-cen-s2">
          <h2>Activity &mdash; last <?= (int) $days ?> days</h2>
          <p style="margin:4px 0 10px;color:#6B7280;font-size:13.5px;">
            From <?= e(us_date($since)) ?>. A listing view is counted once per visitor per day; crawlers and bots are left out.
            Leads are the enquiries sent from listing pages.
          </p>
          <p style="margin:0 0 14px;">
            <?php foreach (array(7, 30, 90) as $__d): ?>
              <a href="<?= base_url('admin/analytics?days=' . $__d) ?>" class="db-list-ststus" style="<?= $__d === (int) $days ? 'background:#20245B;color:#fff;' : '' ?>"><?= $__d ?> days</a>
            <?php endforeach; ?>
          </p>
          <?php if (!$ready): ?>
            <div class="alert alert-warning">Run database migration 0037 to start counting clicks, coupon and banner activity.</div>
          <?php endif; ?>

          <div class="p411-an-tiles">
            <?php foreach ($totals as $label => $value): ?>
              <div class="p411-an-tile"><b><?= number_format((int) $value) ?></b><span><?= e($label) ?></span></div>
            <?php endforeach; ?>
          </div>

          <h3 style="font-size:16px;">Busiest businesses</h3>
          <?php if (!$top): ?>
            <p><em>No listing activity in this period yet.</em></p>
          <?php else: ?>
            <table class="p411-an-table">
              <tr><th>Business</th><th>Views</th><th>Leads</th><th>Website</th><th>Phone</th><th>Email</th><th>Map</th></tr>
              <?php foreach ($top as $l): $id = (int) $l->listing_id; ?>
                <tr>
                  <td><a href="<?= e_url(base_url('listing-details/' . rawurlencode((string) $l->listing_slug))) ?>" target="_blank" rel="noopener"><?= e($l->listing_name) ?></a></td>
                  <td><?= $n($listing_stats, $id, 'view') ?></td>
                  <td><?= (int) ($leads[$id] ?? 0) ?></td>
                  <td><?= $n($listing_stats, $id, 'website') ?></td>
                  <td><?= $n($listing_stats, $id, 'phone') + $n($listing_stats, $id, 'whatsapp') ?></td>
                  <td><?= $n($listing_stats, $id, 'email') ?></td>
                  <td><?= $n($listing_stats, $id, 'map') ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>

          <h3 style="font-size:16px;">Banners and promotional placements</h3>
          <?php if (!$ads): ?>
            <p><em>No sold banner was shown in this period.</em></p>
          <?php else: ?>
            <table class="p411-an-table">
              <tr><th>Position</th><th>Advertiser</th><th>Runs</th><th>Impressions</th><th>Clicks</th><th>Click rate</th></tr>
              <?php foreach ($ads as $a): $imp = $n($ad_stats, $a->id, 'impression'); $clk = $n($ad_stats, $a->id, 'click'); ?>
                <tr>
                  <td><?= e($a->zone_name ?: 'Banner #' . (int) $a->id) ?></td>
                  <td><?= e($a->advertiser ?: '—') ?></td>
                  <td><?= e(us_date($a->ad_start_date)) ?> &ndash; <?= e(us_date($a->ad_end_date)) ?></td>
                  <td><?= $imp ?></td>
                  <td><?= $clk ?></td>
                  <td><?= $imp > 0 ? number_format(100 * $clk / $imp, 1) . '%' : '—' ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>

          <h3 style="font-size:16px;">Deals &amp; coupons</h3>
          <?php if (!$coupons): ?>
            <p><em>No coupon activity in this period.</em></p>
          <?php else: ?>
            <table class="p411-an-table">
              <tr><th>Deal</th><th>Ends</th><th>Views</th><th>Code copied / link opened</th></tr>
              <?php foreach ($coupons as $c): ?>
                <tr>
                  <td><a href="<?= base_url('coupons/' . (int) $c->coupon_id) ?>" target="_blank" rel="noopener"><?= e($c->coupon_name) ?></a></td>
                  <td><?= e(us_date($c->coupon_end_date)) ?></td>
                  <td><?= $n($coupon_stats, $c->coupon_id, 'view') ?></td>
                  <td><?= $n($coupon_stats, $c->coupon_id, 'click') ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
