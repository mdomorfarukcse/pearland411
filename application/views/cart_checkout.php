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
    /* Shares the pricing page's design system (see views/pricing_plan.php). */
    .cco{--ink:#1c1b4b;--ink2:#5b5b7a;--line:#e7e7f2;--brand:#180f5e;--go:#21a179;
         background:#f7f8fc;padding:0 0 70px;color:var(--ink);}
    .cco *{box-sizing:border-box;}

    .cco-hero{background:linear-gradient(135deg,#180f5e 0%,#2d1f8f 100%);color:#fff;padding:40px 20px 52px;text-align:center;}
    .cco-hero h1{font-size:28px;font-weight:800;margin:0 0 6px;color:#fff;}
    .cco-hero p{margin:0;font-size:14px;color:#cfcbe8;}

    /* Step rail */
    .cco-steps{display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;}
    .cco-steps span{font-size:12px;font-weight:700;color:#b9b4de;display:flex;align-items:center;gap:7px;}
    .cco-steps span:not(:last-child):after{content:"";width:26px;height:1px;background:#5a4fa8;display:inline-block;}
    .cco-steps span.on{color:#fff;}
    .cco-steps i{width:19px;height:19px;border-radius:50%;background:#3a2f8f;color:#cfcbe8;font-style:normal;font-size:11px;display:inline-flex;align-items:center;justify-content:center;font-weight:800;}
    .cco-steps span.on i{background:#7bdcb5;color:#123f30;}

    .cco-wrap{max-width:1050px;margin:-32px auto 0;padding:0 16px;display:grid;grid-template-columns:1.35fr 1fr;gap:24px;align-items:start;}
    @media(max-width:860px){.cco-wrap{grid-template-columns:1fr;}}

    .cco-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:24px;box-shadow:0 8px 26px rgba(24,15,94,.08);}
    .cco-card h2{font-size:17px;font-weight:800;color:var(--ink);margin:0 0 18px;}

    .cco-item{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid var(--line);}
    .cco-item:last-of-type{border-bottom:none;}
    .cco-item .n{font-weight:700;color:var(--ink);font-size:14.5px;}
    .cco-item .c{font-size:11.5px;color:var(--ink2);margin-top:2px;text-transform:uppercase;letter-spacing:.4px;}
    .cco-item>div:first-child{flex:1;min-width:0;}
    .cco-item .p{font-weight:800;color:var(--ink);white-space:nowrap;font-size:15px;text-align:right;}
    .cco-item .x{cursor:pointer;color:#b9b4c8;border:none;background:none;font-size:20px;line-height:1;padding:2px 4px;border-radius:6px;transition:.15s;}
    .cco-item .x:hover{color:#c0392b;background:#fdeceb;}

    .cco-total{display:flex;justify-content:space-between;align-items:baseline;font-weight:800;color:var(--ink);margin-top:18px;padding-top:16px;border-top:2px solid var(--line);}
    .cco-total span:first-child{font-size:15px;}
    .cco-total span:last-child{font-size:24px;}

    .cco-charge{background:#f7f8fc;border:1px solid var(--line);border-radius:10px;padding:13px 15px;font-size:13.5px;color:var(--ink2);line-height:1.55;margin-bottom:4px;}
    .cco-charge strong{color:var(--ink);}

    .cco-btn{display:block;width:100%;text-align:center;background:var(--go);color:#fff;border:none;padding:14px;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer;margin-top:16px;transition:.15s;}
    .cco-btn:hover{background:#1c8c68;color:#fff;}
    .cco-btn[disabled]{opacity:.55;cursor:not-allowed;}

    /* Trust cues */
    .cco-trust{list-style:none;padding:16px 0 0;margin:16px 0 0;border-top:1px solid var(--line);}
    .cco-trust li{position:relative;padding-left:22px;font-size:12.5px;color:var(--ink2);line-height:1.5;margin-bottom:8px;}
    .cco-trust li:last-child{margin-bottom:0;}
    .cco-trust li:before{content:"";position:absolute;left:0;top:3px;width:14px;height:14px;border-radius:50%;background:#eaf7f2;}
    .cco-trust li:after{content:"";position:absolute;left:4.2px;top:6.5px;width:4.5px;height:7.5px;border-right:2px solid var(--go);border-bottom:2px solid var(--go);transform:rotate(42deg);}

    .cco-note{background:#fff8e6;border:1px solid #f2dfab;color:#8a6d1f;padding:13px;border-radius:10px;font-size:13px;margin-top:14px;line-height:1.55;}
    .cco-err{display:none;background:#fdeceb;border:1px solid #f3c3bf;color:#8c2f26;padding:12px;border-radius:10px;margin-bottom:14px;font-size:13.5px;}

    .cco-field{margin-bottom:13px;}
    .cco-field label{display:block;font-size:12.5px;font-weight:600;color:var(--ink2);margin-bottom:5px;}
    .cco-field input{width:100%;padding:11px 13px;border:1px solid #d9d9e8;border-radius:9px;font-size:14px;color:var(--ink);background:#fff;transition:.15s;}
    .cco-field input:focus{outline:none;border-color:var(--go);box-shadow:0 0 0 3px rgba(33,161,121,.13);}

    .cco-tabs{display:flex;gap:6px;margin-bottom:18px;background:#f1f1f8;padding:4px;border-radius:11px;}
    .cco-tab{flex:1;text-align:center;padding:10px;border-radius:8px;background:none;color:var(--ink2);font-weight:700;font-size:13.5px;cursor:pointer;border:none;transition:.15s;}
    .cco-tab.active{background:#fff;color:var(--ink);box-shadow:0 2px 6px rgba(24,15,94,.1);}
    .cco-pane{display:none;} .cco-pane.active{display:block;}

    .cco-muted{font-size:12.5px;color:var(--ink2);text-align:center;margin-top:12px;}
    .cco-muted a{color:var(--brand);font-weight:600;}
</style>

<div class="cco">
  <div class="cco-hero">
    <h1>Complete your order</h1>
    <p>You're one step away from going live on Pearland411.</p>
    <div class="cco-steps">
        <span><i>&#10003;</i> Choose packages</span>
        <span class="on"><i>2</i> Checkout</span>
        <span><i>3</i> You're live</span>
    </div>
  </div>

  <div class="cco-wrap">
    <!-- Cart -->
    <div class="cco-card">
        <h2>Your order &middot; <?= (int) $cart['count'] ?> item<?= (int) $cart['count'] === 1 ? '' : 's' ?></h2>
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
                <div class="cco-charge">
                    You'll be charged <strong><?= htmlspecialchars($cur) ?> <?= number_format($cart['total'], 2) ?></strong> today.
                </div>
                <button id="cco-pay" class="cco-btn" type="button">Pay <?= htmlspecialchars($cur) ?> <?= number_format($cart['total'], 2) ?> securely</button>
                <ul class="cco-trust">
                    <li>Processed by Helcim over an encrypted connection</li>
                    <li>Your card details never touch our servers</li>
                    <li>Your packages activate as soon as payment clears</li>
                </ul>
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
