
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div> <span class="udb-inst">All Products</span>
    <?php if($this->session->flashdata('message')){ ?>
        <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
    <?php } ?>	
	<div class="ud-cen-s2">
		<h2>Product Details</h2>
		<a href="<?= base_url('user/add-product');?>" class="db-tit-btn">Add new Product</a>
		<table class="responsive-table bordered">
			<thead>
				<tr>
					<th>No</th>
					<th>Product Name</th>
					<!--                    <th>Rating</th>-->
					<th>Views</th>
					<!--									<th>Expiry on</th>-->
					<th>Status</th>
					<th>Edit</th>
					<th>Delete</th>
					<th>Preview</th>
				</tr>
			</thead>
			<tbody>
				<?php
					if (count($allProducts) > 0) {
						$i = 1;
						foreach ($allProducts as $product) {
				?>
					<tr>
						<td><?= $i; ?></td>
						<td>
							<?php
								$image = '';
								$images = json_decode($product->gallery_image);
								if($images != NULL)
								{
									$image = './'.$images[0];
								}
							?>
							<img src="<?= base_url($image) ;?>" alt="<?= $product->product_name; ?> ">
							<?= $product->product_name; ?> 
							<span><?= date('d, M, Y',strtotime($product->created_at)); ?></span>
						</td>
						<td><span class="db-list-rat">0</span></td>
						<td>
							<span class="db-list-ststus">
								<?= $product->product_status == 1 ? 'Active' : 'Inactive';?>
							</span>
						</td>
						<td>
							<a href="<?= base_url('user/edit-products/'.$product->product_id);?>" class="db-list-edit">		Edit
							</a>
						</td>
						<td><a href="<?= base_url('user/delete-product/'.$product->product_id);?>" class="db-list-edit">Delete</a>
						</td>
						<td>
							<a href="<?= base_url('user/product-details/'.$product->product_id);?>" class="db-list-edit" target="_blank">
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
	</div>
</div>
			