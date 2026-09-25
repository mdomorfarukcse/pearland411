<?php
/**
 * /user/add-coupon — post a deal.
 *
 * From User::add_coupon(): $listings (non-empty; the controller redirects
 * otherwise), plus $coupon_errors / $coupon_old on a rejected submit.
 *
 * This view has existed since the theme shipped and posted to
 * `user/add-coupon`, a method nobody had written. It is rewritten here against
 * the method that now exists.
 */
$listings = (isset($listings) && is_array($listings)) ? $listings : array();
?>
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Post a deal</span>
	<div class="ud-cen-s2">
		<h2>Post a deal</h2>

		<p style="margin:10px 0 18px;color:#5A6072;font-size:14px;">
			It goes live straight away on
			<a href="<?= e_url(base_url('coupons')) ?>">the deals page</a>, on your listing and on the
			homepage, and comes off by itself on the end date.
			<?php if (count($listings) === 1): ?>
				Posting as <strong><?= e($listings[0]->listing_name ?? 'your business') ?></strong>.
			<?php endif; ?>
		</p>

		<form name="coupon_form" id="coupon_form" method="post" enctype="multipart/form-data" data-validate
			  action="<?= e_url(base_url('user/add-coupon')) ?>">

			<?php include APPPATH . 'views/partials/coupon_form_fields.php'; ?>

			<button type="submit" name="coupon_submit" class="btn btn-primary">Post the deal</button>
			<a href="<?= e_url(base_url('user/my-coupons')) ?>" class="btn btn-link">Cancel</a>
		</form>
	</div>
</div>
<!--END CENTER SECTION-->
