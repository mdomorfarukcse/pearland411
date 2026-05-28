<style>
	.biz-pro {

		margin-top: -55px;

	}

	.breadcrumb_prod li {
		color: white;
	}

	.bpro-sli .carousel-item img {
		object-fit: cover !important;
	}
</style>

<!-- END -->

<div class="all-list-bre all-pro-bre" style="margin-top: 0px !important;">

	<div class="container sec-all-list-bre">

		<div class="row">

			<h2><?= $product->cat_name; ?></h2>

			<ul class="breadcrumb_prod">

				<li>Home</li>

				<li>All Products</li>

				<li><?= $product->cat_name; ?></li>

			</ul>

		</div>

	</div>

</div>

<!-- START -->

<section class="biz-pro">

	<div class="container">

		<div class="row">

			<div class="col-md-5 lhs">

				<div class="bpro-sli">

					<div id="demo" class="carousel slide" data-ride="carousel">

						<!-- The slideshow -->

						<div class="carousel-inner">

							<?php
							$images = json_decode($product->gallery_image);
							if (is_array($images) && count($images) > 0) {
								foreach ($images as $idx => $img) {
									$active = ($idx === 0) ? 'active' : '';
									echo '<div class="carousel-item ' . $active . '">';
									echo '<img src="' . base_url($img) . '" alt="">';
									echo '</div>';
								}
							} else {
								// fallback if no images
								echo '<div class="carousel-item active"><img src="' . base_url('assets/images/no-image.png') . '" alt="No image"></div>';
							}
							?>
						</div>

						<!-- Left and right controls -->

						<a class="carousel-control-prev" href="#demo" data-slide="prev"> <span class="carousel-control-prev-icon"></span>

						</a>

						<a class="carousel-control-next" href="#demo" data-slide="next"> <span class="carousel-control-next-icon"></span>

						</a>

					</div>

				</div>

				<div class="biz-pro-btn">
					<a href="#" data-toggle="modal" data-target="#quote" class="btn btn1">Get quote</a>
					<a target="_blank" href="<?= htmlspecialchars($product->product_payment_link) ?>" class="btn btn2">Buy now</a>
				</div>

			</div>

			<div class="col-md-7 rhs">

				<div class="pro-pri-box">

					<div class="pro-pbox-2"> <span class="veri">Verified</span>

						<h1><?= $product->product_name; ?></h1>

						<!-- <span class="rat">4.0</span> -->

						<span class="pro-cost">$<?= $product->product_price; ?><b class="pro-off"><?= $product->product_price_offer; ?>% off</b></span>

					</div>

					<div class="pro-pbox-3 pro-pbox-com">

						<h4>Highlights</h4>

						<ul>

							<?php

							$Highlights = json_decode($product->product_highlights);

							foreach ($Highlights as $highlight) {

								echo "<li>" . $highlight . "</li>";
							}

							?>
						</ul>

					</div>

					<div class="pro-pbox-4 pro-pbox-com">

						<?= strip_tags(html_entity_decode(trim((string) $product->product_description))) ?>

					</div>

					<div class="pro-pbox-5 pro-pbox-com">

						<h4>Specifications</h4>

						<ul>

							<?php

							$piq = json_decode($product->product_info_question);

							$pia = json_decode($product->product_info_answer);

							for ($i = 0; $i < sizeof($piq); $i++) {

								echo "<li> 

										<span class='pro-spe-li'>" . $piq[$i] . "</span> : <span class='pro-spe-po'>&nbsp;&nbsp;" . $pia[$i] . "</span>

									</li>";
							}

							?>

						</ul>

					</div>

					<div class="pro-pbox-7 pro-pbox-com">

						<h4>Tags</h4>

						<?php

						$single_tag = explode(',', $product->product_tags);

						foreach ($single_tag as $tag) {

							echo "<a href='#'>" . $tag . "</a>";
						}

						?>
					</div>

					<div class="pro-pbox-6 pro-pbox-com">

						<h4>Created by</h4>

						<div class="ud-lhs-s1">

							<img src="<?= base_url() ?>assets/images/user/3.jpg" alt="">

							<h4><?= $product->name; ?></h4>

							<b>Join on <?= date('M Y', strtotime($product->user_created)); ?></b>

							<a href="#" target="_blank" class="fclick">&nbsp;</a>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>

<!--END-->

<!-- START -->

<section class="eve-deta-body blog-deta-body">

	<div class="container">

		<div class="pro-bot-shar">

			<h4>Share this post</h4>

			<ul>

				<li>

					<div class="sh-pro-shar sh-pro-face">

						<a target="_blank" href="https://www.facebook.com/sharer/sharer.html?u=product/weight-loss-digital-products?src=facebook&quote=Weight loss digital products">

							<img src="<?= base_url() ?>assets/images/social/3.png" alt="">Facebook</a>

					</div>

				</li>

				<li>

					<div class="sh-pro-shar sh-pro-twi">

						<a target="_blank" href="http://twitter.com/share?text=Weight loss digital products&url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fproduct%2Fweight-loss-digital-products%3Fsrc%3Dtwitter">

							<img src="<?= base_url() ?>assets/images/social/2.png" alt="">Twitter</a>

					</div>

				</li>

				<li>

					<div class="sh-pro-shar sh-pro-what">

						<a target="_blank" href="whatsapp://send?text=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fproduct%2Fweight-loss-digital-products%3Fsrc%3Dwhatsapp">

							<img src="<?= base_url() ?>assets/images/social/6.png" alt="">WhatsApp</a>

					</div>

				</li>

				<li>

					<div class="sh-pro-shar sh-pro-link">

						<a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fproduct%2Fweight-loss-digital-products%26%26src%3Dlinkedin">

							<img src="<?= base_url() ?>assets/images/social/1.png" alt="">Linkedin</a>

					</div>

				</li>

				<li>

					<div style="background-color: #da271a" class="sh-pro-shar sh-pro-pin">

						<a target="_blank" href="https://www.pinterest.com/pin/create/bookmarklet/?media=images/products/33804awesomebooks-new3.jpg&url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fproduct%2Fweight-loss-digital-products%26%26src%3Dpinterest&description=Weight loss digital products">

							<img src="<?= base_url() ?>assets/images/social/9.png" alt="">Pinterest</a>

					</div>

				</li>

			</ul>

		</div>

		<div class="pro-rel-pro-box">
			<h4>Related Posts</h4>
			<div class="us-ppg-com us-ppg-blog">
				<ul>
					<?php if (isset($related_products) && is_array($related_products) && count($related_products) > 0): ?>
						<?php foreach ($related_products as $rel): ?>
							<li>
								<div class="pro-eve-box">
									<div>
										<?php
										$rel_images = json_decode($rel->gallery_image);
										$rel_img = isset($rel_images[0]) ? $rel_images[0] : 'assets/images/no-image.png';
										?>
										<img src="<?= base_url($rel_img) ?>" alt="">
									</div>
									<div> <span>$<?= htmlspecialchars($rel->product_price) ?></span>
										<h2><?= htmlspecialchars($rel->product_name) ?></h2>
									</div> <a href="<?= base_url('product/' . $rel->product_slug) ?>" class="fclick">&nbsp;</a>
								</div>
							</li>
						<?php endforeach; ?>
					<?php else: ?>
						<li>No related products found.</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

	</div>

</section>

<!--END-->





<section>

	<div class="pop-ups pop-quo">

		<!-- The Modal -->

		<div class="modal fade" id="quote">

			<div class="modal-dialog">

				<div class="modal-content">

					<div class="log-bor">&nbsp;</div>

					<span class="udb-inst">Send enquiry</span>

					<button type="button" class="close" data-dismiss="modal">&times;</button>

					<!-- Modal Header -->

					<div class="quote-pop">

						<h4>Get quote</h4>

						<form>

							<div class="form-group">

								<input type="text" class="form-control" placeholder="Enter name*" required>

							</div>

							<div class="form-group">

								<input type="email" class="form-control" placeholder="Enter email*" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address" required>

							</div>

							<div class="form-group">

								<input type="text" class="form-control" placeholder="Enter mobile number *" pattern="[0-9+()\- ]{7,20}" title="Enter a phone number (7–20 characters, digits/+/()/-/space allowed)" required>

							</div>

							<div class="form-group">

								<textarea class="form-control" rows="3" placeholder="Enter your query or message"></textarea>

							</div>

							<input type="hidden" id="source">

							<button type="submit" class="btn btn-primary">Submit</button>

						</form>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>