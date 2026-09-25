<?php
/**
 * Shared body of the post-a-deal and edit-a-deal forms.
 *
 * Expects $coupon (row or NULL), $coupon_old (posted values on a failed
 * submit) and $coupon_errors (field => message), all set up by the including
 * view. Kept in one file so the two forms cannot drift: the add form used to
 * ask for four fields and the edit form for five, against the same table.
 */

$c_err = (isset($coupon_errors) && is_array($coupon_errors)) ? $coupon_errors : array();
$c_old = (isset($coupon_old)    && is_array($coupon_old))    ? $coupon_old    : array();
$c_row = isset($coupon) ? $coupon : NULL;

/** Posted value first (they just typed it), then what is stored. */
$c_val = function ($field, $default = '') use ($c_old, $c_row) {
	if (array_key_exists($field, $c_old))       return (string) $c_old[$field];
	if ($c_row && isset($c_row->$field))        return (string) $c_row->$field;
	return (string) $default;
};
$c_bad = function ($field) use ($c_err) {
	return isset($c_err[$field]) ? $c_err[$field] : '';
};
?>

<?php if (!empty($c_err['general'])): ?>
	<div class="alert alert-danger"><p style="margin:0;"><?= e($c_err['general']) ?></p></div>
<?php endif; ?>

<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="coupon_name">What is the offer?</label>
			<input id="coupon_name" type="text" class="form-control" name="coupon_name" required maxlength="150"
				   placeholder="20% off your first visit" value="<?= e_attr($c_val('coupon_name')) ?>">
			<?php if ($c_bad('coupon_name')): ?><span class="text-danger"><?= e($c_bad('coupon_name')) ?></span><?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="coupon_code">Code</label>
			<input id="coupon_code" type="text" class="form-control" name="coupon_code" required
				   maxlength="40" pattern="[A-Za-z0-9 _\-]{2,40}"
				   title="2 to 40 characters: letters, numbers, spaces, - and _"
				   placeholder="PEARL20" value="<?= e_attr($c_val('coupon_code')) ?>">
			<small class="form-text text-muted">What people show in store or type at checkout.</small>
			<?php if ($c_bad('coupon_code')): ?><span class="text-danger"><?= e($c_bad('coupon_code')) ?></span><?php endif; ?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="coupon_link">Link <span class="text-muted">(optional)</span></label>
			<input id="coupon_link" type="url" class="form-control" name="coupon_link" maxlength="255"
				   placeholder="https://example.com/offer" value="<?= e_attr($c_val('coupon_link')) ?>">
			<small class="form-text text-muted">Where the code is used online, if it is an online offer.</small>
			<?php if ($c_bad('coupon_link')): ?><span class="text-danger"><?= e($c_bad('coupon_link')) ?></span><?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="coupon_start_date">Starts</label>
			<input id="coupon_start_date" type="date" class="form-control" name="coupon_start_date" required
				   value="<?= e_attr($c_val('coupon_start_date', date('Y-m-d'))) ?>">
			<?php if ($c_bad('coupon_start_date')): ?><span class="text-danger"><?= e($c_bad('coupon_start_date')) ?></span><?php endif; ?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="coupon_end_date">Ends</label>
			<input id="coupon_end_date" type="date" class="form-control" name="coupon_end_date" required
				   value="<?= e_attr($c_val('coupon_end_date', date('Y-m-d', strtotime('+30 days')))) ?>">
			<small class="form-text text-muted">It comes off the public page by itself on this date.</small>
			<?php if ($c_bad('coupon_end_date')): ?><span class="text-danger"><?= e($c_bad('coupon_end_date')) ?></span><?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="coupon_use_members">Who can use it? <span class="text-muted">(optional)</span></label>
			<input id="coupon_use_members" type="text" class="form-control" name="coupon_use_members" maxlength="150"
				   placeholder="New customers only" value="<?= e_attr($c_val('coupon_use_members')) ?>">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="coupon_details">Terms <span class="text-muted">(optional)</span></label>
			<textarea id="coupon_details" class="form-control" name="coupon_details" rows="4" maxlength="2000"
					  placeholder="One per customer. Not valid with any other offer."><?= e($c_val('coupon_details')) ?></textarea>
			<?php if ($c_bad('coupon_details')): ?><span class="text-danger"><?= e($c_bad('coupon_details')) ?></span><?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="coupon_photo">Picture <span class="text-muted">(optional)</span></label>
			<?php if ($c_row && !empty($c_row->coupon_photo)): ?>
				<div style="margin-bottom:8px;">
					<img src="<?= e_attr(base_url(thumb_url($c_row->coupon_photo, 'thumb'))) ?>" alt=""
						 style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
					<small class="form-text text-muted">Choosing a new file replaces this one.</small>
				</div>
			<?php endif; ?>
			<input id="coupon_photo" type="file" name="coupon_photo" class="form-control-file"
				   accept="image/jpeg,image/png,image/gif,image/webp">
			<small class="form-text text-muted">
				Your logo or a photograph of what is on offer. JPG, PNG, GIF or WebP, under 5&nbsp;MB.
			</small>
		</div>
	</div>
</div>
