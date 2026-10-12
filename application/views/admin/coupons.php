<!-- START -->
<?php
/**
 * /admin/coupons — every deal members have posted (Admin::coupons).
 *
 * Members create deals at /user/my-coupons. This is the moderation side:
 * see them all, switch one off (hidden from /coupons and the homepage, kept
 * for the member to edit), or delete it.
 */
$today = date('Y-m-d');
?>
<style>
    .cpn-note { color: #666; font-size: 13px; margin: 0 0 14px; line-height: 1.6; }
    .cpn-search { display: flex; gap: 8px; flex-wrap: wrap; margin: 0 0 16px; }
    .cpn-search input[type="search"] { flex: 1 1 260px; min-width: 0; }
    .cpn-sub { display: block; color: #555; font-size: 12px; }
    .cpn-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; line-height: 18px; white-space: nowrap; }
    .cpn-live { background: #e3f5e8; color: #1c6b34; }
    .cpn-off { background: #f1f1f1; color: #767676; }
    .cpn-old { background: #fff4e5; color: #8a5a10; }
    .cpn-actions form { display: inline; }
    .cpn-actions button { margin: 0 4px 4px 0; }
</style>
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Deals &amp; Coupons</span>

                <?php if ($this->session->flashdata('message')) { ?>
                    <div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
                <?php } ?>
                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="log-err"><p><?= e($this->session->flashdata('error')) ?></p></div>
                <?php } ?>

                <div class="ud-cen-s2">
                    <h2>Member deals</h2>

                    <p class="cpn-note">
                        Deals members post from their account. <strong>Switch off</strong> hides a deal from
                        <a href="<?= base_url('coupons') ?>" target="_blank" rel="noopener">/coupons</a>,
                        the homepage and the business&rsquo;s listing, and leaves it for the member to fix;
                        <strong>Delete</strong> removes it for good.
                    </p>

                    <form method="get" action="<?= base_url('admin/coupons') ?>" class="cpn-search">
                        <input type="search" name="q" class="form-control"
                               placeholder="Search by deal, code, or member name / email&hellip;"
                               value="<?= e_attr($search_query) ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search_query !== '') { ?>
                            <a href="<?= base_url('admin/coupons') ?>" class="btn btn-secondary">Clear</a>
                        <?php } ?>
                    </form>

                    <p class="cpn-note">
                        <?= (int) $total_rows ?> deal<?= (int) $total_rows === 1 ? '' : 's' ?><?php
                        if ($search_query !== '') { ?> matching &ldquo;<?= e($search_query) ?>&rdquo;<?php } ?>, newest first.
                    </p>

                    <table data-admin-table data-server-search class="responsive-table bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Deal</th>
                                <th>Posted by</th>
                                <th>Runs</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($coupons)) {
                                $i = $start_number;
                                foreach ($coupons as $c) {
                                    $ended = !empty($c->coupon_end_date) && $c->coupon_end_date < $today;
                                    $on    = !empty($c->coupon_status); ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <a href="<?= base_url('coupons/' . (int) $c->coupon_id) ?>" target="_blank" rel="noopener"><?= e($c->coupon_name) ?></a>
                                            <?php if (!empty($c->coupon_code)) { ?>
                                                <span class="cpn-sub">Code: <?= e($c->coupon_code) ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?= e($c->listing_name ?: ($c->owner_name ?: 'Unknown account')) ?>
                                            <?php if (!empty($c->owner_email)) { ?>
                                                <span class="cpn-sub"><?= e($c->owner_email) ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?= !empty($c->coupon_start_date) ? e(us_date($c->coupon_start_date)) : '&mdash;' ?>
                                            &ndash;
                                            <?= !empty($c->coupon_end_date) ? e(us_date($c->coupon_end_date)) : '&mdash;' ?>
                                        </td>
                                        <td>
                                            <?php if (!$on) { ?>
                                                <span class="cpn-pill cpn-off">Switched off</span>
                                            <?php } elseif ($ended) { ?>
                                                <span class="cpn-pill cpn-old">Ended</span>
                                            <?php } else { ?>
                                                <span class="cpn-pill cpn-live">Live</span>
                                            <?php } ?>
                                        </td>
                                        <td class="cpn-actions">
                                            <form method="post" action="<?= base_url('admin/coupon_toggle/' . (int) $c->coupon_id) ?>">
                                                <button type="submit" class="btn btn-sm <?= $on ? 'btn-outline-secondary' : 'btn-outline-success' ?>"><?= $on ? 'Switch off' : 'Switch on' ?></button>
                                            </form>
                                            <form method="post" action="<?= base_url('admin/coupon_delete/' . (int) $c->coupon_id) ?>"
                                                  onsubmit="return confirm(<?= e_attr(json_encode('Delete the deal "' . $c->coupon_name . '"? This cannot be undone.')) ?>);">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php $i++;
                                }
                            } else { ?>
                                <tr>
                                    <td colspan="6">
                                        <?= $search_query !== '' ? 'No deal matches &ldquo;' . e($search_query) . '&rdquo;.' : 'No member has posted a deal yet.' ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <?php if (!empty($link)) { ?>
                        <div class="pagi-nation"><?= $link ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- END -->
