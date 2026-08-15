<?php
/**
 * Advertising catalog / pricing page.
 *
 * NOTE: this is a view *fragment* — it renders between the `header` and
 * `footer` views, so it must not emit <!DOCTYPE>/<html>/<head>/<body>.
 *
 * $plans        rows from plans (grouped by `category`)
 * $my_packages  what the logged-in user owns + expiry (may be empty)
 * $cart         current session cart summary
 */
$catLabels = array(
    'business-listing'  => 'Business Listing',
    'add-ons'           => 'Add-ons',
    'site-wide-banners' => 'Site Wide Banners',
    'category-banners'  => 'Category Specific Banners',
    'site-wide-videos'  => 'Site Wide Videos',
    'category-videos'   => 'Category Specific Videos',
    'special'           => 'Special Package',
);
$catBlurbs = array(
    'business-listing'  => 'Your core presence in the directory. Pick one tier.',
    'add-ons'           => 'Optional boosts you can stack on any listing.',
    'site-wide-banners' => 'Seen on every page of Pearland411.',
    'category-banners'  => 'Targeted to shoppers browsing your category.',
    'site-wide-videos'  => 'Your video in the right rail, site-wide.',
    'category-videos'   => 'Your video shown inside your category.',
    'special'           => 'Everything bundled at the best value.',
);

// Group the catalog by category, preserving sort order.
$grouped = array();
foreach ($plans as $p) {
    $c = isset($p->category) && $p->category ? $p->category : 'business-listing';
    $grouped[$c][] = $p;
}
$cartIds = array();
if (!empty($cart['items'])) {
    foreach ($cart['items'] as $ci) { $cartIds[] = (int) $ci['plan_id']; }
}

// Signed in? Distinct from having a subscription — the free-tier button used to
// conflate the two and sent logged-in visitors without a plan to /register.
$isLoggedIn = !empty($is_logged_in);

/** Bullet list for a card: limits for listing/special tiers, placement details otherwise. */
function pp_features($plan, $cat)
{
    $f = array();
    if (in_array($cat, array('business-listing', 'special'))) {
        $map = array(
            'listing_limit' => 'Listing', 'product_limit' => 'Product', 'event_limit' => 'Event',
            'classified_limit' => 'Classified', 'rental_limit' => 'Rental', 'house_limit' => 'House',
            'blog_limit' => 'Blog', 'video_limit' => 'Video', 'news_limit' => 'News', 'ad_limit' => 'Ad',
        );
        foreach ($map as $col => $label) {
            if (!isset($plan->$col)) continue;
            $v = (int) $plan->$col;
            if ($v === 0) continue;
            $f[] = ($v === -1 ? 'Unlimited' : $v) . ' ' . $label . ($v === 1 ? '' : 's');
        }
        if (!empty($plan->featured_listing))  $f[] = 'Featured placement';
        if (!empty($plan->priority_support))  $f[] = 'Priority support';
        if (!empty($plan->analytics_access))  $f[] = 'Analytics dashboard';
    } elseif (!empty($plan->duration_days)) {
        $f[] = (int) $plan->duration_days . ' days placement';
    }
    return $f;
}
?>

<style>
    /* Brand tokens (2026-08): the page was #180f5e navy with a #21a179 teal
       accent and a mint headline — none of which appear anywhere else on the
       site. Same values as assets/css/directory.css. Structure untouched. */
    .pp{--ink:#20245B;--ink2:#5A6472;--line:#E4E6EC;--brand:#20245B;--go:#79B82A;--go-d:#5E9220;--bu:#6D263D;}
    .pp{background:#F7F5F0;padding:0 0 90px;color:var(--ink);}
    .pp *{box-sizing:border-box;}

    /* Hero */
    .pp-hero{background:linear-gradient(135deg,#171A45 0%,#20245B 55%,#6D263D 100%);color:#fff;padding:56px 20px 44px;text-align:center;}
    .pp-hero h1{font-size:34px;font-weight:800;margin:0 0 10px;color:#fff;}
    .pp-hero h1 span{color:#A9E060;}
    .pp-hero p{margin:0 auto;max-width:620px;font-size:15px;line-height:1.6;color:#CDD4E6;}
    .pp-callus{margin:18px auto 0 !important;font-size:15px;color:#fff !important;}
    .pp-callus a{color:#A9E060;font-weight:700;text-decoration:underline;}
    .pp-callus a:hover{color:#fff;}

    .pp-wrap{max-width:1180px;margin:0 auto;padding:0 20px;}

    /* Inline error (CSRF expiry, network, server) */
    .pp-err{display:none;background:#fdeceb;border:1px solid #f3c3bf;color:#8c2f26;padding:13px 16px;border-radius:11px;margin:-14px auto 22px;font-size:14px;font-weight:600;position:relative;}

    /* My packages */
    .pp-mine{background:#fff;border:1px solid var(--line);border-radius:14px;padding:20px 22px;margin:-28px auto 34px;box-shadow:0 8px 26px rgba(31,36,48,.08);position:relative;}
    .pp-mine h3{font-size:15px;font-weight:800;margin:0 0 14px;color:var(--ink);}
    .pp-mine-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px;}
    .pp-mine-item{border:1px solid var(--line);border-left:3px solid var(--go);border-radius:10px;padding:11px 13px;background:#FBFCF9;}
    .pp-mine-item .n{font-weight:700;font-size:14px;}
    .pp-mine-item .e{font-size:12px;color:var(--ink2);margin-top:3px;}
    .pp-mine-item.soon{border-left-color:var(--bu);}
    .pp-mine-item.soon .e{color:var(--bu);font-weight:600;}
    .pp-mine .pill{display:inline-block;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;background:#F1F7E5;color:var(--go);margin-left:6px;}

    /* Section heading */
    .pp-sec{margin:44px 0 0;}
    .pp-sec-head{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap;margin-bottom:6px;}
    .pp-sec-head h2{font-size:23px;font-weight:800;margin:0;color:var(--ink);}
    .pp-sec-head .hint{font-size:13px;color:var(--ink2);}
    .pp-sec-rule{height:3px;width:54px;background:var(--go);border-radius:3px;margin:10px 0 22px;}

    /* Cards */
    .pp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(258px,1fr));gap:20px;}
    .pp-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:24px 22px 22px;display:flex;flex-direction:column;transition:.18s;position:relative;}
    .pp-card:hover{transform:translateY(-3px);box-shadow:0 14px 32px rgba(31,36,48,.11);border-color:#D6DAE4;}
    .pp-card.is-selected{border-color:var(--go);box-shadow:0 0 0 2px rgba(121,184,42,.20);}
    .pp-card.owned{border-color:#CFE0AE;background:#FCFEF8;}
    .pp-badge{position:absolute;top:-10px;right:16px;background:var(--go);color:#fff;font-size:11px;font-weight:700;padding:4px 11px;border-radius:20px;letter-spacing:.3px;}
    .pp-badge.pop{background:var(--bu);}
    .pp-name{font-size:14px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--ink);margin:0 0 8px;line-height:1.35;}
    .pp-desc{font-size:13px;line-height:1.6;color:var(--ink2);margin:0 0 18px;}
    .pp-price{display:flex;align-items:baseline;gap:5px;margin-bottom:4px;}
    .pp-price .amt{font-size:36px;font-weight:800;color:var(--ink);line-height:1;}
    .pp-price .per{font-size:13px;color:var(--ink2);}
    .pp-feats{list-style:none;padding:0;margin:18px 0 22px;border-top:1px solid var(--line);padding-top:16px;}
    .pp-feats li{position:relative;padding-left:23px;font-size:13.5px;line-height:1.5;color:#3F4657;margin-bottom:9px;}
    .pp-feats li:before{content:"";position:absolute;left:0;top:4px;width:15px;height:15px;border-radius:50%;background:#F1F7E5;}
    .pp-feats li:after{content:"";position:absolute;left:4.5px;top:8px;width:5px;height:8px;border-right:2px solid var(--go);border-bottom:2px solid var(--go);transform:rotate(42deg);}
    .pp-cta{margin-top:auto;}
    .pp-buy{display:block;width:100%;text-align:center;background:var(--brand);color:#fff;border:none;padding:12px;border-radius:10px;font-family:inherit;font-weight:700;font-size:14.5px;cursor:pointer;text-decoration:none;transition:.15s;}
    .pp-buy:hover{background:#171A45;color:#fff;}
    .pp-buy.free{background:#fff;color:var(--brand);border:1.5px solid var(--brand);}
    .pp-buy.ownedbtn{background:#F1F7E5;color:var(--go);cursor:default;}
    .pp-add{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;margin-top:9px;background:none;border:1.5px dashed #D6DAE4;color:#5A6472;padding:9px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;transition:.15s;}
    .pp-add:hover{border-color:var(--go);color:var(--go);}
    .pp-add.on{background:#F1F7E5;border:1.5px solid var(--go);color:var(--go);}

    /* Sticky bundle bar */
    .pp-bar{position:fixed;left:0;right:0;bottom:0;background:#171A45;color:#fff;padding:13px 20px;display:none;z-index:9999;box-shadow:0 -6px 24px rgba(0,0,0,.22);}
    .pp-bar.show{display:block;}
    .pp-bar-in{max-width:1180px;margin:0 auto;display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
    .pp-bar .sum{font-size:14px;flex:1;min-width:180px;}
    .pp-bar .sum b{font-size:19px;margin-left:6px;}
    .pp-bar .items{font-size:12px;color:#AEB7CC;display:block;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:640px;}
    .pp-bar a.co{background:var(--go);color:#fff;padding:11px 26px;border-radius:9px;font-weight:700;text-decoration:none;font-size:14.5px;}
    .pp-bar button.clr{background:none;border:none;color:#AEB7CC;font-size:13px;cursor:pointer;text-decoration:underline;}

    @media(max-width:640px){
        .pp-hero h1{font-size:26px;}
        .pp-grid{grid-template-columns:1fr;}
        .pp-bar-in{flex-direction:column;align-items:stretch;text-align:center;gap:9px;}
        .pp-bar a.co{display:block;}
        .pp-bar .items{display:none;}
    }
</style>

<div class="pp">
    <div class="pp-hero">
        <h1>Advertise on <span>Pearland411</span></h1>
        <p>Get in front of local customers searching for businesses like yours. Choose a listing tier, then boost it with banners, videos and add-ons.</p>
        <?php
        /* Not everyone buying a listing wants to check out online. The line
           here catches them before they scroll; the full panel with the
           callback form is at the foot of the catalog. */
        $pp_phone = trim((string) site_setting('contact_phone', '281-482-2031'));
        ?>
        <?php if ($pp_phone !== ''): ?>
            <p class="pp-callus">
                Rather order over the phone? Call
                <a href="tel:<?= e_attr(preg_replace('/[^0-9+]/', '', $pp_phone)) ?>"><?= e($pp_phone) ?></a>
                and we will set it up for you &mdash; or
                <a href="#talk-to-us">ask us to call you back</a>.
            </p>
        <?php endif; ?>
    </div>

    <div class="pp-wrap">

        <?php // A leaderboard at the head of the catalogue. This page sells the
              // zones, so it also shows one: an advertiser can see the position
              // they are about to buy on the page that describes it. ?>

        <div class="pp-err" id="ppErr"></div>

        <?php
        /* Server-side notices land here too: /start-free and /buy-now both bounce
           back to this page with a flash message, which previously had nowhere
           to render. `display:block` overrides .pp-err's hidden default (the JS
           above only unhides it for cart errors). */
        $pp_notice = $this->session->flashdata('error') ?: $this->session->flashdata('message');
        ?>
        <?php if (!empty($pp_notice)): ?>
            <div class="pp-err" style="display:block;"><?= htmlspecialchars($pp_notice, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($my_packages)): ?>
            <div class="pp-mine">
                <h3>Your active packages</h3>
                <div class="pp-mine-grid">
                    <?php foreach ($my_packages as $mp):
                        $soon = ($mp['days_left'] !== null && $mp['days_left'] <= 14); ?>
                        <div class="pp-mine-item <?= $soon ? 'soon' : '' ?>">
                            <div class="n"><?= htmlspecialchars($mp['name']) ?>
                                <?php if (!empty($mp['auto_renew'])): ?><span class="pill">Auto-renews</span><?php endif; ?>
                            </div>
                            <div class="e">
                                <?php if (!empty($mp['expires_at'])): ?>
                                    <?= !empty($mp['auto_renew']) ? 'Renews' : 'Expires' ?>
                                    <?= date('M j, Y', strtotime($mp['expires_at'])) ?>
                                    <?php if ($mp['days_left'] !== null): ?>
                                        · <?= (int) $mp['days_left'] ?> day<?= $mp['days_left'] == 1 ? '' : 's' ?> left
                                    <?php endif; ?>
                                <?php else: ?>
                                    Active
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($plans)): ?>
            <div class="pp-sec"><p style="color:#5A6472;">Packages are being updated — please check back shortly.</p></div>
        <?php endif; ?>

        <?php foreach ($catLabels as $catKey => $catLabel): ?>
            <?php if (empty($grouped[$catKey])) continue; ?>
            <section class="pp-sec">
                <div class="pp-sec-head">
                    <h2><?= htmlspecialchars($catLabel) ?></h2>
                    <span class="hint"><?= isset($catBlurbs[$catKey]) ? htmlspecialchars($catBlurbs[$catKey]) : '' ?></span>
                </div>
                <div class="pp-sec-rule"></div>

                <div class="pp-grid">
                    <?php foreach ($grouped[$catKey] as $plan):
                        $price    = (float) $plan->price;
                        $isFree   = $price <= 0;
                        $inCart   = in_array((int) $plan->id, $cartIds);
                        $isOwned  = (isset($user_subscription) && $user_subscription && (int) $user_subscription->plan_id === (int) $plan->id);
                        $feats    = pp_features($plan, $catKey);
                        $per      = $catKey === 'business-listing' || $catKey === 'special'
                                    ? 'per ' . htmlspecialchars($plan->billing_interval)
                                    : 'one-time';
                        ?>
                        <div class="pp-card <?= $inCart ? 'is-selected' : '' ?> <?= $isOwned ? 'owned' : '' ?>" data-card="<?= (int) $plan->id ?>">
                            <?php if ($isOwned): ?>
                                <span class="pp-badge">Your plan</span>
                            <?php elseif (!empty($plan->featured_listing) && $catKey === 'business-listing'): ?>
                                <span class="pp-badge pop">Most popular</span>
                            <?php elseif ($catKey === 'special'): ?>
                                <span class="pp-badge pop">Best value</span>
                            <?php endif; ?>

                            <h3 class="pp-name"><?= htmlspecialchars($plan->name) ?></h3>
                            <?php if (!empty($plan->description)): ?>
                                <p class="pp-desc"><?= htmlspecialchars($plan->description) ?></p>
                            <?php endif; ?>

                            <div class="pp-price">
                                <span class="amt">$<?= number_format($price, ($price - floor($price)) > 0 ? 2 : 0) ?></span>
                                <span class="per"><?= $isFree ? '' : $per ?></span>
                            </div>

                            <?php if (!empty($feats)): ?>
                                <ul class="pp-feats">
                                    <?php foreach ($feats as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div style="margin:18px 0 22px;border-top:1px solid var(--line);"></div>
                            <?php endif; ?>

                            <div class="pp-cta">
                                <?php if ($isOwned): ?>
                                    <a class="pp-buy ownedbtn" href="<?= base_url('user/my-account') ?>">Active — manage</a>
                                <?php elseif ($isFree): ?>
                                    <?php if (!$isLoggedIn): ?>
                                        <a class="pp-buy free" href="<?= base_url('register') ?>">Get started free</a>
                                    <?php elseif (!empty($user_subscription)): ?>
                                        <?php /* Already on another active plan — the free tier would be a downgrade. */ ?>
                                        <a class="pp-buy free" href="<?= base_url('user/user-listing') ?>">Go to my listings</a>
                                    <?php else: ?>
                                        <?php /* Signed in with no plan: start the free tier here. This used to link
                                                  to /register, which bounced an already-logged-in user to a signup
                                                  form they had no use for. */ ?>
                                        <form method="post" action="<?= base_url('start-free') ?>" style="margin:0;">
                                            <input type="hidden" name="plan_id" value="<?= (int) $plan->id ?>">
                                            <button type="submit" class="pp-buy free">Get started free</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a class="pp-buy" href="<?= base_url('buy-now?plan_id=' . (int) $plan->id) ?>">Buy Now</a>
                                    <button type="button" class="pp-add <?= $inCart ? 'on' : '' ?>"
                                            data-plan="<?= (int) $plan->id ?>"
                                            data-cat="<?= htmlspecialchars($catKey) ?>">
                                        <span class="t"><?= $inCart ? '✓ Added to bundle' : '+ Add to bundle' ?></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>


        <?php
        /* Talk to a person: phone, email and a callback form. Shared partial —
           see views/partials/sales_help.php. */
        include APPPATH . 'views/partials/sales_help.php';
        ?>


<!-- Sticky bundle bar: appears only when 2+ things are combined -->
<div class="pp-bar" id="ppBar">
    <div class="pp-bar-in">
        <div class="sum">
            <span id="ppBarCount">0 items</span> · Total <b id="ppBarTotal">$0.00</b>
            <span class="items" id="ppBarItems"></span>
        </div>
        <button type="button" class="clr" id="ppBarClear">Clear</button>
        <a class="co" href="<?= base_url('checkout') ?>">Checkout →</a>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/pp_post.js"></script>
<script>
(function () {
    var base = '<?= base_url() ?>';
    var bar = document.getElementById('ppBar');
    var elCount = document.getElementById('ppBarCount');
    var elTotal = document.getElementById('ppBarTotal');
    var elItems = document.getElementById('ppBarItems');

    function money(n) { return '$' + Number(n || 0).toFixed(2); }

    function paint(cart) {
        var items = (cart && cart.items) || [];
        var ids = items.map(function (i) { return String(i.plan_id); });

        document.querySelectorAll('.pp-add').forEach(function (b) {
            var on = ids.indexOf(b.getAttribute('data-plan')) !== -1;
            b.classList.toggle('on', on);
            b.querySelector('.t').textContent = on ? '✓ Added to bundle' : '+ Add to bundle';
            var card = document.querySelector('.pp-card[data-card="' + b.getAttribute('data-plan') + '"]');
            if (card) card.classList.toggle('is-selected', on);
        });

        if (items.length) {
            elCount.textContent = items.length + (items.length === 1 ? ' item' : ' items');
            elTotal.textContent = money(cart.total);
            elItems.textContent = items.map(function (i) {
                return i.term_label ? i.name + ' (' + i.term_label + ')' : i.name;
            }).join(' · ');
            bar.classList.add('show');
            document.body.style.paddingBottom = bar.offsetHeight + 'px';
        } else {
            bar.classList.remove('show');
            document.body.style.paddingBottom = '';
        }
    }

    function flash(msg) {
        var e = document.getElementById('ppErr');
        e.textContent = msg;
        e.style.display = 'block';
        clearTimeout(flash._t);
        flash._t = setTimeout(function () { e.style.display = 'none'; }, 6000);
    }

    function send(path, planId, btn) {
        if (btn) btn.disabled = true;
        ppPost(
            base + path,
            planId != null ? { plan_id: planId } : {},
            function (res) {
                if (btn) btn.disabled = false;
                if (res && res.cart) paint(res.cart);
            },
            function (msg) {
                if (btn) btn.disabled = false;
                flash(msg);
            }
        );
    }

    document.querySelectorAll('.pp-add').forEach(function (b) {
        b.addEventListener('click', function () {
            send(b.classList.contains('on') ? 'cart-remove' : 'cart-add', b.getAttribute('data-plan'), b);
        });
    });
    document.getElementById('ppBarClear').addEventListener('click', function () { send('cart-clear', null, null); });

    // Paint the persisted cart on load.
    paint(<?= json_encode(isset($cart) ? $cart : array('items' => array(), 'total' => 0)) ?>);
})();
</script>
