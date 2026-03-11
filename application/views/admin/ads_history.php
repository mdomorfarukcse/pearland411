<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Ad History</span>

        <?php if($this->session->flashdata('message')){ ?>
          <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
        <?php } ?>	       
           
        <div class="ud-cen-s2 ad-table-inn">
          <h2>Past Ads</h2>
          <div class="ad-int-sear">
            <input type="text" id="pg-sear" placeholder="Search this page..">
          </div>
          <table class="responsive-table bordered" id="pg-resu">
           <thead>
            <tr>
             <th>No</th>
             <th>User Name</th>
             <th>Ad Position</th>
             <th>Start date</th>
             <th>End date</th>
             <th>Image</th>
             <th>Ad Days</th>
             <th>Ad Cost</th>
             <th>Delete</th>
           </tr>
         </thead>
         <tbody>
          <?php
            if($ads && $ads != null){
              $j = 0;
              foreach ($ads as $key => $value) {
                $j++;
          ?>      
          <tr>
            <td>1</td>
            <td><a href=""><img src="<?= base_url($value->user_image); ?>" alt=""><?= $value->username;?> <span>Join: <?= date('d, M Y',strtotime($value->user_join)); ?></span></a></td>
            <td><?= $value->ads_price_name; ?>(<?= $value->ad_cost_per_day; ?>$/per day)</td>
            <td><?= date('d, M Y',strtotime($value->ad_start_date)); ?></td>
            <td><?= date('d, M Y',strtotime($value->ad_end_date)); ?></td>
            <td><img src="<?= base_url($value->ad_photo); ?>" alt=""></td>
            <td><?= $value->ad_total_days; ?></td>
            <td><span class="db-list-rat">$<?= $value->ad_total_cost; ?></span></td>
            <td>
            <a href="#" onclick="javascript:deleteConfirm('<?php echo base_url('admin/ads-history-delete/'.$value->id);?>');" class="db-list-edit">Delete</a>
            </td>
          </tr>
          <?php    
              }
            } else {
          ?>
              <tr>
                <td colspan="9">No Data Available!</td>

              </tr>        
          <?php
            }
          ?>

        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
</section>
<!-- END -->    
