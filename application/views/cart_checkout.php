<?php
// Cart-based checkout. $cart = ['items'=>[...], 'total', 'count', 'currency'];
// $is_logged_in, $helcim_configured, $helcimpay_js_url, $billing_info provided by User::checkout().
$cur = isset($cart['currency']) ? $cart['currency'] : 'USD';
function cc_cat_label($c)
{
    $m = array('business-listing' => 'Business Listing', 'add-ons' => 'Add-on', 'site-wide-banners' => 'Site-wide Banner',
        'category-banners' => 'Category Banner', 'site-wide-videos' => 'Site-wide Video', 'category-videos' => 'Category Video',
        'special' => 'Special Package');
    return isset($m[$c]) ? $m[$c] : '';
}
?>
<style>
    .cco-wrap{max-width:1050px;margin:30px auto;padding:0 16px;display:grid;grid-template-columns:1.4fr 1fr;gap:26px;}
    @media(max-width:820px){.cco-wrap{grid-template-columns:1fr;}}
    .cco-card{background:#fff;border:1px solid #e6e6ef;border-radius:14px;padding:22px;box-shadow:0 6px 20px rgba(0,0,0,.05);}
    .cco-card h2{font-size:20px;font-weight:800;color:#2b2b6b;margin:0 0 16px;}
    .cco-item{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid #f0f0f5;}
    .cco-item:last-child{border-bottom:none;}
    .cco-item .n{font-weight:600;color:#333;}
    .cco-item .c{font-size:12px;color:#999;}
    .cco-item .p{font-weight:700;color:#2b2b6b;white-space:nowrap;}
    .cco-item .x{cursor:pointer;color:#c0392b;font-weight:700;border:none;background:none;font-size:18px;line-height:1;}
    .cco-total{display:flex;justify-content:space-between;font-size:18px;font-weight:800;color:#2b2b6b;margin-top:16px;padding-top:14px;border-top:2px solid #eee;}
    .cco-btn{display:block;width:100%;text-align:center;background:#21d78d;color:#fff;border:none;padding:13px;border-radius:26px;font-weight:800;font-size:15px;cursor:pointer;margin-top:18px;}
    .cco-btn[disabled]{opacity:.55;cursor:not-allowed;}
    .cco-note{background:#fff3cd;border:1px solid #ffeeba;color:#856404;padding:12px;border-radius:8px;font-size:13px;margin-top:14px;}
    .cco-err{display:none;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:11px;border-radius:8px;margin-bottom:12px;}
    .cco-field{margin-bottom:12px;}
    .cco-field label{display:block;font-size:13px;color:#555;margin-bottom:4px;}
    .cco-field input{width:100%;padding:10px 12px;border:1px solid #d5d5e2;border-radius:8px;font-size:14px;}
    .cco-tabs{display:flex;gap:8px;margin-bottom:16px;}
    .cco-tab{flex:1;text-align:center;padding:10px;border-radius:24px;background:#eef0fb;color:#3a3a8c;font-weight:700;cursor:pointer;border:none;}
    .cco-tab.active{background:#2b2b6b;color:#fff;}
    .cco-pane{display:none;} .cco-pane.active{display:block;}
    .cco-muted{font-size:12px;color:#999;text-align:center;margin-top:10px;}
</style>

<div class="cco-wrap">
    <!-- Cart -->
    <div class="cco-card">
        <h2>Your order</h2>
        <?php foreach ($cart['items'] as $it): ?>
            <div class="cco-item">
                <div>
                    <div class="n"><?= htmlspecialchars($it['name']) ?></div>
                    <div class="c"><?= cc_cat_label($it['category']) ?></div>
                </div>
                <div class="p"><?= htmlspecialchars($cur) ?> <?= number_format($it['price'], 2) ?></div>
                <button class="x" type="button" data-plan="<?= (int) $it['plan_id'] ?>" title="Remove">&times;</button>
            </div>
        <?php endforeach; ?>
        <div class="cco-total"><span>Total</span><span><?= htmlspecialchars($cur) ?> <?= number_format($cart['total'], 2) ?></span></div>
        <p class="cco-muted"><a href="<?= base_url('pricing-plan') ?>">&larr; Keep browsing packages</a></p>
    </div>

    <!-- Pay / Auth -->
    <div class="cco-card">
        <div id="cco-err" class="cco-err"></div>

        <?php if ($is_logged_in): ?>
            <h2>Payment</h2>
            <?php if (!empty($helcim_configured)): ?>
                <p style="font-size:13px;color:#555;">You'll be charged <strong><?= htmlspecialchars($cur) ?> <?= number_format($cart['total'], 2) ?></strong> securely via Helcim. Your card details never touch our servers.</p>
                <button id="cco-pay" class="cco-btn" type="button">Pay securely</button>
            <?php else: ?>
                <div class="cco-note"><strong>Online payments are being set up.</strong> Please <a href="<?= base_url('contact-us') ?>">contact support</a> to complete your order.</div>
            <?php endif; ?>
        <?php else: ?>
            <h2>Sign in to complete your purchase</h2>
            <div class="cco-tabs">
                <button class="cco-tab active" type="button" data-pane="login">Sign in</button>
                <button class="cco-tab" type="button" data-pane="register">Create account</button>
            </div>

            <!-- Login -->
            <div class="cco-pane active" id="pane-login">
                <form method="post" action="<?= base_url('login') ?>">
                    <div class="cco-field"><label>Email</label><input type="email" name="email_id" required></div>
                    <div class="cco-field"><label>Password</label><input type="password" name="password" required></div>
                    <button type="submit" name="login_submit" value="submit" class="cco-btn">Sign in &amp; continue</button>
                </form>
                <p class="cco-muted"><a href="<?= base_url('forgot-password') ?>">Forgot password?</a></p>
            </div>

            <!-- Register -->
            <div class="cco-pane" id="pane-register">
                <form method="post" action="<?= base_url('register') ?>">
                    <div class="cco-field"><label>Full name</label><input type="text" name="name" required></div>
                    <div class="cco-field"><label>Email</label><input type="email" name="email_id" required></div>
                    <div class="cco-field"><label>Mobile</label><input type="text" name="mobile_number"></div>
                    <div class="cco-field"><label>Zip code</label><input type="text" name="zip_code"></div>
                    <div class="cco-field"><label>Password</label><input type="password" name="password" required></div>
                    <button type="submit" name="register_submit" value="submit" class="cco-btn">Create account &amp; continue</button>
                </form>
                <p class="cco-muted">Your cart is saved — you'll return here after signing up.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var base = '<?= base_url() ?>';
    var err = document.getElementById('cco-err');
    function showErr(m) { if (err) { err.textContent = m; err.style.display = 'block'; } }

    // Remove item -> reload (checkout() re-renders / redirects if empty).
    Array.prototype.forEach.call(document.querySelectorAll('.cco-item .x'), function (x) {
        x.addEventListener('click', function () {
            var b = new URLSearchParams(); b.append('plan_id', x.getAttribute('data-plan'));
            fetch(base + 'cart-remove', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: b, credentials: 'same-origin' })
                .then(function () { location.reload(); });
        });
    });

    // Guest auth tabs.
    Array.prototype.forEach.call(document.querySelectorAll('.cco-tab'), function (t) {
        t.addEventListener('click', function () {
            document.querySelectorAll('.cco-tab').forEach(function (e) { e.classList.remove('active'); });
            document.querySelectorAll('.cco-pane').forEach(function (e) { e.classList.remove('active'); });
            t.classList.add('active');
            document.getElementById('pane-' + t.getAttribute('data-pane')).classList.add('active');
        });
    });

    // Pay (logged-in) via HelcimPay.js.
    var payBtn = document.getElementById('cco-pay');
    if (payBtn) {
        payBtn.addEventListener('click', function () {
            payBtn.disabled = true; payBtn.textContent = 'Starting…';
            fetch(base + 'cart-pay-init', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: new URLSearchParams(), credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) { showErr(res.error || 'Could not start payment.'); payBtn.disabled = false; payBtn.textContent = 'Pay securely'; return; }
                    var token = res.checkoutToken;
                    var handler = function (event) {
                        if (!event.data || event.data.eventName !== 'helcim-pay-js-' + token) return;
                        if (event.data.eventStatus === 'ABORTED') { window.removeEventListener('message', handler); showErr('Payment cancelled.'); payBtn.disabled = false; payBtn.textContent = 'Pay securely'; return; }
                        if (event.data.eventStatus === 'SUCCESS') {
                            window.removeEventListener('message', handler);
                            payBtn.textContent = 'Finalizing…';
                            var resp = event.data.eventMessage; if (typeof resp === 'string') { try { resp = JSON.parse(resp); } catch (e) {} }
                            var container = (resp && resp.data) ? resp.data : resp;
                            var txnData = (container && container.data) ? container.data : container;
                            var hash = (container && container.hash) ? container.hash : ((resp && resp.hash) ? resp.hash : '');
                            var cb = new URLSearchParams(); cb.append('checkoutToken', token); cb.append('transaction_json', JSON.stringify(txnData)); cb.append('hash', hash);
                            fetch(base + 'process-checkout', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: cb, credentials: 'same-origin' })
                                .then(function (r) { return r.json(); })
                                .then(function (cr) { if (cr.success) { location.href = cr.redirect; } else { showErr(cr.error || 'Could not finalize.'); payBtn.disabled = false; payBtn.textContent = 'Pay securely'; } });
                        }
                    };
                    window.addEventListener('message', handler);
                    if (typeof appendHelcimPayIframe === 'function') { appendHelcimPayIframe(token); }
                    else { showErr('Payment library failed to load.'); payBtn.disabled = false; payBtn.textContent = 'Pay securely'; }
                })
                .catch(function () { showErr('Network error.'); payBtn.disabled = false; payBtn.textContent = 'Pay securely'; });
        });
    }
})();
</script>

<?php if (!empty($is_logged_in) && !empty($helcim_configured)): ?>
<script type="text/javascript" src="<?= $helcimpay_js_url ?>"></script>
<?php endif; ?>
