<!-- START -->

<!--PRICING DETAILS-->

<section class=" login-reg">

	<div class="container">

		<div class="row">

			<div class="login-main">

				<div class="log-bor">&nbsp;</div>

				<div class="log log-1">

					<div class="login">

						<h4>Member Login</h4>

						<?php $reset_msg = $this->session->flashdata('login_message'); ?>
						<?php if (!empty($reset_msg)): ?>
							<div class="alert alert-success" role="alert">
								<?= html_escape($reset_msg) ?>
							</div>
						<?php endif; ?>

						<?php if ($loginSucess && !empty($loginSucess['message'])): ?>
							<div class="alert alert-danger" role="alert">
								<?= html_escape($loginSucess['message']) ?>
							</div>
						<?php endif; ?>

						<form id="login_form" name="login_form" method="post">
							<input type="hidden" name="lastpage" value="<?= $http_referer ?>">
							<div class="form-group">

								<input type="email" autocomplete="off" name="email_id" value="<?= set_value('email_id') ?>" id="email_id" class="form-control" placeholder="Enter email*" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Enter email address" value="" required>

								<div class='formError'><?= form_error('email_id') ?></div>

							</div>

							<div class="form-group">

								<input type="password" name="password" id="password" value="<?= set_value('password') ?>" class="form-control" placeholder="Enter password*" required value="">

								<div class='formError'><?= form_error('password') ?></div>

							</div>

							<button type="submit" name="login_submit" value="submit" class="btn btn-primary">Sign in</button>

						</form>

						<!-- SOCIAL MEDIA LOGIN -->

						<div class="soc-log">

							<ul>

								<li>

									<div class="g-signin2" data-onsuccess="onSignIn"></div>

								</li>



							</ul>

						</div>

						<!-- END SOCIAL MEDIA LOGIN -->

					</div>

				</div>



				<div class="log-bot">

					<ul>

						<li><a href="<?= base_url() ?>login"> <span class="ll-1">Login?</span></a>

						</li>

						<li><a href="<?= base_url() ?>register"><span class="ll-2">Create an account?</span></a>

						</li>

						<li><a href="<?= base_url('forgot-password') ?>"> <span class="ll-3">Forgot password?</span></a>

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