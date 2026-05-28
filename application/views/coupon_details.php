<section style="background:#0e2a3a;color:#fff;padding:40px 0;">
	<div class="container">
		<div style="font-size:0.85rem;opacity:0.75;margin-bottom:6px;">
			<a href="<?= base_url('coupons') ?>" style="color:#cbd5e1;">&larr; All coupons</a>
		</div>
		<h1 style="margin:0;font-size:1.8rem;"><?= e($coupon->coupon_name) ?></h1>
		<?php if (!empty($coupon->listing_name)): ?>
			<p style="margin:6px 0 0 0;opacity:0.85;">
				From
				<a href="<?= base_url('listing-details/' . rawurlencode((string) $coupon->listing_slug)) ?>"
				   style="color:#cbd5e1;text-decoration:underline;">
					<?= e($coupon->listing_name) ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</section>

<section style="padding:40px 0;">
	<div class="container">
		<div class="row">
			<div class="col-md-7">
				<?php if (!empty($coupon->coupon_photo)): ?>
					<img src="<?= e_attr(base_url($coupon->coupon_photo)) ?>"
						 alt="<?= e_attr($coupon->coupon_name) ?>"
						 style="width:100%;border-radius:8px;margin-bottom:20px;">
				<?php endif; ?>

				<?php if (!empty($coupon->coupon_link)): ?>
					<p>
						<a href="<?= e_url($coupon->coupon_link) ?>" target="_blank" rel="noopener noreferrer"
						   class="btn btn-primary">
							Redeem this offer &rarr;
						</a>
					</p>
				<?php endif; ?>
			</div>

			<div class="col-md-5">
				<div style="border:1px solid #e5e7eb;border-radius:8px;padding:20px;background:#fff;">
					<div style="font-size:0.85rem;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
						Coupon code
					</div>
					<div style="background:#fff7d6;border:2px dashed #d4a017;color:#92400e;padding:14px;border-radius:6px;text-align:center;font-family:monospace;font-weight:bold;font-size:1.4rem;letter-spacing:2px;margin:8px 0 16px 0;">
						<?= e($coupon->coupon_code) ?>
					</div>

					<table style="width:100%;font-size:0.95rem;">
						<tr><th style="text-align:left;padding:6px 0;color:#6b7280;font-weight:500;">Valid from</th>
							<td><?= e(date('M j, Y', strtotime($coupon->coupon_start_date))) ?></td></tr>
						<tr><th style="text-align:left;padding:6px 0;color:#6b7280;font-weight:500;">Valid through</th>
							<td><?= e(date('M j, Y', strtotime($coupon->coupon_end_date))) ?></td></tr>
						<?php if (!empty($coupon->coupon_use_members)): ?>
							<tr><th style="text-align:left;padding:6px 0;color:#6b7280;font-weight:500;">Eligible</th>
								<td><?= e($coupon->coupon_use_members) ?></td></tr>
						<?php endif; ?>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
