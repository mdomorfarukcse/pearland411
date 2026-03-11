
	<!-- START -->
	<!--PRICING DETAILS-->
	<section class=" login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main add-list">
					<div class="log-bor">&nbsp;</div> <span class="steps">Update Product</span>
			        <?php if($this->session->flashdata('message')){ ?>
			            <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
			        <?php } ?>
					<div class="log">
						<div class="login add-list-off add-pro-fie">
							<h4>Update Product</h4>
							<form action="<?= base_url('user/edit_products/'.$product->product_id);?>" class="product_form" id="product_form" name="product_form" method="post" enctype="multipart/form-data">
								<ul>
									<li>
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="text" name="product_name" id="product_name" required="required" value="<?= $product->product_name;?>" class="form-control" placeholder="Product Name*">
												</div>
												<span class="text-danger"><?= form_error("product_name")?form_error("product_name"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<select onChange="getProductSubCategory(this.value);" name="category_id" id="category_id" class="chosen-select form-control">
														<option value="">Select Category</option>
														<?php
															if(count($productCategories) > 0){
																foreach ($productCategories as $value) {
														?>
														<option value="<?= $value->category_id;?>" <?= $product->category_id == $value->category_id? 'selected' : '';?>><?= $value->category_name;?></option>
														<?php	
																}
															}
														?>
													</select>
												</div>
												<span class="text-danger"><?= form_error("category_id")?form_error("category_id"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
					                    <div class="row">
					                      <div class="col-md-12">
					                        <div class="form-group">
					                          <select  data-placeholder="Select Sub Category" name="sub_category_id" id="sub_category_id"  class="chosen-select form-control">
					                            <?php if($sub_category_products){ ?>
					                              <option value="">Choose a Sub Category</option>
					                              <?php
					                              foreach($sub_category_products as $subcat){ ?>
					                                <option value="<?= $subcat->sub_category_id  ?>" <?=  ($subcat->sub_category_id == $product->sub_category_id) ? 'SELECTED' : ''?>><?= $subcat->sub_category_name?></option>
					                                <?php
					                              }
					                            } 
					                            ?>
					                          </select>
					                        </div>
					                      </div>
					                    </div>										
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="product_price" id="product_price" onkeypress="return isNumber(event)" required="required" class="form-control" value="<?= $product->product_price;?>" placeholder="Price*">
												</div>
												<span class="text-danger"><?= form_error("product_price")?form_error("product_price"):'' ;?></span>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<input type="text" name="product_price_offer" id="product_price_offer" onkeypress="return isNumber(event)" class="form-control" value="<?= $product->product_price_offer;?>" placeholder="Offer (i.e) 50">
												</div>
												<span class="text-danger"><?= form_error("product_price_offer")?form_error("product_price_offer"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<input type="text" class="form-control" name="product_payment_link" id="product_payment_link" placeholder="Product Payment External Link" value="<?= $product->product_payment_link;?>" required>
													<!-- INPUT TOOL TIP -->
													<div class="inp-ttip"> <b>Paymeny link</b>
														Customers click on "Buy now" button means.. page automaticall redirect to you provided link.</div>
													<!-- END INPUT TOOL TIP -->
												</div>
												<span class="text-danger"><?= form_error("product_payment_link")?form_error("product_payment_link"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea class="form-control" required="required" name="product_description" id="product_description" placeholder="Product Details" > <?= $product->product_description;?></textarea>
												</div>
												<span class="text-danger"><?= form_error("product_description")?form_error("product_description"):'' ;?></span>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Product images(max 5 images)</label>
													<input type="file" name="gallery_image[]" class="form-control" accept="image/png, image/jpeg" multiple>													
												</div>
											</div>
										</div>

										<?php
											// echo "<pre>"; echo $product->gallery_image;die();
										?>
										<!--FILED END-->

										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="log">
													<div class="login add-prod-high-oth">
														<h4>Highlights</h4>
														<span class="add-list-add-btn prod-add-high-oad" title="add new offer">+</span>
														<span class="add-list-rem-btn prod-add-high-ore" title="remove offer">-</span>
														<ul>
														<?php 
														$Highlights = json_decode($product->product_highlights);
														if ($Highlights != NULL) {
															$i = 1;
															foreach ($Highlights as $hl) {
														?>	
															<li>
																<!--FILED START-->
																<div class="row">
																	<div class="col-md-12">
																		<div class="form-group">
																			<input type="text" name="product_highlights[]" class="form-control" placeholder="(i.e) 1 Year Onsite Warranty" value="<?= $hl;?>">
																		</div>
																		<span class="text-danger"><?= form_error("product_highlights")?form_error("product_highlights"):'' ;?></span>
																	</div>
																</div>
																<!--FILED END-->
															</li>
														<?php 
															}
														} else 
														{
														?>	
															<li>
																<!--FILED START-->
																<div class="row">
																	<div class="col-md-12">
																		<div class="form-group">
																			<input type="text" name="product_highlights[]" class="form-control" placeholder="(i.e) 1 Year Onsite Warranty">
																		</div>
																		<span class="text-danger"><?= form_error("product_highlights")?form_error("product_highlights"):'' ;?></span>
																	</div>
																</div>
																<!--FILED END-->
															</li>
														<?php } ?>
														</ul>
													</div>
												</div>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="log">
													<div class="login add-prod-oth">
														<h4>Specifications</h4>
														<span class="add-list-add-btn prod-add-oad" title="add new offer">+</span>
														<span class="add-list-rem-btn prod-add-ore" title="remove offer">-</span>
														<ul>
															<li>
																<div class="row">
																<?php 
																	$piq 	= json_decode($product->product_info_question);
																	$pia 	= json_decode($product->product_info_answer);
																	$count 	= count($piq);

																	if ($count > 0) {
																		for($i = 0; $i < $count; $i++)
																		{
																?>
																		<div class="col-md-5">
																			<div class="form-group">
																				<input type="text" name="product_info_question[]" class="form-control" placeholder="(i.e) Serial Number" value="<?= $piq[$i];?>">
																			</div>
																			<span class="text-danger"><?= form_error("product_info_question")?form_error("product_info_question"):'' ;?></span>
																		</div>

																		<div class="col-md-2">
																			<div class="form-group"> <i class="material-icons">arrow_forward</i>
																			</div>
																		</div>	
																		
																		<div class="col-md-5">
																			<div class="form-group">
																				<input type="text" name="product_info_answer[]" class="form-control" placeholder="(i.e) qwerty3421" value="<?= $pia[$i];?>">
																			</div>
																			<span class="text-danger"><?= form_error("product_info_answer")?form_error("product_info_answer"):'' ;?></span>
																		</div>																																					
																<?php			
																		}
																	} else {
																?>	

																	<div class="col-md-5">
																		<div class="form-group">
																			<input type="text" name="product_info_question[]" class="form-control" placeholder="(i.e) Serial Number" />
																		</div>
																		<span class="text-danger"><?= form_error("product_info_question")?form_error("product_info_question"):'' ;?></span>
																	</div>

																	<div class="col-md-2">
																		<div class="form-group"> <i class="material-icons">arrow_forward</i>
																		</div>
																	</div>	
																	
																	<div class="col-md-5">
																		<div class="form-group">
																			<input type="text" name="product_info_answer[]" class="form-control" placeholder="(i.e) qwerty3421" />
																		</div>
																		<span class="text-danger"><?= form_error("product_info_answer")?form_error("product_info_answer"):'' ;?></span>
																	</div>	

																<?php
																	}
																?>
																</div>															
															</li>
														</ul>
													</div>
												</div>
											</div>
										</div>
										<!--FILED END-->
										<!--FILED START-->
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<textarea class="form-control" name="product_tags" id="product_tags" placeholder="Product Tags (i.e) electronics,laptop,hp,canon" ><?= $product->product_tags; ?></textarea>
												</div>
												<span class="text-danger"><?= form_error("product_tags")?form_error("product_tags"):'' ;?></span>	
											</div>
										</div>
										<!--FILED END-->
									</li>
								</ul>
								<!--FILED START-->
								<div class="row">
									<div class="col-md-12">
										<button type="submit" name="product_submit" class="btn btn-primary">Submit</button>
									</div>
									<div class="col-md-12"> <a href="<?= base_url('user/dashboard');?>" class="skip">Go to user dashboard >></a>
									</div>
								</div>
								<!--FILED END-->
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--END PRICING DETAILS-->

	<script src="<?= base_url('assets/ckeditor/ckeditor.js');?>"></script>
	<script>
		CKEDITOR.replace('product_description');
	</script>
	<script>
	  function getProductSubCategory(val) {
	    $.ajax({
	      type: "POST",
	      url: '<?=base_url()?>/admin/get_product_sub_category',
	      data: 'category_id=' + val,
	      success: function (data) {
	      	console.log(data);
	        $("#sub_category_id").html(data);
	        $('#sub_category_id').trigger("chosen:updated");
	      }
	    });
	  }
	</script>