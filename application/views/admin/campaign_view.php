<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">Campaign #<?= (int) $campaign->campaign_id ?></span>

				<?php if ($this->session->flashdata('message')): ?>
					<div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger"><?= e($this->session->flashdata('error')) ?></div>
				<?php endif; ?>

				<div class="ud-cen-s2">
					<a href="<?= base_url('admin/campaigns') ?>">&larr; All campaigns</a>

					<table class="responsive-table bordered" style="margin-top:16px;">
						<tr><th style="width:200px;">Subject</th>
							<td><?= e($campaign->subject) ?></td></tr>
						<tr><th>Status</th>
							<td><strong><?= e($campaign->status) ?></strong></td></tr>
						<tr><th>Total recipients</th>
							<td><?= (int) $campaign->total_recipients ?></td></tr>
						<tr><th>Sent</th>
							<td><?= (int) $campaign->sent_count ?></td></tr>
						<tr><th>Failed</th>
							<td><?= (int) $campaign->failed_count ?></td></tr>
						<tr><th>Created</th>
							<td><?= e(date('Y-m-d H:i', strtotime($campaign->created_at))) ?></td></tr>
						<tr><th>Started</th>
							<td><?= $campaign->started_at ? e(date('Y-m-d H:i', strtotime($campaign->started_at))) : '—' ?></td></tr>
						<tr><th>Finished</th>
							<td><?= $campaign->finished_at ? e(date('Y-m-d H:i', strtotime($campaign->finished_at))) : '—' ?></td></tr>
					</table>

					<h4 style="margin-top:24px;">Message preview</h4>
					<div style="border:1px solid #ddd;padding:16px;background:#fff;max-height:400px;overflow:auto;">
						<?php
						// Defense-in-depth: even though only admins author this,
						// strip script/style/iframe before previewing.
						$allowed = '<p><br><strong><b><em><i><ul><ol><li><a><h1><h2><h3><h4><blockquote><img><table><tr><td><th><thead><tbody><span><div><hr>';
						echo strip_tags($campaign->body_html, $allowed);
						?>
					</div>

					<div style="margin-top:24px;">
						<?php if ($campaign->status === 'draft'): ?>
							<a href="<?= base_url('admin/campaigns/edit/' . (int) $campaign->campaign_id) ?>"
							   class="btn btn-primary">Edit draft</a>
							<a href="<?= base_url('admin/campaigns/queue/' . (int) $campaign->campaign_id) ?>"
							   onclick="return confirm('Queue this campaign for sending? It will no longer be editable.');"
							   class="btn btn-success">Queue for sending</a>
						<?php elseif (in_array($campaign->status, array('queued', 'sending'), true)): ?>
							<form method="post"
								  action="<?= base_url('admin/campaigns/send-batch/' . (int) $campaign->campaign_id) ?>"
								  style="display:inline-block;">
								<label>Batch size</label>
								<input type="number" name="batch_size" value="25" min="1" max="200" style="width:80px;">
								<button type="submit" class="btn btn-primary">Send next batch</button>
							</form>
							<small style="color:#6b7280;display:block;margin-top:8px;">
								For large lists set up a cron job hitting this same URL.
							</small>
						<?php elseif ($campaign->status === 'sent'): ?>
							<span style="color:green;font-weight:bold;">Campaign sent.</span>
						<?php endif; ?>

						<a href="<?= base_url('admin/campaigns/delete/' . (int) $campaign->campaign_id) ?>"
						   onclick="return confirm('Delete this campaign and all send-log rows?');"
						   class="btn btn-link" style="color:#c00;margin-left:12px;">Delete</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
