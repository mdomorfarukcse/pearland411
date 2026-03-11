<!-- START -->

<section>

  <div class="ad-com">

    <div class="ad-dash leftpadd">

      <div class="ud-cen">

       <div class="log-bor">&nbsp;</div>

       <span class="udb-inst">Current running ads</span>

        <?php if($this->session->flashdata('message')){ ?>

          <div class="log-suc" ><p><?= $this->session->flashdata('message') ?></p></div>

        <?php } ?>	       

       <div class="ud-cen-s2">

         <h2>Current Ads</h2>

         <div class="ad-int-sear">

          <input type="text" id="pg-sear" placeholder="Search this page..">

        </div>

        <table class="responsive-table bordered" id="pg-resu">

          <thead>

           <tr>

            <th>No</th>

            <th>Ad Position</th>

            <th>Start Date</th>

            <th>End Date</th>

            <th>Payment cost</th>

            <th>Payment Date</th>

            <th>Send Invoice</th>

            <th>Edit</th>

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

                <td><?= $j; ?></td>

                <td><?= $value->ads_price_name; ?></td>

                <td><?= date('d, M Y', strtotime($value->ad_start_date)); ?></td>

                <td><?= date('d, M Y', strtotime($value->ad_end_date)); ?></td>

                <td>$ <?= $value->ad_total_cost; ?></td>

                <td><?= date('d, M Y', strtotime($value->ad_start_date)); ?></td>

                <td><a href="" class="db-list-rat">Send</a></td>

                <td><a href="<?= base_url('admin/ads-edit/'.$value->id); ?>" class="db-list-edit">Edit</a></td>

                <td>

                  <a href="#" onclick="javascript:deleteConfirm('<?php echo base_url('admin/ads-delete/'.$value->id);?>');" class="db-list-edit">Delete</a>

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

