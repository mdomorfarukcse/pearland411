<!-- START -->

<section>

	<div class="ad-com">

		<div class="ad-dash leftpadd">

			<div class="ud-cen">

				<div class="log-bor">&nbsp;</div>

				<span class="udb-inst">All Products</span>

				<?php if ($this->session->flashdata('message')) { ?>

					<div class="log-suc">
						<p><?= $this->session->flashdata('message') ?></p>
					</div>

				<?php } ?>

				<div class="ud-cen-s2">

					<h2>Product details</h2>
					<div class="ad-int-sear">
						<input type="text" id="pg-sear" placeholder="Search this page..">
					</div>

					<a href="<?= base_url('admin/add-product'); ?>" class="db-tit-btn">Add new product</a>

					<table class="responsive-table bordered" id="pg-resu">

						<thead>

							<tr>

								<th>No</th>

								<th>Product Name</th>

								<!--                            <th>product Date</th>-->

								<!--<th>Created by</th>-->

								<th>Status</th>

								<th>Views</th>

								<th>Edit</th>

								<th>Delete</th>

								<th>Preview</th>

							</tr>

						</thead>

						<tbody>

							<?php

							if (isset($allProducts) && count($allProducts) > 0) {
								$i = $start_number;
								foreach ($allProducts as $product) {
							?>
									<tr>
										<td><?= $i; ?></td>
										<td>
											<?php

											$image = '';

											$images = json_decode($product->gallery_image);

											if ($images != NULL) {

												$image = './' . $images[0];
											}

											?>

											<img src="<?= base_url($image); ?>" alt="<?= $product->product_name; ?> ">

											<?= $product->product_name; ?>

											<span><?= date('d, M, Y', strtotime($product->created_at)); ?></span>

										</td>

										<td>
											<?php if ($product->product_status == 0) { ?>

												<button class="db-list-ststus" onclick="updateStatus(1,<?= $product->product_id; ?>)">In-Active</button>

											<?php } else { ?>

												<button class="db-list-ststus" onclick="updateStatus(0,<?= $product->product_id; ?>)">Active</button>

											<?php } ?>

											<input type="hidden" id="updateField" value="product_status">

											<input type="hidden" id="tableName" value="products">

											<input type="hidden" id="tableIdName" value="product_id">

											<input type="hidden" id="id" value="<?= $product->product_id; ?>">

										</td>

										<?php $p_views = (isset($views_counts) && isset($views_counts[$product->product_id])) ? $views_counts[$product->product_id] : 0; ?>
										<td><span class="db-list-rat"><?= $p_views; ?></span></td>
										<td>
											<a href="<?= base_url('admin/edit-products/' . $product->product_id); ?>" class="db-list-edit"> Edit

											</a>
											<input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
										</td>

										<td>
											<a href="#"
												onclick="javascript:deleteConfirm('<?php echo base_url('admin/delete_product/' . $product->product_id); ?>');"
												class="db-list-edit">Delete</a>

										</td>

										<td>

											<a href="<?= base_url('admin/product-details/' . $product->product_id); ?>" class="db-list-edit" target="_blank">
												Preview
											</a>

										</td>

									</tr>

								<?php

									$i++;
								}
							} else {

								?>

								<tr>

									<td colspan="6"> No Products Available!!</td>

								</tr>

							<?php } ?>



						</tbody>

					</table>
					<div class="float-right" style="margin-top: 20px;">

						<?= $link; ?>

					</div>
				</div>

			</div>

		</div>

	</div>

</section>

<!-- END -->
<script src="http://harvesthq.github.io/chosen/chosen.jquery.js"></script>