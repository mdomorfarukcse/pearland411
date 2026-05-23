	<section class="login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main">
					<div class="log-bor">&nbsp;</div>
					<div class="log ">
						<div class="login">
							<h4>Forgot password</h4>

							<?php $msg = $this->session->flashdata('forgot_message'); if (!empty($msg)): ?>
								<div class="alert alert-success" role="alert">
									<?= html_escape($msg) ?>
								</div>
							<?php endif; ?>

							<?php if (!empty($error)): ?>
								<div class="alert alert-danger" role="alert">
									<?= html_escape($error) ?>
								</div>
							<?php endif; ?>

							<form id="forget_form" name="forget_form" method="post"
								  action="<?= base_url('forgot-password') ?>">
								<div class="form-group">
									<input type="email" autocomplete="off" name="email_id" id="email_id"
										   class="form-control" placeholder="Enter email*"
										   title="Invalid email address" required>
								</div>
								<button type="submit" name="forgot_submit" class="btn btn-primary">
									Email me a reset link
								</button>
							</form>
						</div>
					</div>
					<div class="log-bot">
						<ul>
							<li><a href="<?= base_url('login') ?>"><span class="ll-1">Login?</span></a></li>
							<li><a href="<?= base_url('register') ?>"><span class="ll-2">Create an account?</span></a></li>
							<li><span class="ll-3">Forgot password?</span></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
