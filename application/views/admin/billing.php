<!-- START -->
<style>
    .bill-note { color: #666; font-size: 13px; margin: 0 0 14px; }
    .bill-warn { background: #fff4e5; border: 1px solid #f0c37a; border-radius: 4px; padding: 10px 14px; margin: 0 0 16px; font-size: 14px; }
    .bill-search { display: flex; gap: 8px; flex-wrap: wrap; margin: 0 0 16px; }
    .bill-search input[type="search"] { flex: 1 1 260px; min-width: 0; }
    .bill-biz { display: block; color: #555; font-size: 12px; }
    .bill-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; line-height: 18px; white-space: nowrap; }
    .bill-yes { background: #e3f5e8; color: #1c6b34; }
    .bill-no { background: #f1f1f1; color: #767676; }
    .bill-bad { background: #fdecea; color: #96271c; }
</style>
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Customer billing</span>

                <div class="ud-cen-s2">
                    <h2>Customer billing</h2>

                    <p class="bill-note">
                        Put a card on file, take a payment, and set a package or an agreed price for
                        <strong>one business</strong>. This list shows businesses that already have a
                        subscription or a payment &mdash; <strong>search to find any other business</strong>,
                        including one you have never billed.
                    </p>

                    <?php if (empty($helcim_configured)) { ?>
                        <div class="bill-warn">
                            <strong>Helcim is not configured.</strong>
                            You can still see billing records, but no card can be saved and no payment can be taken
                            until <code>HELCIM_API_TOKEN</code> is set in <code>.env</code>. Check it under
                            <a href="<?= base_url('admin/setting') ?>">SEO &amp; Setting</a>.
                        </div>
                    <?php } ?>

                    <?php if ($this->session->flashdata('message')) { ?>
                        <div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
                    <?php } ?>
                    <?php if ($this->session->flashdata('error')) { ?>
                        <div class="bill-warn"><?= e($this->session->flashdata('error')) ?></div>
                    <?php } ?>

                    <form method="get" action="<?= base_url('admin/billing') ?>" class="bill-search">
                        <input type="search" name="q" class="form-control"
                               placeholder="Search by business name, or the owner's name or email&hellip;"
                               value="<?= e_attr($search_query) ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search_query !== '') { ?>
                            <a href="<?= base_url('admin/billing') ?>" class="btn btn-secondary">Clear</a>
                        <?php } ?>
                    </form>

                    <p class="bill-note">
                        <?= (int) $total_rows ?> <?= ((int) $total_rows === 1) ? 'business' : 'businesses' ?>
                        <?= ($search_query !== '') ? 'matching &ldquo;' . e($search_query) . '&rdquo;' : 'with billing activity' ?>.
                    </p>

                    <?php // data-server-search: the box above searches every customer in SQL,
                          // so the enhancer must not add a second one that only filters this page. ?>
                    <table data-admin-table data-server-search class="responsive-table bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Business</th>
                                <th>Owner account</th>
                                <th>Package / price</th>
                                <th>Status</th>
                                <th>Card on file</th>
                                <th>Auto-renew</th>
                                <th>Next charge</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($businesses)) {
                                $i = $start_number;
                                foreach ($businesses as $c) { ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><strong><?= e($c->listing_name) ?></strong></td>
                                        <td>
                                            <?php if (!empty($c->user_id)) { ?>
                                                <?= e($c->owner_name) ?>
                                                <span class="bill-biz"><?= e($c->owner_email) ?></span>
                                            <?php } else { ?>
                                                <span class="bill-pill bill-bad">No owner account</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($c->custom_amount)) { ?>
                                                <?= e(!empty($c->currency) ? $c->currency : 'USD') ?>
                                                <?= number_format((float) $c->custom_amount, 2) ?>
                                                <span class="bill-biz">agreed price<?= !empty($c->plan_name) ? ' &middot; ' . e($c->plan_name) : '' ?></span>
                                            <?php } elseif (!empty($c->plan_name)) { ?>
                                                <?= e($c->plan_name) ?>
                                                <?php if (isset($c->plan_price)) { ?>
                                                    <span class="bill-biz"><?= e(!empty($c->currency) ? $c->currency : 'USD') ?> <?= number_format((float) $c->plan_price, 2) ?></span>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <span class="bill-biz">No package</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if (empty($c->sub_status)) { ?>
                                                <span class="bill-pill bill-no">None</span>
                                            <?php } elseif ($c->sub_status === 'active') { ?>
                                                <span class="bill-pill bill-yes">Active</span>
                                            <?php } else { ?>
                                                <span class="bill-pill bill-no"><?= e(ucfirst($c->sub_status)) ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ((int) $c->card_count > 0) { ?>
                                                <span class="bill-pill bill-yes">Yes</span>
                                            <?php } else { ?>
                                                <span class="bill-pill bill-no">No</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($c->auto_renew)) { ?>
                                                <span class="bill-pill bill-yes">On</span>
                                            <?php } else { ?>
                                                <span class="bill-pill bill-no">Off</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?= !empty($c->next_billing_date) ? e(us_date($c->next_billing_date)) : '&mdash;' ?>
                                            <?php if (!empty($c->renewal_attempts)) { ?>
                                                <span class="bill-pill bill-bad" title="<?= e_attr($c->last_renewal_error) ?>">
                                                    <?= (int) $c->renewal_attempts ?> failed
                                                </span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <a class="db-list-edit"
                                               href="<?= base_url('admin/billing-customer/' . (int) $c->listing_id) ?>">Manage billing</a>
                                        </td>
                                    </tr>
                                <?php $i++;
                                }
                            } else { ?>
                                <tr>
                                    <td colspan="9">
                                        <?php if ($search_query !== '') { ?>
                                            No business matches &ldquo;<?= e($search_query) ?>&rdquo;.
                                        <?php } else { ?>
                                            Nothing has been billed yet. Search above for the business you want to set up.
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
