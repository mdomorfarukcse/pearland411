<!-- START -->
<section>
	<div class="ad-com">
		<div class="ad-dash leftpadd">
			<div class="ud-cen">
				<div class="log-bor">&nbsp;</div>
				<span class="udb-inst">events</span>
				<div class="ud-cen-s2">
					<h2>Event details</h2>
					<div class="ad-int-sear">
						<input type="text" id="pg-sear" placeholder="Search this page..">
					</div>
					<?php if ($this->session->flashdata('message')) { ?>
						<div class="log-suc">
							<p><?= $this->session->flashdata('message') ?></p>
						</div>
					<?php } ?>
					<?php if ($this->session->flashdata('error')) { ?>
						<div class="alert alert-danger" style="background:#fde2e7;color:#591A2D;border:1px solid #f1b3bf;padding:12px 16px;border-radius:10px;margin-bottom:14px;">
							<?= e($this->session->flashdata('error')) ?>
						</div>
					<?php } ?>
					<a href="<?= base_url() ?>admin/add-new-event" class="db-tit-btn">Add new event</a>

					<table data-admin-table class="responsive-table bordered" id="pg-resu">
						<thead>
							<tr>
								<th>No</th>
								<th>Event Name</th>
								<th>Event Date</th>
								<th>Created by</th>
								<th>Views</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php

							if ($allEvents) {
								$i = $start_number;
								foreach ($allEvents as $event) {

							?>
									<tr>
										<td><?= $i++; ?></td>
										<td><?= e($event->event_name); ?> <span><?= date('d, M Y', strtotime($event->created_at)); ?></span></td>
										<td><?= date('d, M Y', strtotime($event->event_start_date)); ?></td>
										<td>
											<?php if (!empty($event->name)) : ?>
												<a href="" class="db-list-ststus" target="_blank"><?= e($event->name); ?></a>
											<?php endif; ?>
										</td>
										<?php $e_views = (isset($views_counts) && isset($views_counts[$event->event_id])) ? $views_counts[$event->event_id] : 0; ?>
										<td><span class="db-list-rat"><?= $e_views; ?></span></td>

										<td>

											<?php if ($event->event_status == 0) { ?>
												<button class="db-list-ststus" onclick="updateStatus(1,<?= $event->event_id; ?>)">In-Active</button>
											<?php } else { ?>
												<button class="db-list-ststus" onclick="updateStatus(0,<?= $event->event_id; ?>)">Active</button>
											<?php } ?>
											<input type="hidden" id="updateField" value="event_status">
											<input type="hidden" id="tableName" value="events">
											<input type="hidden" id="tableIdName" value="event_id">
											<input type="hidden" id="id" value="<?= $event->event_id; ?>">
										</td>

										<td><?= admin_actions(array(
											'view'   => base_url('event-details/' . rawurlencode((string) $event->event_slug)),
											'edit'   => base_url('admin/edit_event/' . rawurlencode((string) $event->event_slug)),
											'delete' => base_url('admin/delete_event/' . (int) $event->event_id),
											'delete_confirm' => 'Delete this event?',
										), 'event') ?></td>
									</tr>
							<?php }
							} ?>
						</tbody>
					</table>
					<div class="float-right">

						<?= $link; ?>

					</div>
				</div>

			</div>
		</div>
	</div>
</section>
<!-- END -->