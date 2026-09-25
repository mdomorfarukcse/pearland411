<?php
/**
 * /user/edit-coupon/<id> — edit a deal.
 *
 * From User::edit_coupon(): $coupon (the member's own; the controller 404s
 * on anybody else's), plus $coupon_errors / $coupon_old on a rejected submit.
 */
$is_off = isset($coupon) && (int) ($coupon->coupon_status ?? 1) !== 1;
if (isset($coupon_old) && is_array($coupon_old) && array_key_exists('coupon_status', $coupon_old)) {
	$is_off = (string) $coupon_old['coupon_status'] === '0';
}
?>
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">Edit deal</span>
	<div class="ud-cen-s2">
		<h2>Edit deal</h2>

		<form name="edit_coupon_form" id="edit_coupon_form" method="post" enctype="multipart/form-data" data-validate
			  action="<?= e_url(base_url('user/edit-coupon/' . (int) ($coupon->coupon_id ?? 0))) ?>">

			<?php include APPPATH . 'views/partials/coupon_form_fields.php'; ?>

			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label for="coupon_status">Show it?</label>
						<select id="coupon_status" name="coupon_status" class="form-control">
							<option value="1" <?= $is_off ? '' : 'selected' ?>>Yes &mdash; run it between the dates above</option>
							<option value="0" <?= $is_off ? 'selected' : '' ?>>No &mdash; take it off the public pages</option>
						</select>
					</div>
				</div>
			</div>

			<button type="submit" name="coupon_submit" class="btn btn-primary">Save changes</button>
			<a href="<?= e_url(base_url('user/my-coupons')) ?>" class="btn btn-link">Cancel</a>
		</form>
	</div>
</div>
<!--END CENTER SECTION-->
