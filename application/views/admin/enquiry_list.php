
<!--CENTER SECTION-->
<style>
	.ud-rhs{display: none;}
	    .ud-cen{width: 77%;}
	    @media screen and (max-width: 992px){
	        .ud-cen{width: 100%;}
	    }
</style>

<div class="ud-cen">
<div class="log-bor">&nbsp;</div> <span class="udb-inst">Leads</span>
<div class="ud-cen-s2">
	<h2>Enquiry Details</h2>
	<!-- <a href="<?//= base_url('user/add-blog');?>" class="db-tit-btn">Add new nquiry</a> -->

  <?php if($this->session->flashdata('message')){ ?>
    <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
  <?php } ?>		
					          			
	<table class="responsive-table bordered">
		<thead>
			<tr>
				<th>No</th>
				<th>Name</th>
				<th>Email</th>
				<th>Phone</th>
				<th>Message</th>
				<th>Type</th>
				<th>Page Name</th>
			</tr>
		</thead>
		<tbody>
			<?php
				if ($enquiries != NULL) {
					$i = 1;
					$link = '';
					$id = '';
					foreach ($enquiries as $enq) {
					if ($enq->enquiry_type == 'listing') {
						$link = 'listing_preview';
						$id   = 'listing_id'; 
					} 
					if ($enq->enquiry_type == 'blog') {
						$link = 'blog_details';
						$id   = 'blog_id';
					}
					if ($enq->enquiry_type == 'event') {
						$link = 'event_details';
						$id   = 'event_id';
					}
			?>			
			<tr>
				<td><?= $i; ?></td>
				<td>
					<?= $enq->enquiry_name; ?> <span><?= date('d, M Y',strtotime($enq->enquiry_created_at)); ?></span>
				</td>
				<td>
					<?= $enq->enquiry_email; ?>
				</td>
				<td>
					<?= $enq->enquiry_mobile; ?>
				</td>
				<td>
					<?= $enq->enquiry_message; ?>
				</td>
				<td>
					<?= ucfirst($enq->enquiry_type); ?>
				</td>
				<td>
					<a href="<?= base_url('user/'.$link.'/'.$enq->$id);?>"><?= $enq->enquiry_source; ?></a>
				</td>

			</tr>
			<?php	
				$i++;		
					}
				} else {
			?>
				<tr>
					<td colspan="6"> No Blogs Available!!</td>
				</tr>
			<?php } ?>			
		</tbody>
	</table>
</div>
</div>
