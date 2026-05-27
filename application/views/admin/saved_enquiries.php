 

<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <!-- <span class="udb-inst">Listing Enquiry</span> --> <span class="udb-inst">Enquiry</span>
        <div class="ud-cen-s2">
         <h2>Enquiry Details</h2>
         <div class="ad-int-sear">
          <input type="text" id="pg-sear" placeholder="Search this page..">
        </div>
        <table data-admin-table class="responsive-table bordered tb-bold-dis" id="pg-resu">
         <thead>
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Type</th>
            <th>Page Name</th>
            <th>Delete</th>
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
          <a href="<?= base_url('admin/'.$link.'/'.$enq->$id);?>"><?= $enq->enquiry_source; ?></a>
        </td>               
        <td>
          <a onclick="javascript:deleteConfirm('<?php echo base_url().'admin/delete-saved-enquiry/'.$enq->enquiry_id;?>');"  href="#" class="db-list-edit">Delete</a>
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
<div class="ad-pgnat">
  <ul class="pagination">
    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
    <li class="page-item active"><a class="page-link" href="#">1</a></li>
    <li class="page-item"><a class="page-link" href="#">2</a></li>
    <li class="page-item"><a class="page-link" href="#">3</a></li>
    <li class="page-item"><a class="page-link" href="#">Next</a></li>
  </ul>
</div>
</div>
</div>
</section>
<!-- END -->    
