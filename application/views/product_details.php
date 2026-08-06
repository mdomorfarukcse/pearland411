<style>
	.biz-pro {

		margin-top: -55px;

	}

	.pro-rel-pro-box .pro-eve-box {
		border-radius: 10px;
	}

	.pro-rel-pro-box .pro-eve-box img {
		object-fit: cover;
		border-radius: 5px;

	}
	/* phone */

	@media only screen and (max-width: 600px) {
		.biz-pro {

			margin-top: -0px !important;

		}
	}
</style>

<!-- END -->

<div class="all-list-bre all-pro-bre" style="margin-top: 0px !important;">

	<div class="container sec-all-list-bre">

		<div class="row">

			<h2><?= e($product->product_name); ?></h2>

			<ul>

				<li><a href="index.html">Home</a>

				</li>

				<li><a href="all-products.html">All Products</a>

				</li>

				<li><a href="#"><?= e($product->cat_name); ?></a>

				</li>

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

							if (!empty($images) && is_array($images)): ?>
								<div class="carousel-item active">
									<img src="<?= base_url($images[0]); ?>" alt="<?= e($product->product_name); ?>">
								</div>

								<?php for ($j = 1; $j < count($images); $j++): ?>
									<div class="carousel-item">
										<img src="<?= base_url($images[$j]); ?>" alt="<?= e($product->product_name); ?>">
									</div>
								<?php endfor; ?>
							<?php else: ?>
								<div class="carousel-item active">
									<img src="<?= base_url('assets/images/products/default.jpg'); ?>" alt="<?= e($product->product_name); ?>">
								</div>
							<?php endif; ?>
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

					<a target="_blank" rel="noopener noreferrer" href="<?= e_url($product->product_payment_link); ?>" class="btn btn2">Buy now</a>

				</div>

			</div>

			<div class="col-md-7 rhs">

				<div class="pro-pri-box">

					<div class="pro-pbox-2"> <span class="veri">Verified</span>

						<h1><?= e($product->product_name); ?></h1>

						<!-- <span class="rat">4.0</span> -->

						<span class="pro-cost">$<?= number_format((float) $product->product_price, 2) ?> <b class="pro-off"><?= (int) $product->product_price_offer ?>% off</b></span>

					</div>

					<div class="pro-pbox-3 pro-pbox-com">

						<h4>Highlights</h4>

						<ul>
							<?php
							$highlights = json_decode($product->product_highlights);
							if (!empty($highlights) && is_array($highlights)):
								foreach ($highlights as $highlight): ?>
									<li><?= htmlspecialchars($highlight); ?></li>
								<?php endforeach;
							else: ?>
								<li>No highlights available</li>
							<?php endif; ?>
						</ul>

					</div>

					<div class="pro-pbox-4 pro-pbox-com">

						<?= strip_tags(html_entity_decode($product->product_description)); ?>

					</div>

					<div class="pro-pbox-5 pro-pbox-com">

						<h4>Specifications</h4>

						<ul>
							<?php
							$questions = json_decode($product->product_info_question);
							$answers = json_decode($product->product_info_answer);

							if (!empty($questions) && !empty($answers) && is_array($questions) && is_array($answers) && count($questions) == count($answers)):
								for ($i = 0; $i < count($questions); $i++): ?>
									<li>
										<span class='pro-spe-li'><?= htmlspecialchars($questions[$i]); ?></span> : <span class='pro-spe-po'>&nbsp;&nbsp;<?= htmlspecialchars($answers[$i]); ?></span>
									</li>
								<?php endfor;
							else: ?>
								<li>No specifications available</li>
							<?php endif; ?>

						</ul>

					</div>

					<div class="pro-pbox-7 pro-pbox-com">

						<h4>Tags</h4>

						<?php
						if (!empty($product->product_tags)):
							$tags = explode(',', $product->product_tags);
							foreach ($tags as $tag):
								$tag = trim($tag);
								if (!empty($tag)): ?>
									<a href="#"><?= htmlspecialchars($tag); ?></a>
							<?php endif;
							endforeach;
						else: ?>
							<span>No tags available</span>
						<?php endif; ?>

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
			<?php
			// Build current product URL
			$product_url = base_url('products-details/' . $product->product_slug);
			$product_name = $product->product_name;
			$encoded_url = urlencode($product_url);
			$encoded_name = urlencode($product_name);
			?>
			<ul>
				<li>
					<div class="sh-pro-shar sh-pro-face">
						<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= $encoded_url ?>&quote=<?= $encoded_name ?>">
							<img src="<?= base_url() ?>assets/images/social/3.png" alt="">Facebook</a>
					</div>
				</li>
				<li>
					<div class="sh-pro-shar sh-pro-twi">
						<a target="_blank" href="https://twitter.com/intent/tweet?text=<?= $encoded_name ?>&url=<?= $encoded_url ?>">
							<img src="<?= base_url() ?>assets/images/social/2.png" alt="">Twitter</a>
					</div>
				</li>
				<li>
					<div class="sh-pro-shar sh-pro-what">
						<a target="_blank" href="https://wa.me/?text=<?= $encoded_name ?>%20<?= $encoded_url ?>">
							<img src="<?= base_url() ?>assets/images/social/6.png" alt="">WhatsApp</a>
					</div>
				</li>
				<li>
					<div class="sh-pro-shar sh-pro-link">
						<a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encoded_url ?>">
							<img src="<?= base_url() ?>assets/images/social/1.png" alt="">Linkedin</a>
					</div>
				</li>
				<li>
					<div style="background-color: #da271a" class="sh-pro-shar sh-pro-pin">
						<a target="_blank" href="https://pinterest.com/pin/create/button/?url=<?= $encoded_url ?>&media=<?= isset($images[0]) ? base_url($images[0]) : '' ?>&description=<?= $encoded_name ?>">
							<img src="<?= base_url() ?>assets/images/social/9.png" alt="">Pinterest</a>
					</div>
				</li>
			</ul>
		</div>

		<div class="pro-rel-pro-box">

			<h4>Related Products</h4>

			<div class="us-ppg-com us-ppg-blog">

				<ul>
					<?php if (!empty($related_products)): ?>
						<?php foreach ($related_products as $related): ?>
							<?php
							// Get first image from gallery
							$related_images = json_decode($related->gallery_image);
							$first_image = !empty($related_images) ? $related_images[0] : 'assets/images/products/default.jpg';
							?>
							<li>

								<div class="pro-eve-box">

									<div>

										<img src="<?= base_url($first_image); ?>" alt="<?= $related->product_name; ?>">

									</div>

									<div> <span>$<?= $related->product_price; ?></span>

										<h2><?= $related->product_name; ?></h2>

									</div> <a href="<?= base_url('user/product_details/' . $related->product_slug); ?>" class="fclick">&nbsp;</a>

								</div>

							</li>
						<?php endforeach; ?>
					<?php else: ?>
						<li>
							<div class="pro-eve-box">
								<div>
									<img src="<?= base_url() ?>assets/images/products/default.jpg" alt="No related products">
								</div>
								<div>
									<h2>No related products found</h2>
								</div>
							</div>
						</li>
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

						<?php if ($this->session->flashdata('message')): ?>
							<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
								<?= e($this->session->flashdata('message')); ?>
							</div>
						<?php endif; ?>

						<?php if ($this->session->flashdata('error_msg')): ?>
							<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
								<?= e($this->session->flashdata('error_msg')); ?>
							</div>
						<?php endif; ?>

						<form method="post" action="<?= base_url('user/add-enquiry/product_details/' . $product->product_id); ?>" data-validate>

							<!-- Hidden fields for enquiry data -->
							<input type="hidden" name="listing_id" value="">
							<input type="hidden" name="event_id" value="">
							<input type="hidden" name="blog_id" value="">
							<input type="hidden" name="product_id" value="<?= $product->product_id; ?>">
							<input type="hidden" name="receiving_user_id" value="<?= $product->user_id; ?>">
							<input type="hidden" name="enquiry_sender_id" value="<?= isset($logged_id) ? $logged_id : ''; ?>">
							<input type="hidden" name="enquiry_source" value="<?= e($product->product_name); ?>">
							<input type="hidden" name="enquiry_type" value="product">

							<div class="form-group">

								<input type="text" class="form-control" name="enquiry_name" placeholder="Enter name*" required>

							</div>

							<div class="form-group">

								<input type="email" class="form-control" name="enquiry_email" placeholder="Enter email*" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address" required>

							</div>

							<div class="form-group">

								<input type="tel" inputmode="tel" class="form-control" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number (10-15 digits)" required>

							</div>

							<div class="form-group">

								<textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>

							</div>

							<button type="submit" class="btn btn-primary">Submit</button>

						</form>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>