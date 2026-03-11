
<!--CENTER SECTION-->
<div class="ud-cen">
<div class="log-bor">&nbsp;</div> <span class="udb-inst">Coupons</span>          		
<?php if($this->session->flashdata('message')){ ?>
	<div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
<?php } ?>
<div class="ud-cen-s2">
	<h2>Coupons</h2>
	<a href="<?= base_url('user/add-coupon');?>" class="db-tit-btn">Add new Coupon</a>
<!-- 	<ul class="nav nav-tabs">
		<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#coupon">All Coupon Details</a>
		</li>
		<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#couponacc">Coupon used members</a>
		</li>
	</ul> -->

	<div class="tab-content">
		<div id="coupon" class="container tab-pane active">
			<div class="db-coupons">
				<ul>				
				<?php
					if ($coupons != null) {
						$i = 1;
						foreach ($coupons as $coupon) {
				?>
					<li>
						<div class="db-coup-lhs">
							<div class="coup-box">
								<div class="coup-box-1">
									<div class="s1">
										<div class="lhs">
											<img src="<?= base_url($coupon->coupon_photo);?>">
										</div>
										<div class="rhs">
											<h4><?= $coupon->coupon_name; ?></h4>
										</div>
									</div>
									<div class="s2">
										<div class="lhs"> <span>Expires</span>
											<h6><?= date('d, M Y', strtotime($coupon->coupon_end_date)); ?></h6>
											<a href="">Terms &amp; Conditions Apply</a>
										</div>
										<div class="rhs"> <a href=""><span class="get-coup-btn get-coup-act">Get coupon</span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="db-coup-rhs">
							<h5>
                            <b>00</b>
                            <span>Members access this coupon</span>
                        </h5>
							<ol>
								<li><b>Start
                                    date:</b><?= date('d, M Y', strtotime($coupon->coupon_start_date)); ?></li>
								<li><b>Expiry
                                    date:</b> <?= date('d, M Y', strtotime($coupon->coupon_end_date)); ?></li>
								<li><b>Coupon code:</b> <?= $coupon->coupon_code; ?></li>
								<li> <a href="<?= base_url('user/edit-coupon/'.$coupon->coupon_id);?>">Edit</a>
									<a href="#" onclick="javascript:deleteConfirm('<?php echo base_url('user/delete-coupon/').$coupon->coupon_id;?>');">Delete</a>
								</li>
							</ol>
						</div>
					</li>
				<?php	
				$i++;
						}
					} else {
				?>
					<li>
						<h5> No Coupons Available!! </h5>
					</li>
				<?php } ?>		
				<div style="float: right">
				    <?php echo $link?>
				</div>			
				</ul>
			</div>
		</div>
		<div id="couponacc" class="container tab-pane fade">
			<table class="responsive-table bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Coupon name</th>
						<th>Profile</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td> <span>01, Jan 1970</span>
						</td>
						<td></td>
						<td></td>
						<td>Bizbook 50% Off</td>
						<td><a href="profile.html" target="_blank" class="db-list-edit">View</a>
						</td>
						<td><a href="#" class="db-list-edit">Delete</a>
						</td>
					</tr>
                    <tr>
						<td>2</td>
						<td> <span>01, Jan 1970</span>
						</td>
						<td></td>
						<td></td>
						<td>Amazes 50% Off</td>
						<td><a href="profile.html" target="_blank" class="db-list-edit">View</a>
						</td>
						<td><a href="#" class="db-list-edit">Delete</a>
						</td>
					</tr>
                    <tr>
                        <td>2</td>
						<td> <span>01, Jan 1970</span>
						</td>
						<td></td>
						<td></td>
						<td>Bizbook 50% Off</td>
						<td><a href="profile.html" target="_blank" class="db-list-edit">View</a>
						</td>
						<td><a href="#" class="db-list-edit">Delete</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
</div>
			