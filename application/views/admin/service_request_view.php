<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Service Request #<?= (int) $request->request_id ?></span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<a href="<?= base_url('admin/service-requests') ?>">&larr; Back to all requests</a>

					<table class="responsive-table bordered" style="margin-top:16px;">
						<tr><th style="width:200px;">Received</th>
							<td><?= e(date('Y-m-d H:i', strtotime($request->created_at))) ?></td></tr>
						<tr><th>Service</th>
							<td>
								<?php if (!empty($request->service_slug)): ?>
									<a href="<?= base_url('services/' . rawurlencode($request->service_slug)) ?>" target="_blank">
										<?= e($request->service_name) ?>
									</a>
								<?php else: ?>
									<em>General / custom inquiry</em>
								<?php endif; ?>
							</td></tr>
						<tr><th>Name</th>    <td><?= e($request->name) ?></td></tr>
						<tr><th>Email</th>   <td><a href="mailto:<?= e_attr($request->email) ?>"><?= e($request->email) ?></a></td></tr>
						<tr><th>Phone</th>   <td><?= e($request->phone) ?></td></tr>
						<tr><th>Company</th> <td><?= e($request->company) ?></td></tr>
						<tr><th>Budget</th>  <td><?= e(str_replace('_', ' ', (string) $request->budget_range)) ?></td></tr>
						<tr><th>IP</th>      <td><?= e($request->ip) ?></td></tr>
					</table>

					<h4 style="margin-top:24px;">Message</h4>
					<blockquote style="background:#f3f4f6;padding:16px;border-left:4px solid #6b7280;">
						<?= e_nl2br($request->message) ?>
					</blockquote>

					<h4 style="margin-top:24px;">Update status / notes</h4>
					<form method="post" action="<?= base_url('admin/service-requests/' . (int) $request->request_id) ?>">
						<div class="form-group">
							<label>Status</label>
							<?php $cur = $request->status; ?>
							<select name="status" class="form-control">
								<?php foreach (array('new','in_review','quoted','won','lost','spam') as $s): ?>
									<option value="<?= $s ?>" <?= ($cur === $s) ? 'selected' : '' ?>>
										<?= e(str_replace('_', ' ', $s)) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label>Internal notes (not shown to the requester)</label>
							<textarea name="admin_notes" rows="5" class="form-control"><?= e($request->admin_notes) ?></textarea>
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
						<a href="<?= base_url('admin/service-requests/delete/' . (int) $request->request_id) ?>"
						   onclick="return confirm('Delete this request?');"
						   class="btn btn-link" style="color:#c00;">Delete</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
