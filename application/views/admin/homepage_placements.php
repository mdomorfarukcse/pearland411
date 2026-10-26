<?php
/**
 * /admin/homepage-placements — Business Hotspot, Business of the Week and
 * Featured: who is on the homepage, until when, and the way to add, extend or
 * remove one. From Admin::homepage_placements(): $placements, $q, $results,
 * $has_dates.
 */
$placements = isset($placements) ? $placements : array('hotspot' => array(), 'week' => array(), 'featured' => array());
$today = date('Y-m-d');
$labels = array(
	'hotspot'  => array('Business Hotspot', 'The rail of businesses near the top of the homepage. Sold for 30 days.'),
	'week'     => array('Business of the Week', 'One wide spotlight card. If several are live, the one ending soonest shows.'),
	'featured' => array('Featured / Spotlight', 'The Featured row (rotates when there are more than fit) and the highlighted cards in the directory. No end date.'),
);
?>
<style>
	.p411-hp-card { border:1px solid #E4E6EC; border-radius:10px; padding:16px 18px; margin:0 0 16px; background:#fff; }
	.p411-hp-card h3 { margin:0 0 4px; font-size:17px; color:#20245B; }
	.p411-hp-card p.hint { margin:0 0 10px; color:#6B7280; font-size:13px; }
	.p411-hp-table { width:100%; font-size:13.5px; border-collapse:collapse; }
	.p411-hp-table td, .p411-hp-table th { padding:7px 8px; border-top:1px solid #EEF0F4; text-align:left; vertical-align:middle; }
	.p411-hp-pill { display:inline-block; padding:2px 9px; border-radius:999px; font-size:12px; font-weight:700; }
	.p411-hp-add { display:flex; flex-wrap:wrap; gap:6px; align-items:center; margin:0; }
	.p411-hp-add select, .p411-hp-add input[type=date] { height:32px; font-size:13px; }
</style>

<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Homepage placements</span>

        <?php if ($this->session->flashdata('message')) { ?>
          <div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
        <?php } ?>
        <?php if ($this->session->flashdata('error')) { ?>
          <div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
        <?php } ?>

        <div class="ud-cen-s2">
          <h2>Who is on the homepage</h2>
          <p style="margin:4px 0 14px;color:#6B7280;font-size:13.5px;">
            Dated placements come off the homepage by themselves the day after their end date; ended ones stay listed here
            for 30 days so you can renew them. Online orders for a Hotspot or Business of the Week arrive as an
            &ldquo;New online order&rdquo; email &mdash; add the business here when they do.
          </p>

          <?php if (!$has_dates): ?>
            <div class="alert alert-warning">Run database migration 0018 to enable the dated placements.</div>
          <?php endif; ?>

          <!-- Add -->
          <div class="p411-hp-card">
            <h3>Add a business</h3>
            <form method="get" action="<?= base_url('admin/homepage-placements') ?>" style="display:flex;gap:8px;margin:6px 0 10px;">
              <input type="search" name="q" value="<?= e_attr($q) ?>" placeholder="Business name or listing id" style="min-width:240px;">
              <button type="submit" class="db-tit-btn" style="position:static;">Find</button>
            </form>
            <?php if ($q !== '' && empty($results)): ?>
              <p><em>No listing matches &ldquo;<?= e($q) ?>&rdquo;.</em></p>
            <?php endif; ?>
            <?php if (!empty($results)): ?>
              <table class="p411-hp-table">
                <?php foreach ($results as $r): ?>
                  <tr>
                    <td><strong><?= e($r->listing_name) ?></strong><br><small><?= e($r->listing_address ?? '') ?></small></td>
                    <td>
                      <form method="post" action="<?= base_url('admin/homepage-placement-save') ?>" class="p411-hp-add">
                        <input type="hidden" name="listing_id" value="<?= (int) $r->listing_id ?>">
                        <select name="type" class="no-select2">
                          <?php if ($has_dates): ?>
                            <option value="hotspot">Business Hotspot</option>
                            <option value="week">Business of the Week</option>
                          <?php endif; ?>
                          <option value="featured">Featured</option>
                        </select>
                        <input type="date" name="until" min="<?= $today ?>" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" title="End date (not used for Featured)">
                        <button type="submit" class="db-list-ststus">Add</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </table>
            <?php endif; ?>
          </div>

          <?php foreach ($labels as $key => $meta): $rows = $placements[$key]; ?>
            <div class="p411-hp-card">
              <h3><?= e($meta[0]) ?> <small style="color:#6B7280;">(<?= count($rows) ?>)</small></h3>
              <p class="hint"><?= e($meta[1]) ?></p>
              <?php if (!$rows): ?>
                <p><em>Nobody right now.</em></p>
              <?php else: ?>
                <table class="p411-hp-table">
                  <?php foreach ($rows as $r):
                    $until = isset($r->until) ? (string) $r->until : '';
                    $live  = $key === 'featured' || ($until !== '' && $until >= $today); ?>
                    <tr>
                      <td>
                        <a href="<?= e_url(base_url('listing-details/' . rawurlencode((string) $r->listing_slug))) ?>" target="_blank" rel="noopener"><?= e($r->listing_name) ?></a>
                        <?php if ((string) $r->listing_status !== '1'): ?><small style="color:#6D263D;"> · listing not published</small><?php endif; ?>
                      </td>
                      <td>
                        <?php if ($key !== 'featured'): ?>
                          <span class="p411-hp-pill" style="<?= $live ? 'background:#E6F4D7;color:#3C6A10;' : 'background:#F2F3F5;color:#6B7280;' ?>">
                            <?= $live ? 'Live until ' : 'Ended ' ?><?= e(us_date($until)) ?>
                          </span>
                        <?php else: ?>
                          <span class="p411-hp-pill" style="background:#E6F4D7;color:#3C6A10;">Live</span>
                        <?php endif; ?>
                      </td>
                      <td style="text-align:right;white-space:nowrap;">
                        <?php if ($key !== 'featured'): ?>
                          <form method="post" action="<?= base_url('admin/homepage-placement-save') ?>" class="p411-hp-add" style="display:inline-flex;">
                            <input type="hidden" name="listing_id" value="<?= (int) $r->listing_id ?>">
                            <input type="hidden" name="type" value="<?= e_attr($key) ?>">
                            <input type="date" name="until" min="<?= $today ?>" value="<?= e_attr(date('Y-m-d', strtotime(max($until, $today) . ' +' . ($key === 'week' ? 7 : 30) . ' days'))) ?>">
                            <button type="submit" class="db-list-ststus"><?= $live ? 'Extend' : 'Renew' ?></button>
                          </form>
                        <?php endif; ?>
                        <?php if ($live): ?>
                          <form method="post" action="<?= base_url('admin/homepage-placement-remove/' . (int) $r->listing_id) ?>" style="display:inline;"
                                onsubmit="return confirm('Take this business off the homepage now?');">
                            <input type="hidden" name="type" value="<?= e_attr($key) ?>">
                            <button type="submit" class="db-list-ststus" style="background:#6D263D;color:#fff;">Remove</button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </table>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
