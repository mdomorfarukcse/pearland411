<style>
    :root {
        --primary-color: #180f5e;
        --accent-color: #7bb73f;
        --text-dark: #333;
        --text-light: #666;
        --bg-light: #f8f9fa;
        --border-color: #e0e0e0;
    }

    .checkout-section {
        padding: 60px 0;
        margin-bottom: 120px;
        background: linear-gradient(135deg, #ffffff 0%, var(--bg-light) 100%);
    }

    .checkout-section .select2-selection {
        height: 50px;
    }

    .checkout-section .select2-selection .select2-selection__rendered {
        margin-top: 10px;
    }

    .checkout-section .section-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .checkout-section .section-header h2 {
        font-size: 35px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 12px;
    }

    .checkout-section .section-header .highlight {
        color: var(--accent-color);
    }

    .checkout-section .section-header .subtitle {
        font-size: 16px;
        color: var(--text-light);
        max-width: 550px;
        margin: 0 auto;
        line-height: 1.5;
    }

    .checkout-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 40px;
        align-items: start;
    }

    .checkout-section form {
        display: flex;
        flex-direction: column;
    }

    .checkout-form {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        border: 2px solid var(--border-color);
    }

    .checkout-form h3 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 15px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--accent-color);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-row.three-col {
        grid-template-columns: 1fr 1fr;
    }

    .form-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
    }

    .form-row-3 .form-group {
        float: none;
    }

    .order-summary {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        border: 2px solid var(--border-color);
    }

    .order-summary h3 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 15px;
    }

    .selected-plan {
        background: linear-gradient(135deg, var(--primary-color) 0%, #2a1a7a 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .selected-plan h4 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .selected-plan .price {
        font-size: 32px;
        font-weight: 800;
        color: var(--accent-color);
        margin: 10px 0;
    }

    .selected-plan .billing-info {
        font-size: 14px;
        opacity: 0.9;
    }

    .plan-features {
        margin-bottom: 20px;
    }

    .plan-features h4 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 15px;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-list li {
        padding: 5px 0 5px 20px;
        color: var(--text-dark);
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .feature-list li::before {
        content: "✓";
        color: var(--accent-color);
        font-weight: bold;
        margin-left: -20px;
    }

    .checkout-btn {
        width: 100%;
        background: var(--accent-color) !important;
        color: white !important;
        border: none;
        border-radius: 50px !important;
        padding: 15px 30px !important;
        font-size: 16px !important;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        cursor: pointer;
        text-align: center;
    }

    .checkout-btn:hover {
        background: var(--primary-color) !important;
        transform: translateY(-2px);
    }

    .checkout-btn:disabled {
        background: #ccc !important;
        cursor: not-allowed;
        transform: none;
    }

    .error-message {
        background: #fee;
        color: #c33;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .no-plan {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-light);
    }

    .no-plan a {
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 600;
    }

    .no-plan a:hover {
        text-decoration: underline;
    }

    .total-section {
        border-top: 2px solid var(--border-color);
        margin-top: 20px;
        padding-top: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .total-row.final {
        border-top: 1px solid var(--border-color);
        padding-top: 10px;
        margin-top: 10px;
        font-size: 18px;
        font-weight: 700;
    }

    .total-row .label {
        font-size: 14px;
        color: var(--text-light);
    }

    .total-row.final .label {
        font-size: 18px;
        color: var(--text-dark);
    }

    .total-row .value {
        font-weight: 600;
    }

    .total-row.final .value {
        color: var(--accent-color);
    }

    @media (max-width: 768px) {
        .checkout-content {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .checkout-section .section-header h2 {
            font-size: 28px;
        }

        .form-row,
        .form-row-3 {
            grid-template-columns: 1fr;
        }

        .checkout-form,
        .order-summary {
            padding: 20px;
        }
    }
</style>

<div class="checkout-section">
    <div class="section-header">
        <h2>Complete Your <span class="highlight">Purchase</span></h2>
        <p class="subtitle">Secure checkout with 256-bit SSL encryption</p>
    </div>

    <div class="checkout-container">
        <div class="checkout-content">
            <div class="checkout-form">
                <h3>Billing Information</h3>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="error-message">
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php endif; ?>
                <!-- debug area removed for production -->

                <form id="checkout-form" method="POST" action="<?= base_url('process-checkout') ?>">
                    <input type="hidden" name="plan_id" value="<?= isset($selected_plan) ? $selected_plan->id : '' ?>">

                    <!-- Auto-fill notification for upgrades -->
                    <?php if (isset($is_upgrade) && $is_upgrade): ?>
                        <div style="background: #e8f5e8; border: 1px solid #7bb73f; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <h4 style="color: #2e7d2e; margin: 0 0 10px 0;">Plan Upgrade</h4>
                            <p style="margin: 0; color: #2e7d2e;">You're upgrading from <strong><?= isset($existing_subscription->name) ? $existing_subscription->name : 'Current Plan' ?></strong> to <strong><?= $selected_plan->name ?></strong>. Your billing information has been pre-filled from your previous subscription.</p>
                        </div>
                    <?php elseif (isset($stored_payment_card) && $stored_payment_card): ?>
                        <div style="background: #f0f8ff; border: 1px solid #4a90e2; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <h4 style="color: #2c5aa0; margin: 0 0 10px 0;">Information Pre-filled</h4>
                            <p style="margin: 0; color: #2c5aa0;">We've pre-filled your billing information from your account. Please review and update if needed.</p>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name"
                                value="<?= isset($billing_info['first_name']) ? htmlspecialchars($billing_info['first_name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name"
                                value="<?= isset($billing_info['last_name']) ? htmlspecialchars($billing_info['last_name']) : '' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email"
                            value="<?= isset($billing_info['email']) ? htmlspecialchars($billing_info['email']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Billing Address *</label>
                        <input type="text" id="address" name="address"
                            value="<?= isset($billing_info['address']) ? htmlspecialchars($billing_info['address']) : '' ?>" required>
                    </div>

                    <div class="form-row three-col">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city"
                                value="<?= isset($billing_info['city']) ? htmlspecialchars($billing_info['city']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State/Province *</label>
                            <input type="text" id="state" name="state"
                                value="<?= isset($billing_info['state']) ? htmlspecialchars($billing_info['state']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="zip">ZIP Code *</label>
                            <input type="text" id="zip" name="zip"
                                value="<?= isset($billing_info['zip']) ? htmlspecialchars($billing_info['zip']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="text" id="phone" name="phone"
                                value="<?= isset($billing_info['phone']) ? htmlspecialchars($billing_info['phone']) : '' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="country">Country *</label>
                        <select id="country" name="country" required>
                            <option value="">Select Country</option>
                            <option value="US" <?= (isset($billing_info['country']) && $billing_info['country'] == 'US') ? 'selected' : '' ?>>United States</option>
                            <option value="CA" <?= (isset($billing_info['country']) && $billing_info['country'] == 'CA') ? 'selected' : '' ?>>Canada</option>
                            <option value="UK" <?= (isset($billing_info['country']) && $billing_info['country'] == 'UK') ? 'selected' : '' ?>>United Kingdom</option>
                            <option value="AU" <?= (isset($billing_info['country']) && $billing_info['country'] == 'AU') ? 'selected' : '' ?>>Australia</option>
                        </select>
                    </div>

                    <!-- Duration Dropdown -->
                    <div class="form-group">
                        <label for="duration_id">Duration</label>
                        <select name="duration_id" id="duration_id" class="form-control no-select2">
                            <?php if (isset($plan_durations) && is_array($plan_durations)): ?>
                                <?php foreach ($plan_durations as $duration): ?>
                                    <?php $currency = isset($duration->currency) && $duration->currency ? $duration->currency : 'USD'; ?>
                                    <option value="<?= $duration->id ?>" data-price="<?= $duration->price ?>" data-currency="<?= $currency ?>">
                                        <?= $duration->duration_months ?> Month (<?= number_format($duration->price, 2) ?> <?= $currency ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <h3 style="margin-top: 30px;">Payment</h3>

                    <?php if (!empty($helcim_configured)): ?>
                    <div id="helcim-pay-error" style="display:none; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:12px; border-radius:8px; margin-bottom:16px;"></div>
                    <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:14px;">
                        <input type="checkbox" id="auto_renew" name="auto_renew" value="1">
                        Automatically renew my plan each term &mdash; your card is charged again at each renewal, we email you
                        <?= (int) site_setting('renewal_reminder_days', 3) ?> days before every charge, and you can cancel anytime from your account
                        (<a href="<?= base_url('terms-of-use') ?>#refunds">refund &amp; cancellation policy</a>)
                    </label>
                    <button type="button" class="checkout-btn" id="helcim-pay-btn">Pay securely</button>
                    <p style="font-size:12px; color:#777; margin-top:10px;">Secured by Helcim. Your card details never touch our servers.</p>
                    <?php else: ?>
                    <div style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                        <strong>Online payments are being set up.</strong>
                        Please <a href="<?= base_url('contact-us') ?>">contact support</a> to activate your plan.
                    </div>
                    <button type="button" class="checkout-btn" disabled style="opacity: 0.6; cursor: not-allowed;">Online checkout coming soon</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>

                <?php if (isset($selected_plan) && $selected_plan): ?>
                    <div class="selected-plan">
                        <h4><?= strtoupper(htmlspecialchars($selected_plan->name)) ?></h4>
                        <div class="price"></div>
                        <div class="billing-info">Per <?= htmlspecialchars($selected_plan->billing_interval) ?></div>
                    </div>

                    <div class="plan-features">
                        <h4>What's Included:</h4>
                        <?php $ck_show_limits = !isset($selected_plan->category) || in_array($selected_plan->category, ['business-listing', 'special']); ?>
                        <ul class="feature-list">
                            <?php if ($ck_show_limits): ?>
                            <li><?= $selected_plan->listing_limit == -1 ? 'Unlimited' : $selected_plan->listing_limit ?> Listings</li>
                            <?php if ($selected_plan->product_limit > 0 || $selected_plan->product_limit == -1): ?>
                                <li><?= $selected_plan->product_limit == -1 ? 'Unlimited' : $selected_plan->product_limit ?> Products</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->event_limit > 0 || $selected_plan->event_limit == -1): ?>
                                <li><?= $selected_plan->event_limit == -1 ? 'Unlimited' : $selected_plan->event_limit ?> Events</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->classified_limit > 0 || $selected_plan->classified_limit == -1): ?>
                                <li><?= $selected_plan->classified_limit == -1 ? 'Unlimited' : $selected_plan->classified_limit ?> Classifieds</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->rental_limit > 0 || $selected_plan->rental_limit == -1): ?>
                                <li><?= $selected_plan->rental_limit == -1 ? 'Unlimited' : $selected_plan->rental_limit ?> Rentals</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->house_limit > 0 || $selected_plan->house_limit == -1): ?>
                                <li><?= $selected_plan->house_limit == -1 ? 'Unlimited' : $selected_plan->house_limit ?> Houses</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->blog_limit > 0 || $selected_plan->blog_limit == -1): ?>
                                <li><?= $selected_plan->blog_limit == -1 ? 'Unlimited' : $selected_plan->blog_limit ?> Blogs</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->video_limit > 0 || $selected_plan->video_limit == -1): ?>
                                <li><?= $selected_plan->video_limit == -1 ? 'Unlimited' : $selected_plan->video_limit ?> Videos</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->news_limit > 0 || $selected_plan->news_limit == -1): ?>
                                <li><?= $selected_plan->news_limit == -1 ? 'Unlimited' : $selected_plan->news_limit ?> News</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->ad_limit > 0 || $selected_plan->ad_limit == -1): ?>
                                <li><?= $selected_plan->ad_limit == -1 ? 'Unlimited' : $selected_plan->ad_limit ?> Ads</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->featured_listing): ?>
                                <li>Featured Listings</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->priority_support): ?>
                                <li>Priority Support</li>
                            <?php endif; ?>
                            <?php if ($selected_plan->analytics_access): ?>
                                <li>Analytics Dashboard</li>
                            <?php endif; ?>
                            <?php else: ?>
                            <?php if (!empty($selected_plan->description)): ?>
                                <li><?= htmlspecialchars($selected_plan->description) ?></li>
                            <?php endif; ?>
                            <li><?= (int) $selected_plan->duration_days ?> days placement</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="total-section">
                        <div class="total-row">
                            <span class="label">Subtotal:</span>
                            <span class="value"></span>
                        </div>
                        <div class="total-row">
                            <span class="label">Tax (10%):</span>
                            <span class="value"></span>
                        </div>
                        <div class="total-row final">
                            <span class="label">Total:</span>
                            <span class="value"></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-plan">
                        <p>No plan selected</p>
                        <a href="<?= base_url('user/pricing_plan') ?>">Choose a plan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Input formatting
        (function() {
            const cardNumber = document.getElementById('card_number');
            if (cardNumber) {
                cardNumber.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = (value.match(/.{1,4}/g) || []).join(' ') || value;
                    if (formattedValue.length <= 19) e.target.value = formattedValue;
                });
            }

            const cvv = document.getElementById('cvv');
            if (cvv) cvv.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/gi, '');
            });
        })();

        // Form submit UI
        (function() {
            const form = document.getElementById('checkout-form');
            const submitBtn = document.getElementById('submit-btn');
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                    if (document.body.classList) document.body.classList.add('loading');
                });
            }
        })();

        // Duration / price logic (robust: match by value, observe mutations, polling fallback)
        (function() {
            const durationSelect = document.getElementById('duration_id');
            const priceSpan = document.querySelector('.selected-plan .price');
            // debug DOM element removed; keep logic silent in production
            const debugDiv = null;

            function findValueSpan(labelText) {
                const rows = document.querySelectorAll('.total-row');
                const needle = String(labelText).trim().toLowerCase();
                for (let row of rows) {
                    const label = row.querySelector('.label');
                    if (!label) continue;
                    // match whole word(s) only: split label by non-word chars and check exact equality
                    const words = label.textContent.trim().toLowerCase().split(/[^a-z0-9]+/).filter(Boolean);
                    for (const w of words) {
                        if (w === needle) return row.querySelector('.value');
                    }
                }
                return null;
            }

            let subtotalSpan = findValueSpan('subtotal');
            let taxSpan = findValueSpan('tax');
            let totalSpan = findValueSpan('total');

            // Fallbacks if label-based lookup fails (some templates/styles may differ)
            if (!subtotalSpan) subtotalSpan = document.querySelector('.total-section .total-row .value');
            if (!taxSpan) taxSpan = document.querySelector('.total-section .total-row:nth-child(2) .value');
            if (!totalSpan) totalSpan = document.querySelector('.total-row.final .value') || document.querySelectorAll('.total-row .value')[document.querySelectorAll('.total-row .value').length - 1] || null;


            function getSelectedIndexByValueOrSelected(options) {
                if (!options || options.length === 0) return -1;
                // prefer explicit selectedIndex if valid
                try {
                    const selectVal = durationSelect.value;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === selectVal) return i;
                    }
                } catch (e) {
                    // ignore
                }

                // fallback to option.selected
                for (let i = 0; i < options.length; i++) {
                    if (options[i].selected) return i;
                }

                // last-resort: use selectedIndex property
                if (durationSelect && typeof durationSelect.selectedIndex === 'number' && durationSelect.selectedIndex >= 0) return durationSelect.selectedIndex;

                return 0;
            }

            function updateOrderSummary() {
                if (!durationSelect) return;
                const opts = durationSelect.options;
                if (!opts || opts.length === 0) return;

                let selIndex = getSelectedIndexByValueOrSelected(opts);
                if (selIndex < 0) selIndex = 0;
                const sel = opts[selIndex];
                let price = parseFloat(sel.getAttribute('data-price'));
                let currency = sel.getAttribute('data-currency') || 'USD';

                // fallback to first valid price
                if (isNaN(price)) {
                    for (let i = 0; i < opts.length; i++) {
                        const p = parseFloat(opts[i].getAttribute('data-price'));
                        if (!isNaN(p)) {
                            price = p;
                            currency = opts[i].getAttribute('data-currency') || 'USD';
                            selIndex = i;
                            break;
                        }
                    }
                }


                // Re-query the subtotal/tax/total elements here in case the DOM changed after initial load
                let localSubtotalSpan = subtotalSpan || findValueSpan('subtotal') || document.querySelector('.total-section .total-row .value');
                let localTaxSpan = taxSpan || findValueSpan('tax') || document.querySelector('.total-section .total-row:nth-child(2) .value');
                let localTotalSpan = totalSpan || findValueSpan('total') || document.querySelector('.total-row.final .value') || (document.querySelectorAll('.total-row .value')[document.querySelectorAll('.total-row .value').length - 1] || null);

                if (!isNaN(price)) {
                    // compute amounts consistently: subtotal = price, tax = subtotal * 0.10, total = subtotal + tax
                    const subtotal = parseFloat(price);
                    const tax = Math.round(subtotal * 0.10 * 100) / 100;
                    const total = Math.round((subtotal + tax) * 100) / 100;

                    if (priceSpan) priceSpan.textContent = subtotal.toFixed(2) + ' ' + currency;
                    if (localSubtotalSpan) localSubtotalSpan.textContent = subtotal.toFixed(2) + ' ' + currency;
                    if (localTaxSpan) localTaxSpan.textContent = tax.toFixed(2) + ' ' + currency;
                    if (localTotalSpan) localTotalSpan.textContent = total.toFixed(2) + ' ' + currency;
                } else {
                    if (priceSpan) priceSpan.textContent = '';
                    if (localSubtotalSpan) localSubtotalSpan.textContent = '';
                    if (localTaxSpan) localTaxSpan.textContent = '';
                    if (localTotalSpan) localTotalSpan.textContent = '';
                }
            }

            if (durationSelect) {
                // Update when standard change fires
                durationSelect.addEventListener('change', updateOrderSummary);

                // Observe DOM mutations on the select and its options (covers many UI libraries that replace/select elements)
                try {
                    const observer = new MutationObserver(function(mutations) {
                        for (const m of mutations) {
                            if (m.type === 'attributes' || m.type === 'childList') {
                                updateOrderSummary();
                                break;
                            }
                        }
                    });

                    observer.observe(durationSelect, {
                        attributes: true,
                        childList: true,
                        subtree: true
                    });

                    // observe each option for attribute changes
                    for (let i = 0; i < durationSelect.options.length; i++) {
                        try {
                            observer.observe(durationSelect.options[i], {
                                attributes: true,
                                attributeFilter: ['selected', 'data-price', 'data-currency']
                            });
                        } catch (e) {
                            /* ignore */
                        }
                    }
                } catch (e) {
                    // MutationObserver not supported — ignore
                }

                // Polling fallback if some UI library intercepts native events
                let lastVal = durationSelect.value;
                setInterval(function() {
                    try {
                        if (durationSelect.value !== lastVal) {
                            lastVal = durationSelect.value;
                            updateOrderSummary();
                        }
                    } catch (e) {
                        // ignore
                    }
                }, 300);

                // initial render + slight delay
                updateOrderSummary();
                setTimeout(updateOrderSummary, 150);
            } else {
                // no visible debug in production
            }
        })();
    });
</script>

<?php if (!empty($helcim_configured)): ?>
<!-- HelcimPay.js: initialize server-side, render the hosted modal, validate. -->
<script type="text/javascript" src="<?= $helcimpay_js_url ?>"></script>
<script>
(function () {
    var btn    = document.getElementById('helcim-pay-btn');
    var errBox = document.getElementById('helcim-pay-error');
    if (!btn) return;

    // CSRF is auto-appended to fetch() by assets/js/csrf.js, so plain
    // URLSearchParams bodies are enough here.
    function showError(msg) {
        if (errBox) { errBox.textContent = msg; errBox.style.display = 'block'; }
    }
    function reset() { btn.disabled = false; btn.textContent = 'Pay securely'; }

    btn.addEventListener('click', function () {
        if (errBox) errBox.style.display = 'none';
        btn.disabled = true; btn.textContent = 'Starting…';

        var planEl = document.querySelector('[name="plan_id"]');
        var durEl  = document.getElementById('duration_id');
        var arEl   = document.getElementById('auto_renew');
        var body   = new URLSearchParams();
        body.append('plan_id', planEl ? planEl.value : '');
        body.append('duration_id', durEl ? durEl.value : '');
        body.append('auto_renew', (arEl && arEl.checked) ? '1' : '0');

        fetch('<?= base_url('helcim-init') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) { showError(res.error || 'Could not start payment.'); reset(); return; }
            var token = res.checkoutToken;

            var handler = function (event) {
                if (!event.data || event.data.eventName !== 'helcim-pay-js-' + token) return;
                if (event.data.eventStatus === 'ABORTED') {
                    window.removeEventListener('message', handler);
                    showError('Payment was cancelled.'); reset();
                    return;
                }
                if (event.data.eventStatus === 'SUCCESS') {
                    window.removeEventListener('message', handler);
                    btn.textContent = 'Finalizing…';

                    var resp = event.data.eventMessage;
                    if (typeof resp === 'string') { try { resp = JSON.parse(resp); } catch (e) {} }
                    // Helcim nests the transaction under .data, with a sibling .hash.
                    var container = (resp && resp.data) ? resp.data : resp;
                    var txnData   = (container && container.data) ? container.data : container;
                    var hash      = (container && container.hash) ? container.hash
                                    : ((resp && resp.hash) ? resp.hash : '');

                    var confirmBody = new URLSearchParams();
                    confirmBody.append('checkoutToken', token);
                    confirmBody.append('transaction_json', JSON.stringify(txnData));
                    confirmBody.append('hash', hash);

                    fetch('<?= base_url('process-checkout') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: confirmBody,
                        credentials: 'same-origin'
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (cr) {
                        if (cr.success) { window.location.href = cr.redirect; }
                        else { showError(cr.error || 'Payment could not be finalized.'); reset(); }
                    })
                    .catch(function () { showError('Network error finalizing payment.'); reset(); });
                }
            };
            window.addEventListener('message', handler);

            // Render the Helcim hosted modal.
            if (typeof appendHelcimPayIframe === 'function') {
                appendHelcimPayIframe(token);
            } else {
                showError('Payment library failed to load. Please refresh and try again.'); reset();
            }
        })
        .catch(function () { showError('Network error starting payment.'); reset(); });
    });
})();
</script>
<?php endif; ?>