	<section class="login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main">
					<div class="log-bor">&nbsp;</div>
					<div class="log">
						<div class="login">
							<h4>Reset your password</h4>

							<?php if (!empty($expired)): ?>
								<div class="alert alert-danger" role="alert">
									This password reset link is invalid or has expired.
									<a href="<?= base_url('forgot-password') ?>">Request a new one</a>.
								</div>
							<?php else: ?>

								<?php if (!empty($error)): ?>
									<div class="alert alert-danger" role="alert">
										<?= html_escape($error) ?>
									</div>
								<?php endif; ?>

								<form method="post" action="<?= base_url('reset-password/' . html_escape($token)) ?>">
									<div class="form-group">
										<input type="password" name="password" class="form-control"
											   placeholder="New password (min 8 characters)*"
											   minlength="8" required>
									</div>
									<div class="form-group">
										<input type="password" name="password_confirm" class="form-control"
											   placeholder="Confirm new password*"
											   minlength="8" required>
									</div>
									<button type="submit" class="btn btn-primary">
										Set new password
									</button>
								</form>
							<?php endif; ?>
						</div>
					</div>
					<div class="log-bot">
						<ul>
							<li><a href="<?= base_url('login') ?>"><span class="ll-1">Back to login</span></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
