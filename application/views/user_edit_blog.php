<section class=" login-reg">

	<div class="container">

		<div class="row">

			<div class="login-main add-list">

				<div class="log-bor">&nbsp;</div> <span class="steps">Edit Blog post</span>

				<div class="log">

					<div class="login add-list-off">

						<h4>Edit this Blog Post</h4>



						<?php if ($this->session->flashdata('message')) { ?>

							<div class="log-suc">
								<p><?= $this->session->flashdata('message') ?></p>
							</div>

						<?php } ?>



						<form action="<?= base_url('user/edit_blog/' . $blog->blog_slug); ?>" class="blog_edit_form" id="blog_edit_form" name="blog_form" method="post" enctype="multipart/form-data">

							<ul>

								<li>

									<!--FILED START-->

									<div class="row">

										<div class="col-md-12">

											<div class="form-group">

												<input type="text" name="blog_name" required="required" value="<?= set_value('blog_name', $blog->blog_name); ?>" class="form-control" placeholder="Post Name*">

											</div>

											<span class="text-danger"><?= form_error("blog_name") ? form_error("blog_name") : ''; ?></span>

										</div>

									</div>

									<!--FILED END-->

									<!--FILED START-->

									<div class="row">

										<div class="col-md-12">

											<div class="form-group">

												<textarea name="blog_description" id="blog_description" required="required" class="form-control" placeholder="Post Details">

														<?= $blog->blog_description; ?>

															

														</textarea>

											</div>

											<span class="text-danger"><?= form_error("blog_description") ? form_error("blog_description") : ''; ?></span>

										</div>

									</div>

									<!--FILED END-->

									<!--FILED START-->

									<div class="row">

										<div class="col-md-12">

											<div class="form-group">

												<label>Choose banner image</label>

												<input type="file" name="blog_image" class="form-control">

											</div>

										</div>

									</div>

									<!--FILED END-->

									<!--FILED START-->

									<div class="row">

										<div class="col-md-12">

											<div>

												<div class="chbox">

													<input type="checkbox" name="isenquiry" id="evefmenab" <?= $blog->isenquiry == '1' ? 'checked' : '' ?>>

													<label for="evefmenab">Enquiry or Registration form enable</label>

												</div>

											</div>

										</div>

									</div>

									<!--FILED END-->

								</li>

							</ul>

							<!--FILED START-->

							<div class="row">

								<div class="col-md-12">

									<button type="submit" name="blog_submit" class="btn btn-primary">Submit</button>

								</div>

								<div class="col-md-12"> <a href="<?= base_url('user/blog-post'); ?>" class="skip">Go to Blog dashboard >></a>

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



<script src="<?= base_url('assets/ckeditor/ckeditor.js'); ?>"></script>

<script>
	CKEDITOR.replace('blog_description');
</script>