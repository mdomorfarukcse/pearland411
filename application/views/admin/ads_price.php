
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
       <div class="log-bor">&nbsp;</div>
       <span class="udb-inst">Ad details</span>

        <?php if($this->session->flashdata('message')){ ?>
          <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>
        <?php } ?>	       
   
       <div class="ud-cen-s2 ad-table-inn">
        <h2>Ad Pricing and other details</h2>
        <table class="responsive-table bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Ads Name</th>
              <th>Ads Preview</th>
              <th>Ads Size</th>
              <th>Cost P/Day</th>
              <th>Status</th>
              <th>Edit</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if($prices && $prices != null){
                $j = 0;
                foreach ($prices as $key => $value) {
                  $j++;
            ?>
            <tr>
              
              <td><?= $j;?></td>
              <td><?= $value->name;?></td>
              <td>
                <img src="<?= base_url($value->img); ?>" alt="">
              </td>
              <td><?= $value->size; ?></td>
              <td><?= $value->cost; ?>$</td>
              <td><span class="db-list-rat"><?= ($value->status ==  1) ? 'Active':'In-Active';?></span></td>
              <td><a href="<?= base_url('admin/ads-price-edit/'.$value->id);?>" class="db-list-edit">Edit</a></td>
            </tr>
            <?php
                }
              } else {
            ?>
            <tr>
              <td colspan="8">No Data Available!</td>
            </tr>
            <?php } ?>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- END -->    
