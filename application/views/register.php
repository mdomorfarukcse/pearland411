<!-- START -->
<!--PRICING DETAILS-->
<section class=" login-reg">
	<div class="container">
		<div class="row">
			<div class="login-main">
				<div class="log-bor">&nbsp;</div>
				<div class="log ">
					<div class="login">
						<?php 
						if(isset($registrationSucess['status'])){ $reg = false; }
            			if(!$registrationSucess['status']){ 
						?>
						<h4>Create an account</h4>
						<p>Don't have an account? Create your account. It's take less then a minutes</p>
						<form name="register_form" id="register_form" method="post">
							<div class="form-group">
								<input type="text" autocomplete="off" value="<?= (isset($reset) && $reset) ? "" : set_value('name'); ?>" name="name" id="name" class="form-control" placeholder="Name">
								<div class='formError'><?= form_error('fname') ?></div>
							</div>
							<div class="form-group">
								<input type="email" autocomplete="off" name="email_id" value="<?= (isset($reset) && $reset) ? "" : set_value('email_id'); ?>" id="email_id" class="form-control" placeholder="Email id*" required>
								<div class='formError'><?= form_error('email_id') ?></div>
							</div>
							<div class="form-group">
								<input type="password" name="password" id="password" value="<?= (isset($reset) && $reset) ? "" : set_value('password'); ?>" class="form-control" placeholder="Password*" required>
								<div class='formError'><?= form_error('password') ?></div>
							</div>
							<div class="form-group">
								<input type="text" onkeypress="return isNumber(event)" autocomplete="off" name="mobile_number" id="mobile_number" value="<?= (isset($reset) && $reset) ? "" : set_value('mobile_number'); ?>" class="form-control" placeholder="Phone">
								<div class='formError'><?= form_error('mobile_number') ?></div>
							</div>
							<div class="form-group">
								<input type="text" autocomplete="off" value="<?= (isset($reset) && $reset) ? "" : set_value('hear_us'); ?>" name="hear_us" id="hear_us" class="form-control" placeholder="How did you hear about us:">
							</div>
							<div class="form-group ">
								<select name="sale_representative" id="sale_representative" class="form-control">
									<option value="" disabled="disabled" selected="selected">Sales Representative:</option>
									<option value="">None</option>
									<option value="Joshua Hari">Joshua Hari</option>
									<option value="Gloria Bennett-Green">Gloria Bennett-Green</option>
									<option value="Shawn House">Shawn House</option>
									<option value="Melinda Trussell">Melinda Trussell</option>
									<option value="BJ Thomas">BJ Thomas</option>
									<option value="Melissa Kelton">Melissa Kelton</option>
									<option value="Rashad Ramkissoon">Rashad Ramkissoon</option>
									<option value="Rafaela Ruffino">Rafaela Ruffino</option>
									<option value="Eddie Noschese">Eddie Noschese</option>
								</select>
							</div>
							<div class="form-group">
                <h6>Zip Code</h6>
                <div class="radi-v4">
                  <input type="radio" id="77581" value="1"
                  name="zip_code" checked="">
                  <label for="77581">77581</label>
                </div>
                <div class="radi-v4">
                  <input type="radio" id="77588" value="77588"
                  name="zip_code" >
                  <label for="77588">77588</label>
                </div>    
                <div class="radi-v4">
                  <input type="radio" id="77584" value="77584"
                  name="zip_code" >
                  <label for="77584">77584</label>
                </div> 
              </div>
							<button type="submit" name="register_submit" class="btn btn-primary">Register Now</button>
						</form>
						<?php }else{ ?>
              <div class="text-center sucess">
                <p class="regiDone">
                  <i class="fa fa-check-square-o" aria-hidden="true"></i>
                </p>
                <h3>Registration successful.</h3>
                
              </div>
            <?php } ?>
					</div>
				</div>
				<div class="log-bot">
					<ul>
						<li><a href="<?= base_url() ?>login"> <span class="ll-1">Login?</span></a> 
						</li>
						<li><a href="<?= base_url() ?>register"><span class="ll-2">Create an account?</span></a> 
						</li>
						<li><a href="<?= base_url() ?>forgot_password"> <span class="ll-3">Forgot password?</span></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>
<!--END PRICING DETAILS-->
<section>
	<div class="pop-ups pop-quo">
		<!-- The Modal -->
		<div class="modal fade" id="quote">
			<div class="modal-dialog">
				<div class="modal-content">
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
								<input type="text" class="form-control" placeholder="Enter mobile number *" pattern="[7-9]{1}[0-9]{9}" title="Phone number starting with 7-9 and remaining 9 digit with 0-9" required>
							</div>
							<div class="form-group">
								<textarea class="form-control" rows="3" placeholder="Enter your query or message"></textarea>
							</div>
							<button type="submit" class="btn btn-primary">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>