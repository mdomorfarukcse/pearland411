	<section class="login-reg">
		<div class="container">
			<div class="row">
				<div class="login-main">
					<div class="log-bor">&nbsp;</div>
					<div class="log">
						<div class="login">
							<?php if (!empty($verified)): ?>
								<h4>Email confirmed</h4>
								<div class="alert alert-success" role="alert">
									Thanks — your email is verified. You can now sign in.
								</div>
								<a href="<?= base_url('login') ?>" class="btn btn-primary">Go to login</a>
							<?php else: ?>
								<h4>Verification failed</h4>
								<div class="alert alert-danger" role="alert">
									This verification link is invalid or has already been used.
								</div>
								<a href="<?= base_url('login') ?>" class="btn btn-primary">Back to login</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
