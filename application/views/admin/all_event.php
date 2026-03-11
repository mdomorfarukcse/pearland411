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
					<a href="<?= base_url() ?>admin/add-new-event" class="db-tit-btn">Add new event</a>

					<table class="responsive-table bordered" id="pg-resu">
						<thead>
							<tr>
								<th>No</th>
								<th>Event Name</th>
								<th>Event Date</th>
								<th>Created by</th>
								<th>Views</th>
								<th>Status</th>
								<th>Edit</th>
								<th>Delete</th>
								<th>Preview</th>
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
										<td><?= $event->event_name; ?> <span><?= date('d, M Y', strtotime($event->created_at)); ?></span></td>
										<td><?= date('d, M Y', strtotime($event->event_start_date)); ?></td>
										<td>
											<?php if (!empty($event->name)) : ?>
												<a href="" class="db-list-ststus" target="_blank"><?= $event->name; ?></a>
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

										<td><a href="<?= base_url('admin/edit_event/' . $event->event_slug); ?>" class="db-list-edit">Edit</a></td>
										<td><a onclick="javascript:deleteConfirm('<?php echo base_url() . 'admin/delete_event/' . $event->event_id; ?>');" href="#" class="db-list-edit">Delete</a></td>
										<td><a href="<?= base_url('admin/event-details/' . $event->event_slug); ?>" class="db-list-edit" target="_blank">Preview</a></td>
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