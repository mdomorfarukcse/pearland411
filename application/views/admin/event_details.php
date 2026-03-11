
<!--CENTER SECTION-->
<div class="ud-cen">
	<div class="log-bor">&nbsp;</div>	<span class="udb-inst">All Events</span>
	<div class="ud-cen-s2">
          <?php if($this->session->flashdata('message')){ ?>
            <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
          <?php } ?>
		<h2>Event Details</h2>
		<a href="<?= base_url('user/add_user_event');?>" class="db-tit-btn">Add new Event</a>
		<table class="responsive-table bordered">
			<thead>
				<tr>
					<th>No</th>
					<th>Event Name</th>
					<th>Event Date</th>
					<th>Views</th>
					<th>Edit</th>
					<th>Delete</th>
					<th>Preview</th>
				</tr>
			</thead>
			<tbody>
			    <?php
			        if($allEvents){
			            $i = 1;
			            foreach($allEvents as $event) {
			     ?>
    				<tr>
    					<td><?= $i;?></td>
    					<td><?= $event->event_name;?> <span><?= date('d, M Y',strtotime($event->created_at));?></span>
    					</td>
    					<td><?= date('d, M Y',strtotime($event->event_start_date));?></td>
    					<td><span class="db-list-rat">0</span>
    					</td>
    					<td><a href="<?= base_url('user/edit-user-event/'.$event->event_id);?>" class="db-list-edit">Edit</a>
    					</td>
    					<!--<td>-->
    					    <!--<a href="#" onclick="javascript:deleteConfirm('<?//php// echo base_url().'user/delete_user_event/'.$event->event_id;?>');" class="db-list-edit">Delete</a-->
    					<!--    >-->
    					<!--</td>-->
                        <td>
                            
                            <button type="button" class="db-list-edit" data-toggle="modal" data-target="#exampleModal-<?= $event->event_id; ?>">
                                Delete
                            </button>
                            
        					 <!--Modal -->
        					<div class="modal fade" id="exampleModal-<?= $event->event_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        
        					  <div class="modal-dialog" role="document">
        
        					    <div class="modal-content">
        
        					      <div class="modal-header">
        
        					        <h3 class="modal-title" id="exampleModalLabel">Confirmation</h3>
        
        					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        
        					          <span aria-hidden="true">&times;</span>
        
        					        </button>
        
        					      </div>
        
        					      <div class="modal-body">
        
        					        <h6>Are you sure you want to remove this event?</h6>
        
        					      </div>
        
        					      <div class="modal-footer">
        
        					        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        
        					        <a href="<?= base_url('user/delete-user-event/'.$event->event_id);?>">
        
        					        	<button type="button" class="btn btn-primary">Delete</button>
        
        					        </a>	        
        
        					      </div>
        
        					    </div>
        
        					  </div>
        
        					</div>                    
                        </td>    	
                        
    					<td><a href="<?= base_url('user/event-details/'.$event->event_id);?>" class="db-list-edit" target="_blank">Preview</a>
    					</td>
    				</tr>
			     <?php
			            $i++;
			            }
			        }
			    ?>
			</tbody>
		</table>
	</div>
</div>
			