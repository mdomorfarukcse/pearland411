
<!-- START -->
<!--PRICING DETAILS-->
<section class=" login-reg">
	<div class="container">
		<div class="row">
			<div class="login-main add-list">
				<div class="log-bor">&nbsp;</div> <span class="steps">Edit Product</span>
				<div class="log">
					<div class="login add-list-off">
						<h4>Edit this product</h4>
						<form action="product_update.html" class="product_form" id="product_form" name="product_form" method="post" enctype="multipart/form-data">
							<input type="hidden" id="product_id" value="34" name="product_id" class="validate">
							<input type="hidden" id="product_code" value="PROD034" name="product_code" class="validate">
							<input type="hidden" id="gallery_image_old" value="20234fp-263d-royal-protton-alpha-steel_interior.png" name="gallery_image_old" class="validate">
							<ul>
								<li>
									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<input type="text" name="product_name" id="product_name" required="required" class="form-control" value="WHIRLPOOL FRIDGE" placeholder="Product Name*">
											</div>
										</div>
									</div>
									<!--FILED END-->
									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<select onChange="getProductSubCategory(this.value);" name="category_id" id="category_id" class="chosen-select form-control">
													<option value="">Select Category</option>
													<option value="23">Health</option>
													<option value="22">Mens</option>
													<option value="21">Fruits</option>
													<option value="20">Baby care</option>
													<option value="19">Toys</option>
													<option value="18">Jewellery</option>
													<option value="17">Shoes</option>
													<option value="16">Footwear</option>
													<option value="15">Clothings</option>
													<option value="8">Sports</option>
													<option value="7">Education</option>
													<option selected value="6">Electricals</option>
													<option value="5">Automobilers</option>
												</select>
											</div>
										</div>
									</div>
									<!--FILED END-->
									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<select name="sub_category_id[]" id="sub_category_id" multiple class="chosen-select form-control">
													<!--                                            <option -->
													<!-- value="">Select Sub Category</option>-->
													<option value="35">Camera lense</option>
													<option value="34">Camera light</option>
													<option value="33">Camera pouch</option>
													<option value="32">Camera holder</option>
													<option value="14">Smart Tech</option>
													<option value="13">Tablets</option>
													<option value="12">Camera</option>
													<option value="11">Speakers</option>
													<option value="10">Laptop</option>
													<option value="9">Smart TV</option>
													<option value="8">Mobiles</option>
												</select>
											</div>
										</div>
									</div>
									<!--FILED END-->
									<!--FILED START-->
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<input type="text" name="product_price" id="product_price" onkeypress="return isNumber(event)" value="24000" required="required" class="form-control" placeholder="Price*">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<input type="text" name="product_price_offer" id="product_price_offer" onkeypress="return isNumber(event)" value="33" class="form-control" placeholder="Offer (i.e) 50">
											</div>
										</div>
									</div>
									<!--FILED END-->
									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<textarea class="form-control" name="product_payment_link" id="product_payment_link" placeholder="Product Payment External Link">https://bizbookdirectorytemplate.com/viki/</textarea>
											</div>
										</div>
									</div>
									<!--FILED END-->
									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<textarea class="form-control" required="required" name="product_description" id="product_description" placeholder="Product Details"></textarea>
											</div>
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
									<!--FILED END-->
									<!--FILED END-->
									<!--                            <div class="row">-->
									<!--                                <div class="col-md-12">-->
									<!--                                    <div class="form-group">-->
									<!--                                        <select data-placeholder="Tags" name="city_id[]" id="city_id"-->
									<!--                                                multiple required="required"-->
									<!--                                                class="chosen-select form-control">-->
									<!--                                                 <option value="">West New York</option>-->
									<!--                                                -->
									<!--                                        </select>-->
									<!--                                    </div>-->
									<!--                                </div>-->
									<!--                            </div>-->
									<!--FILED START-->
									<div class="row">
										<div class="col-md-12">
											<div class="log">
												<div class="login add-prod-high-oth">
													<h4>Highlights</h4>
													<span class="add-list-add-btn prod-add-high-oad" title="add new offer">+</span>
													<span class="add-list-rem-btn prod-add-high-ore" title="remove offer">-</span>
													<ul>
														<li>
															<!--FILED START-->
															<div class="row">
																<div class="col-md-12">
																	<div class="form-group">
																		<input type="text" name="product_highlights[]" value="2 year warranty" class="form-control" placeholder="(i.e) 1 Year Onsite Warranty">
																	</div>
																</div>
															</div>
															<!--FILED END-->
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
											<div class="log">
												<div class="login add-prod-oth">
													<h4>Specifications</h4>
													<span class="add-list-add-btn prod-add-oad" title="add new offer">+</span>
													<span class="add-list-rem-btn prod-add-ore" title="remove offer">-</span>
													<ul>
														<li>
															<!--FILED START-->
															<div class="row">
																<div class="col-md-5">
																	<div class="form-group">
																		<input type="text" class="form-control" name="product_info_question[]" value="2 times" placeholder="(i.e) Serial Number">
																	</div>
																</div>
																<div class="col-md-2">
																	<div class="form-group"> <i class="material-icons">arrow_forward</i>
																	</div>
																</div>
																<div class="col-md-5">
																	<div class="form-group">
																		<input type="text" class="form-control" name="product_info_answer[]" value="free services" placeholder="(i.e) qwerty3421">
																	</div>
																</div>
															</div>
															<!--FILED END-->
														</li>
														<li>
															<!--FILED START-->
															<div class="row">
																<div class="col-md-5">
																	<div class="form-group">
																		<input type="text" class="form-control" name="product_info_question[]" value="6th sense" placeholder="(i.e) Serial Number">
																	</div>
																</div>
																<div class="col-md-2">
																	<div class="form-group"> <i class="material-icons">arrow_forward</i>
																	</div>
																</div>
																<div class="col-md-5">
																	<div class="form-group">
																		<input type="text" class="form-control" name="product_info_answer[]" value="" placeholder="(i.e) qwerty3421">
																	</div>
																</div>
															</div>
															<!--FILED END-->
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
												<textarea class="form-control" name="product_tags" id="product_tags" placeholder="Product Tags (i.e) electronics,laptop,hp,canon"></textarea>
											</div>
										</div>
									</div>
									<!--FILED END-->
								</li>
							</ul>
							<!--FILED START-->
							<div class="row">
								<div class="col-md-12">
									<button type="submit" name="product_submit" class="btn btn-primary">Update & Submit</button>
								</div>
								<div class="col-md-12"> <a href="dashboard.html" class="skip">Go to user dashboard                                        >></a>
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
<script>
	CKEDITOR.replace('product_description');
</script>