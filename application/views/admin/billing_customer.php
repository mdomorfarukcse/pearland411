<?php
/**
 * /admin/billing-customer/<listing_id>
 *
 * Card on file, one-off payments, and the recurring schedule that
 * Cli::renew_subscriptions charges — all for ONE business.
 *
 * The segment is a listing id, not a user id: the imported listings share a
 * handful of data-entry logins, so one account owns dozens of unrelated
 * businesses and billing the account would bill all of them at once. The card
 * still lives on the owning account, because that is where Helcim vaults it.
 *
 * Card details are typed into Helcim's own iframe (HelcimPay.js) and go
 * straight to Helcim. Nothing on this page ever posts a card number to
 * Pearland411 — what comes back is a token, and that is what gets stored.
 */
$sub_active   = !empty($customer->subscription_id) && $customer->sub_status === 'active';
$sub_currency = !empty($currency) ? $currency : 'USD';

// Default the next charge to the first of next month: the common case is
// "this month has been paid by hand, resume on schedule next month".
$next_default = !empty($customer->next_billing_date)
    ? date('Y-m-d', strtotime($customer->next_billing_date))
    : date('Y-m-01', strtotime('first day of next month'));

// Suggest the amount that is actually owed rather than making staff look it up.
$amount_default = ($renewal_amount > 0)
    ? number_format((float) $renewal_amount, 2, '.', '')
    : '';
?>
<!-- START -->
<style>
    .bcx-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
    .bcx-meta { color: #555; font-size: 13px; margin: 4px 0 0; line-height: 1.6; }
    .bcx-card { border: 1px solid #e3e3e3; border-radius: 6px; padding: 18px 20px; margin: 0 0 18px; background: #fff; }
    .bcx-card h3 { font-size: 17px; margin: 0 0 4px; }
    .bcx-hint { color: #666; font-size: 13px; margin: 0 0 14px; line-height: 1.6; }
    .bcx-row { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
    .bcx-row > div { flex: 1 1 180px; min-width: 0; }
    .bcx-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
    .bcx-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; line-height: 18px; }
    .bcx-yes { background: #e3f5e8; color: #1c6b34; }
    .bcx-no { background: #f1f1f1; color: #767676; }
    .bcx-bad { background: #fdecea; color: #96271c; }
    .bcx-warn { background: #fff4e5; border: 1px solid #f0c37a; border-radius: 4px; padding: 10px 14px; margin: 0 0 16px; font-size: 14px; }
    .bcx-err { background: #fdecea; border: 1px solid #f0a79f; border-radius: 4px; padding: 10px 14px; margin: 0 0 16px; font-size: 14px; }
    .bcx-check { display: flex; align-items: center; gap: 8px; margin: 12px 0 0; font-size: 14px; }
    .bcx-check input { width: auto; margin: 0; }
    .bcx-num { font-variant-numeric: tabular-nums; }
</style>
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Customer billing</span>

                <div class="ud-cen-s2">
                    <div class="bcx-head">
                        <div>
                            <h2><?= e($customer->listing_name) ?></h2>
                            <p class="bcx-meta">
                                <?php // The account is who holds the card and who the charge is
                                      // attributed to — worth naming, because the imported
                                      // listings share a handful of data-entry logins. ?>
                                Billed through <strong><?= e($customer->name) ?></strong>
                                (<?= e($customer->email_id) ?>)
                                <?php if (!empty($customer->mobile_number)) { ?>
                                    &middot; <?= e($customer->mobile_number) ?>
                                <?php } ?>
                                <?php if (!empty($customer->listing_slug)) { ?>
                                    <br><a href="<?= e_url(base_url('listing-details/' . rawurlencode($customer->listing_slug))) ?>"
                                           target="_blank" rel="noopener">View the business page &rarr;</a>
                                <?php } ?>
                            </p>
                        </div>
                        <a href="<?= base_url('admin/billing') ?>" class="btn btn-secondary">&larr; All businesses</a>
                    </div>

                    <hr>

                    <?php if ($this->session->flashdata('message')) { ?>
                        <div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
                    <?php } ?>
                    <?php if ($this->session->flashdata('error')) { ?>
                        <div class="bcx-err"><?= e($this->session->flashdata('error')) ?></div>
                    <?php } ?>
                    <div class="bcx-err" id="bcx-js-err" style="display:none;"></div>

                    <?php if (empty($helcim_configured)) { ?>
                        <div class="bcx-warn">
                            <strong>Helcim is not configured.</strong>
                            No card can be saved and no payment can be taken until <code>HELCIM_API_TOKEN</code>
                            is set in <code>.env</code>. Everything below is read-only until then.
                        </div>
                    <?php } ?>

                    <!-- ============================ CARD ON FILE =========================== -->
                    <div class="bcx-card">
                        <h3>Card on file</h3>
                        <p class="bcx-hint">
                            This is the card <strong>recurring billing charges</strong>. Card numbers are typed into
                            Helcim's own secure window &mdash; they never reach Pearland411, and we only ever store
                            the last four digits and a token.
                            <br>The card belongs to the account <strong><?= e($customer->name) ?></strong>, so it is
                            shared by every business that account owns.
                        </p>

                        <?php if (!empty($cards)) { ?>
                            <table class="responsive-table bordered">
                                <thead>
                                    <tr>
                                        <th>Card</th>
                                        <th>Expires</th>
                                        <th>Added</th>
                                        <th>Used for renewals</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cards as $card) { ?>
                                        <tr>
                                            <td>
                                                <?= !empty($card->card_brand) ? e($card->card_brand) . ' ' : '' ?>
                                                &bull;&bull;&bull;&bull;&nbsp;<?= e($card->card_last4) ?>
                                                <?php if (empty($card->helcim_card_token)) { ?>
                                                    <span class="bcx-pill bcx-bad" title="No Helcim token — this card cannot be charged">Not chargeable</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php // 01/2099 is the placeholder the self-serve checkout writes when
                                                      // the gateway returned no expiry — printing it as a real date
                                                      // would tell staff the card is good for another 70 years. ?>
                                                <?php if ((int) $card->exp_year >= 2099) { ?>
                                                    <span class="bcx-pill bcx-no">Not recorded</span>
                                                <?php } else { ?>
                                                    <?= str_pad((int) $card->exp_month, 2, '0', STR_PAD_LEFT) ?>/<?= (int) $card->exp_year ?>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?= !empty($card->created_at) ? e(us_date($card->created_at)) : '&mdash;' ?>
                                                <?php if (isset($card->card_source) && $card->card_source === 'admin') { ?>
                                                    <span class="bcx-pill bcx-no">by staff</span>
                                                <?php } elseif (isset($card->card_source)) { ?>
                                                    <span class="bcx-pill bcx-no">by customer</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($card->is_default) && !empty($card->helcim_card_token)) { ?>
                                                    <span class="bcx-pill bcx-yes">Yes</span>
                                                <?php } else { ?>
                                                    <span class="bcx-pill bcx-no">No</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= base_url('admin/billing-card-delete') ?>"
                                                      onsubmit="return confirm('Remove this card? Recurring charges will fail until another card is on file.');"
                                                      style="margin:0;">
                                                    <input type="hidden" name="listing_id" value="<?= (int) $customer->listing_id ?>">
                                                    <input type="hidden" name="card_id" value="<?= (int) $card->id ?>">
                                                    <button type="submit" class="db-list-edit" style="border:0;background:none;padding:0;cursor:pointer;">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p class="bcx-hint"><span class="bcx-pill bcx-no">No card on file</span>
                                &mdash; recurring billing cannot charge anything until one is added.</p>
                        <?php } ?>

                        <div class="bcx-actions">
                            <button type="button" class="btn btn-primary" id="bcx-add-card"
                                <?= empty($helcim_configured) ? 'disabled' : '' ?>>
                                <?= !empty($cards) ? 'Replace card' : 'Add a card' ?>
                            </button>
                        </div>
                        <p class="bcx-hint" style="margin-top:10px;">
                            Adding a card runs a zero-dollar check to confirm it is good, then stores it.
                            If your Helcim account does not allow that, use <em>Enter a card &amp; charge</em>
                            below instead &mdash; saving the card is part of taking the payment.
                        </p>
                    </div>

                    <!-- ============================ TAKE A PAYMENT ========================= -->
                    <div class="bcx-card">
                        <h3>Take a payment</h3>
                        <p class="bcx-hint">
                            A one-off charge &mdash; this month's invoice, a catch-up, an add-on. It is recorded in
                            the history below and does not change the recurring schedule.
                        </p>

                        <form method="post" action="<?= base_url('admin/billing-charge') ?>" id="bcx-pay-form">
                            <input type="hidden" name="listing_id" value="<?= (int) $customer->listing_id ?>">

                            <div class="bcx-row">
                                <div>
                                    <label for="bcx-amount">Amount (<?= e($sub_currency) ?>)</label>
                                    <input type="text" class="form-control" id="bcx-amount" name="amount"
                                           inputmode="decimal" placeholder="0.00"
                                           value="<?= e_attr($amount_default) ?>" required>
                                </div>
                                <div style="flex: 2 1 300px;">
                                    <label for="bcx-note">What is it for?</label>
                                    <input type="text" class="form-control" id="bcx-note" name="note" maxlength="255"
                                           placeholder="e.g. August 2026 bill">
                                </div>
                            </div>

                            <label class="bcx-check">
                                <input type="checkbox" id="bcx-save-card" checked>
                                Save this card for future renewals <span class="bcx-hint" style="margin:0;">(only applies when entering a new card)</span>
                            </label>

                            <div class="bcx-actions">
                                <button type="submit" class="btn btn-primary"
                                    <?= (empty($helcim_configured) || empty($has_card)) ? 'disabled' : '' ?>>
                                    Charge the card on file
                                </button>
                                <button type="button" class="btn btn-secondary" id="bcx-pay-new"
                                    <?= empty($helcim_configured) ? 'disabled' : '' ?>>
                                    Enter a card &amp; charge
                                </button>
                            </div>
                            <?php if (empty($has_card)) { ?>
                                <p class="bcx-hint" style="margin-top:10px;">
                                    No card is on file, so only <em>Enter a card &amp; charge</em> is available.
                                </p>
                            <?php } ?>
                        </form>
                    </div>

                    <!-- ========================= RECURRING BILLING ========================= -->
                    <div class="bcx-card">
                        <h3>Recurring billing</h3>

                        <?php if ($sub_active) { ?>
                            <p class="bcx-hint">
                                On <strong><?= e($customer->plan_name ?: 'a plan') ?></strong>,
                                term ends <?= !empty($customer->end_date) ? e(us_date($customer->end_date)) : '&mdash;' ?>.
                                <?php if ($renewal_amount > 0) { ?>
                                    Each renewal charges
                                    <strong class="bcx-num"><?= e($sub_currency) ?> <?= number_format((float) $renewal_amount, 2) ?></strong>
                                    <?php if (!empty($customer->custom_amount)) { ?>
                                        &mdash; the agreed price for this business. Clear the field below
                                        to go back to the package price.
                                    <?php } else { ?>
                                        &mdash; the package price. Set an agreed price below to override it,
                                        or edit the package under
                                        <a href="<?= base_url('admin/pricing-plan') ?>">Pricing Plans</a>.
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="bcx-pill bcx-bad">This plan has no price</span>
                                    &mdash; the renewal would be skipped. Set a price under
                                    <a href="<?= base_url('admin/pricing-plan') ?>">Pricing Plans</a> first.
                                <?php } ?>
                            </p>
                            <?php if (!empty($customer->renewal_attempts)) { ?>
                                <div class="bcx-warn">
                                    <?= (int) $customer->renewal_attempts ?> failed renewal
                                    attempt<?= ((int) $customer->renewal_attempts === 1) ? '' : 's' ?> so far.
                                    Last error: <?= e($customer->last_renewal_error) ?>.
                                    Saving below clears that count and starts the schedule again.
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="bcx-hint">
                                <?php if (!empty($customer->subscription_id)) { ?>
                                    This customer's last subscription is
                                    <strong><?= e($customer->sub_status) ?></strong>, and the renewal job only ever
                                    charges active ones. Saving below <strong>starts a new term</strong> on the plan
                                    you choose.
                                <?php } else { ?>
                                    This customer is not on a plan yet. Saving below <strong>starts one</strong>
                                    &mdash; use it after taking the first payment above.
                                <?php } ?>
                            </p>
                        <?php } ?>

                        <form method="post" action="<?= base_url('admin/billing-recurring-save') ?>">
                            <input type="hidden" name="listing_id" value="<?= (int) $customer->listing_id ?>">

                            <?php if (!$sub_active) { ?>
                                <div class="bcx-row">
                                    <div>
                                        <label for="bcx-plan">Package</label>
                                        <select name="plan_id" id="bcx-plan" class="form-control no-select2" required>
                                            <option value="">Choose a package&hellip;</option>
                                            <?php foreach ($plans as $plan) { ?>
                                                <option value="<?= (int) $plan->id ?>"
                                                    <?= (!empty($customer->plan_id) && (int) $customer->plan_id === (int) $plan->id) ? 'selected' : '' ?>>
                                                    <?= e($plan->name) ?>
                                                    &mdash; <?= e($sub_currency) ?> <?= number_format((float) $plan->price, 2) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="bcx-duration">Term</label>
                                        <select name="duration_id" id="bcx-duration" class="form-control no-select2">
                                            <option value="">Plan's standard term</option>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="bcx-row" style="margin-top:14px;">
                                <div>
                                    <?php /*
                                     * A package sets what the business gets; this sets what it
                                     * pays. Blank charges the package price, so nothing changes
                                     * for anyone who does not need a negotiated rate — and a
                                     * founding-member price entered here survives a later rise
                                     * in the plan's list price, which is the whole point.
                                     */ ?>
                                    <label for="bcx-custom">Agreed price (<?= e($sub_currency) ?>)<small style="display:block;font-weight:400;color:#666;">Leave blank to charge the package price.</small></label>
                                    <input type="text" class="form-control" id="bcx-custom" name="custom_amount"
                                           inputmode="decimal" placeholder="Package price"
                                           value="<?= e_attr(!empty($customer->custom_amount) ? number_format((float) $customer->custom_amount, 2, '.', '') : '') ?>">
                                </div>
                                <div>
                                    <label for="bcx-next">Next charge on<?php if (!$sub_active) { ?><small style="display:block;font-weight:400;color:#666;">With automatic charging off, this is simply when the term ends.</small><?php } ?></label>
                                    <input type="date" class="form-control" id="bcx-next" name="next_billing_date"
                                           value="<?= e_attr($next_default) ?>">
                                </div>
                                <div style="flex: 2 1 300px;">
                                    <label class="bcx-check" style="margin-top:26px;">
                                        <input type="checkbox" name="auto_renew" value="1"
                                            <?= (!$sub_active || !empty($customer->auto_renew)) ? 'checked' : '' ?>>
                                        Charge this card automatically on that date, and every term after
                                    </label>
                                </div>
                            </div>

                            <div class="bcx-actions">
                                <button type="submit" class="btn btn-primary">Save recurring billing</button>
                            </div>
                            <p class="bcx-hint" style="margin-top:10px;">
                                Charges are taken by the nightly job
                                (<code>php index.php cli renew_subscriptions</code>). If that cron is not running,
                                nothing will be charged no matter what is set here.
                            </p>
                        </form>
                    </div>

                    <!-- ============================ HISTORY =============================== -->
                    <div class="bcx-card">
                        <h3>Billing history</h3>
                        <?php if (!empty($transactions)) { ?>
                            <table class="responsive-table bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>For</th>
                                        <th>Helcim transaction</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $txn) { ?>
                                        <tr>
                                            <td><?= !empty($txn->created_at) ? e(us_date($txn->created_at)) : '&mdash;' ?></td>
                                            <td class="bcx-num">
                                                <?= e(!empty($txn->currency) ? $txn->currency : $sub_currency) ?>
                                                <?= number_format((float) $txn->amount, 2) ?>
                                            </td>
                                            <td>
                                                <?php if (isset($txn->status) && $txn->status === 'success') { ?>
                                                    <span class="bcx-pill bcx-yes">Paid</span>
                                                <?php } else { ?>
                                                    <span class="bcx-pill bcx-bad"><?= e(isset($txn->status) ? $txn->status : 'unknown') ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($txn->note)) { ?>
                                                    <?= e($txn->note) ?>
                                                <?php } elseif (!empty($txn->txn_ref) && strpos($txn->txn_ref, 'HLC-R-') === 0) { ?>
                                                    Automatic renewal
                                                <?php } elseif (!empty($txn->txn_ref) && strpos($txn->txn_ref, 'ADM-') === 0) { ?>
                                                    Taken by staff
                                                <?php } else { ?>
                                                    Online checkout
                                                <?php } ?>
                                                <?php if (!empty($txn->target_category_id)) {
                                                    // A category-specific banner/video: which category it runs in (0033).
                                                    $__tc = $this->db->select('category_name')->where('category_id', (int) $txn->target_category_id)->limit(1)->get('listing_categories')->row(); ?>
                                                    <br><small>Category: <?= e($__tc ? $__tc->category_name : ('#' . (int) $txn->target_category_id)) ?></small>
                                                <?php } ?>
                                            </td>
                                            <td><?= !empty($txn->helcim_transaction_id) ? e($txn->helcim_transaction_id) : '&mdash;' ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p class="bcx-hint">Nothing has been billed to this customer yet.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/pp_post.js') ?>"></script>
<script>
(function () {
    'use strict';

    var base   = '<?= base_url() ?>';
    var listingId = '<?= (int) $customer->listing_id ?>';
    var errBox = document.getElementById('bcx-js-err');

    function showErr(msg) {
        if (!errBox) { return; }
        errBox.textContent = msg;
        errBox.style.display = 'block';
        errBox.scrollIntoView({ block: 'nearest' });
    }
    function clearErr() {
        if (errBox) { errBox.style.display = 'none'; }
    }

    /* ---- Term dropdown follows the plan picker -------------------------- */
    var durations = <?= json_encode(
        array_map(function ($d) {
            return array(
                'plan_id' => (int) $d->plan_id,
                'id'      => (int) $d->id,
                'label'   => trim(((int) $d->duration_months) . ' month' . (((int) $d->duration_months) === 1 ? '' : 's')),
                'price'   => (float) $d->price,
            );
        }, $all_durations),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
    var currency = <?= json_encode($sub_currency, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var planSel = document.getElementById('bcx-plan');
    var durSel  = document.getElementById('bcx-duration');

    function fillDurations() {
        if (!planSel || !durSel) { return; }
        var planId = parseInt(planSel.value, 10) || 0;
        durSel.innerHTML = '';

        var standard = document.createElement('option');
        standard.value = '';
        standard.textContent = "Plan's standard term";
        durSel.appendChild(standard);

        durations.forEach(function (d) {
            if (d.plan_id !== planId) { return; }
            var o = document.createElement('option');
            o.value = d.id;
            o.textContent = d.label + ' — ' + currency + ' ' + d.price.toFixed(2);
            durSel.appendChild(o);
        });
    }
    if (planSel) {
        planSel.addEventListener('change', fillDurations);
        fillDurations();
    }

    /* ---- HelcimPay.js ---------------------------------------------------
       Opens Helcim's iframe. The card number is typed inside it and posted
       to Helcim directly; what comes back here is a token plus a hash the
       server re-computes before trusting any of it.
    ---------------------------------------------------------------------- */
    function runHelcim(button, params, busyLabel) {
        var label = button.textContent;
        function reset(msg) {
            if (msg) { showErr(msg); }
            button.disabled = false;
            button.textContent = label;
        }

        clearErr();
        button.disabled = true;
        button.textContent = busyLabel;

        ppPost(base + 'admin/billing-card-init', params, function (res) {
            var token = res.checkoutToken;

            var handler = function (event) {
                if (!event.data || event.data.eventName !== 'helcim-pay-js-' + token) { return; }

                if (event.data.eventStatus === 'ABORTED') {
                    window.removeEventListener('message', handler);
                    reset('Cancelled — nothing was charged and nothing was saved.');
                    return;
                }

                if (event.data.eventStatus !== 'SUCCESS') { return; }

                window.removeEventListener('message', handler);
                button.textContent = 'Finishing…';

                var resp = event.data.eventMessage;
                if (typeof resp === 'string') { try { resp = JSON.parse(resp); } catch (e) { /* leave as-is */ } }

                var container = (resp && resp.data) ? resp.data : resp;
                var txnData   = (container && container.data) ? container.data : container;
                var hash      = (container && container.hash) ? container.hash : ((resp && resp.hash) ? resp.hash : '');

                ppPost(
                    base + 'admin/billing-card-save',
                    { checkoutToken: token, transaction_json: JSON.stringify(txnData), hash: hash },
                    function (saved) { location.href = saved.redirect; },
                    function (msg) {
                        // Helcim has already accepted the card at this point —
                        // never word this as "try again", or staff will charge twice.
                        reset('');
                        showErr(msg);
                        button.textContent = 'Check Helcim before retrying';
                        button.disabled = true;
                    }
                );
            };

            window.addEventListener('message', handler);

            if (typeof appendHelcimPayIframe === 'function') {
                appendHelcimPayIframe(token);
            } else {
                reset('Helcim’s payment library did not load. Check this server can reach secure.helcim.app.');
            }
        }, function (msg) {
            reset(msg);
        });
    }

    var addBtn = document.getElementById('bcx-add-card');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            runHelcim(addBtn, { listing_id: listingId, mode: 'verify' }, 'Opening…');
        });
    }

    var payNewBtn = document.getElementById('bcx-pay-new');
    if (payNewBtn) {
        payNewBtn.addEventListener('click', function () {
            var amount = (document.getElementById('bcx-amount') || {}).value || '';
            var note   = (document.getElementById('bcx-note') || {}).value || '';
            var save   = document.getElementById('bcx-save-card');

            if (!amount.replace(/[^0-9.]/g, '')) {
                showErr('Enter the amount to charge first.');
                return;
            }

            runHelcim(payNewBtn, {
                listing_id: listingId,
                mode: 'charge',
                amount: amount,
                note: note,
                save_card: (save && save.checked) ? '1' : '0'
            }, 'Opening…');
        });
    }
})();
</script>

<?php if (!empty($helcim_configured)) { ?>
    <script type="text/javascript" src="<?= $helcimpay_js_url ?>"></script>
<?php } ?>
<!-- END -->
