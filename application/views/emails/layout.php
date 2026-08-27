<?php
/**
 * The standard Pearland411 email shell.
 *
 * Every email the site sends should come through here, so a customer receipt
 * and an internal billing alert look like they came from the same company.
 * Rendered by Mailer::render(); do not load it directly.
 *
 * Variables: $heading, $body, $preheader, $intro, $footnote.
 *
 * Written as 2003-era HTML on purpose. Outlook renders with Word's engine,
 * Gmail strips <style> blocks on some clients, and neither supports flexbox or
 * grid — so this is nested tables with inline styles, one column, no external
 * images. It is deliberately not the site's CSS.
 */
$heading   = isset($heading) ? $heading : 'Pearland411';
$body      = isset($body) ? $body : '';
$preheader = isset($preheader) ? $preheader : '';
$intro     = isset($intro) ? $intro : '';
$footnote  = isset($footnote) ? $footnote : '';

$navy  = '#171A45';
$green = '#79B82A';
$ink   = '#1F2430';
$ink2  = '#5A6472';
$line  = '#E4E6EC';
$bg    = '#F4F5F8';

$site_phone = site_setting('contact_phone', '');
$site_email = site_setting('contact_email', '');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($heading) ?></title>
</head>
<body style="margin:0; padding:0; background:<?= $bg ?>; -webkit-font-smoothing:antialiased;">

<?php // The grey line most inboxes show after the subject. Hidden in the body. ?>
<?php if ($preheader !== ''): ?>
<div style="display:none; font-size:1px; color:<?= $bg ?>; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
	<?= e($preheader) ?>
</div>
<?php endif; ?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:<?= $bg ?>;">
	<tr>
		<td align="center" style="padding:24px 12px;">

			<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
			       style="width:100%; max-width:600px; background:#ffffff; border:1px solid <?= $line ?>; border-radius:8px; overflow:hidden;">

				<?php // Wordmark as text, not an image: images are blocked by default
				      // in most clients, and a broken logo is worse than no logo. ?>
				<tr>
					<td style="background:<?= $navy ?>; padding:20px 28px;">
						<a href="<?= base_url() ?>" style="text-decoration:none; color:#ffffff; font-family:'Segoe UI',Helvetica,Arial,sans-serif; font-size:20px; font-weight:700; letter-spacing:-0.2px;">
							Pearland<span style="color:<?= $green ?>;">411</span>
						</a>
					</td>
				</tr>
				<tr><td style="height:3px; background:<?= $green ?>; font-size:0; line-height:0;">&nbsp;</td></tr>

				<tr>
					<td style="padding:30px 28px 8px 28px; font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
						<h1 style="margin:0 0 6px 0; font-size:21px; line-height:1.3; font-weight:700; color:<?= $ink ?>;">
							<?= e($heading) ?>
						</h1>
						<?php if ($intro !== ''): ?>
							<p style="margin:0; font-size:15px; line-height:1.6; color:<?= $ink2 ?>;"><?= e($intro) ?></p>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<td style="padding:14px 28px 26px 28px; font-family:'Segoe UI',Helvetica,Arial,sans-serif; font-size:15px; line-height:1.65; color:<?= $ink ?>;">
						<?= $body ?>
					</td>
				</tr>

				<?php if ($footnote !== ''): ?>
				<tr>
					<td style="padding:0 28px 26px 28px; font-family:'Segoe UI',Helvetica,Arial,sans-serif; font-size:13px; line-height:1.6; color:<?= $ink2 ?>;">
						<?= $footnote ?>
					</td>
				</tr>
				<?php endif; ?>

				<tr><td style="border-top:1px solid <?= $line ?>; font-size:0; line-height:0;">&nbsp;</td></tr>
				<tr>
					<td style="padding:18px 28px 22px 28px; font-family:'Segoe UI',Helvetica,Arial,sans-serif; font-size:12.5px; line-height:1.6; color:<?= $ink2 ?>;">
						<strong style="color:<?= $ink ?>;">Pearland411</strong> &mdash; Pearland&rsquo;s community business directory<br>
						<?php if ($site_phone !== ''): ?><?= e($site_phone) ?><?php endif; ?>
						<?php if ($site_phone !== '' && $site_email !== ''): ?> &middot; <?php endif; ?>
						<?php if ($site_email !== ''): ?><a href="mailto:<?= e_attr($site_email) ?>" style="color:<?= $ink2 ?>;"><?= e($site_email) ?></a><?php endif; ?>
						<br>
						<a href="<?= base_url() ?>" style="color:<?= $ink2 ?>;"><?= e(preg_replace('#^https?://#', '', rtrim(base_url(), '/'))) ?></a>
					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>
</body>
</html>
