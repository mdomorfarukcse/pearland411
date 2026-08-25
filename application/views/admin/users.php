<!-- START -->
<style>
    .usr-note { color: #666; font-size: 13px; margin: 0 0 14px; line-height: 1.6; }
    .usr-search { display: flex; gap: 8px; flex-wrap: wrap; margin: 0 0 16px; }
    .usr-search input[type="search"] { flex: 1 1 260px; min-width: 0; }
    .usr-sub { display: block; color: #555; font-size: 12px; }
    .usr-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; line-height: 18px; white-space: nowrap; }
    .usr-yes { background: #e3f5e8; color: #1c6b34; }
    .usr-no { background: #f1f1f1; color: #767676; }
    .usr-warn { background: #fff4e5; color: #8a5a10; }
</style>
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Members</span>

                <div class="ud-cen-s2">
                    <h2>Member accounts</h2>

                    <p class="usr-note">
                        Every account that can be picked as the owner of a listing, product or ad.
                        <strong>Accounts with no listings and no verified email are usually leftovers
                        from testing</strong> &mdash; those are the ones worth checking before launch.
                        This list is read-only: removing a member would orphan everything they own,
                        so that is deliberately not a button on this page.
                    </p>

                    <form method="get" action="<?= base_url('admin/users') ?>" class="usr-search">
                        <input type="search" name="q" class="form-control"
                               placeholder="Search by name, email or business name&hellip;"
                               value="<?= e_attr($search_query) ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search_query !== '') { ?>
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Clear</a>
                        <?php } ?>
                    </form>

                    <p class="usr-note">
                        <?= (int) $total_rows ?> <?= ((int) $total_rows === 1) ? 'account' : 'accounts' ?><?php
                        if ($search_query !== '') { ?> matching &ldquo;<?= e($search_query) ?>&rdquo;<?php } ?>,
                        the ones owning the most listings first.
                    </p>

                    <?php // Searched in SQL above, so the enhancer must not add its own box. ?>
                    <table data-admin-table data-server-search class="responsive-table bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Account</th>
                                <th>Businesses owned</th>
                                <th>Email verified</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($members)) {
                                $i = $start_number;
                                foreach ($members as $m) { ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <?= e($m->name) ?>
                                            <span class="usr-sub"><?= e($m->email_id) ?></span>
                                            <?php if (!empty($m->mobile_number)) { ?>
                                                <span class="usr-sub"><?= e($m->mobile_number) ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ((int) $m->listing_count > 0) { ?>
                                                <span class="usr-pill usr-yes"><?= (int) $m->listing_count ?></span>
                                                <span class="usr-sub"><?= e($m->businesses) ?></span>
                                            <?php } else { ?>
                                                <span class="usr-pill usr-no">None</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ($m->email_verified === null) { ?>
                                                <span class="usr-pill usr-no">Not recorded</span>
                                            <?php } elseif ((int) $m->email_verified === 1) { ?>
                                                <span class="usr-pill usr-yes">Yes</span>
                                            <?php } else { ?>
                                                <span class="usr-pill usr-warn">No</span>
                                            <?php } ?>
                                        </td>
                                        <td><?= !empty($m->user_created) ? e(us_date($m->user_created)) : '&mdash;' ?></td>
                                    </tr>
                                <?php $i++;
                                }
                            } else { ?>
                                <tr>
                                    <td colspan="5">
                                        <?php if ($search_query !== '') { ?>
                                            No account matches &ldquo;<?= e($search_query) ?>&rdquo;.
                                        <?php } else { ?>
                                            There are no member accounts yet.
                                        <?php } ?>
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
