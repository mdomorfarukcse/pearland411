<?php
/**
 * Centralised SEO <head> partial. Include this near the top of <head>
 * (after the <meta charset> but before stylesheets), in both the public
 * header.php and the admin header.php where applicable.
 *
 *   <?php include APPPATH.'views/partials/seo_head.php'; ?>
 *
 * The including view (or the controller that loaded it) can set these
 * before the include to customise the tags. All are optional — the
 * partial falls back to a sensible site-wide default.
 *
 *   $title              <title> tag (raw — gets prefixed/suffixed below)
 *   $meta_description   <meta name="description">
 *   $meta_keywords      <meta name="keywords">  (mostly ignored by Google,
 *                                                but kept for Bing/Yandex)
 *   $canonical_url      <link rel="canonical">
 *   $og_image           Absolute URL for the social-share preview image
 *   $og_type            'website' (default), 'article', 'event', 'business.business'
 *   $robots             'index,follow' (default), 'noindex,nofollow', etc.
 *   $structured_data    Pre-rendered JSON-LD string (or array, json_encoded below)
 *
 * Site-wide defaults for GA, Search Console + Bing verification tokens
 * come from .env:
 *
 *   GOOGLE_ANALYTICS_ID=G-XXXXXXX
 *   GOOGLE_SITE_VERIFICATION=xxxxxxxxxxxx
 *   BING_SITE_VERIFICATION=xxxxxxxxxxxx
 *   FACEBOOK_APP_ID=
 *
 * If a key isn't set we just don't emit the tag. No errors, no surprises.
 */

// --- Site constants -----------------------------------------------------

$__seo_site_name    = 'Pearland411';
$__seo_site_tagline = 'Your Community. Your Connection.';

// Site-wide defaults — DB-stored settings beat hardcoded fallbacks.
$__seo_default_title = function_exists('site_setting') ? site_setting('site_title', '') : '';
$__seo_default_desc  = function_exists('site_setting') ? site_setting('site_description', 'Pearland411 is your local guide to Pearland, Texas — businesses, events, deals, news and jobs all in one place.') : 'Pearland411 is your local guide to Pearland, Texas — businesses, events, deals, news and jobs all in one place.';
$__seo_default_img   = function_exists('site_setting') ? site_setting('site_og_image',   '') : '';
if ($__seo_default_img === '') $__seo_default_img = base_url('assets/images/pearland-logo.png');

// --- Resolve title ------------------------------------------------------

$__seo_title_raw = isset($title) && $title !== ''
	? trim($title)
	: ($__seo_default_title !== '' ? $__seo_default_title : $__seo_site_name);
// Avoid double-branding "Pearland411 | Pearland411 — ..." titles.
if (stripos($__seo_title_raw, $__seo_site_name) === false) {
	$__seo_title = $__seo_title_raw . ' | ' . $__seo_site_name;
} else {
	$__seo_title = $__seo_title_raw;
}

$__seo_desc = isset($meta_description) && $meta_description !== ''
	? trim(strip_tags((string) $meta_description))
	: $__seo_default_desc;
// Truncate to ~160 chars (Google's snippet length).
if (mb_strlen($__seo_desc) > 160) {
	$__seo_desc = rtrim(mb_substr($__seo_desc, 0, 157)) . '…';
}

$__seo_keywords = isset($meta_keywords) ? (string) $meta_keywords : '';
$__seo_canonical = isset($canonical_url) && $canonical_url !== ''
	? $canonical_url
	: current_url();

$__seo_og_image = isset($og_image) && $og_image !== '' ? $og_image : $__seo_default_img;
$__seo_og_type  = isset($og_type)  && $og_type  !== '' ? $og_type  : 'website';
$__seo_robots   = isset($robots)   && $robots   !== '' ? $robots   : 'index,follow';

// --- Env-driven tokens (override with DB-stored settings if present) ---

$__seo_ga_id      = function_exists('site_setting') ? site_setting('google_analytics_id',      getenv('GOOGLE_ANALYTICS_ID')      ?: '') : (getenv('GOOGLE_ANALYTICS_ID')      ?: '');
$__seo_gsc_token  = function_exists('site_setting') ? site_setting('google_site_verification', getenv('GOOGLE_SITE_VERIFICATION') ?: '') : (getenv('GOOGLE_SITE_VERIFICATION') ?: '');
$__seo_bing_token = function_exists('site_setting') ? site_setting('bing_site_verification',   getenv('BING_SITE_VERIFICATION')   ?: '') : (getenv('BING_SITE_VERIFICATION')   ?: '');
$__seo_fb_app_id  = function_exists('site_setting') ? site_setting('facebook_app_id',          getenv('FACEBOOK_APP_ID')          ?: '') : (getenv('FACEBOOK_APP_ID')          ?: '');
?>

<title><?= htmlspecialchars($__seo_title, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($__seo_desc, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($__seo_keywords !== ''): ?>
<meta name="keywords" content="<?= htmlspecialchars($__seo_keywords, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<meta name="robots" content="<?= htmlspecialchars($__seo_robots, ENT_QUOTES, 'UTF-8') ?>">
<meta name="theme-color" content="#909D3E">
<link rel="canonical" href="<?= htmlspecialchars($__seo_canonical, ENT_QUOTES, 'UTF-8') ?>">

<!-- Open Graph -->
<meta property="og:site_name"   content="<?= htmlspecialchars($__seo_site_name, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type"        content="<?= htmlspecialchars($__seo_og_type, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:title"       content="<?= htmlspecialchars($__seo_title_raw, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($__seo_desc, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url"         content="<?= htmlspecialchars($__seo_canonical, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image"       content="<?= htmlspecialchars($__seo_og_image, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:locale"      content="en_US">
<?php if ($__seo_fb_app_id !== ''): ?>
<meta property="fb:app_id"      content="<?= htmlspecialchars($__seo_fb_app_id, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Twitter / X cards -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= htmlspecialchars($__seo_title_raw, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($__seo_desc, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:image"       content="<?= htmlspecialchars($__seo_og_image, ENT_QUOTES, 'UTF-8') ?>">

<?php if ($__seo_gsc_token !== ''): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars($__seo_gsc_token, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<?php if ($__seo_bing_token !== ''): ?>
<meta name="msvalidate.01" content="<?= htmlspecialchars($__seo_bing_token, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<?php if (!empty($structured_data)):
	$__seo_jsonld = is_string($structured_data)
		? $structured_data
		: json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<script type="application/ld+json"><?= $__seo_jsonld ?></script>
<?php endif; ?>
<?php if (!empty($json_ld)): /* backward-compat: some controllers pass pre-built <script> tags */ ?>
<?= $json_ld ?>
<?php endif; ?>

<?php if ($__seo_ga_id !== ''): ?>
<!-- Google Analytics (GA4) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($__seo_ga_id, ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '<?= htmlspecialchars($__seo_ga_id, ENT_QUOTES, 'UTF-8') ?>', { anonymize_ip: true });
</script>
<?php endif; ?>

<?php
// Don't leak our temp vars into the caller's scope.
unset(
	$__seo_site_name, $__seo_site_tagline,
	$__seo_default_desc, $__seo_default_img,
	$__seo_title_raw, $__seo_title,
	$__seo_desc, $__seo_keywords, $__seo_canonical,
	$__seo_og_image, $__seo_og_type, $__seo_robots,
	$__seo_ga_id, $__seo_gsc_token, $__seo_bing_token, $__seo_fb_app_id,
	$__seo_jsonld
);
?>
