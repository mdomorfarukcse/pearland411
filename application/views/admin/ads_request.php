 

<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Ad Request & Enquiry</span>
        <div class="ud-cen-s2 ad-table-inn">
          <h2>Ad Request</h2>
          <table class="responsive-table bordered">
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
                <th>Status</th>
                <th>Edit</th>
                <th>Delete</th>
                <th>Approve</th>
              </tr>
            </thead>
            <tbody>
              <!-- <tr>
                <td>1</td>
                <td><a href="admin-user-full-details.html?row=37"><img src="../images/user/62736rn53themes.png" alt="">Rn53 Themes<span>Join: 26, Mar 2021</span></a></td>
                <td>Home page Bottom (70$/per day)</td>
                <td>31, Mar 2021</td>
                <td>29, Apr 2021</td>
                <td><img src="../images/ads/54524soumam.png" alt=""></td>
                <td>0</td>
                <td><span class="db-list-rat">$0</span></td>
                <td><a href="#!" class="db-list-appro">InActive</a></td>
                <td><a href="admin-ads-edit.html?row=45" class="db-list-edit">Edit</a></td>
                <td><a href="admin-ads-delete.html?row=45" class="db-list-edit">Delete</a></td>
                <td><a href="admin-ads-approve.html?adsstatusadsstatusadsstatusadsstatusadsstatus=45" class="db-list-appro">Approve this</a></td>
              </tr> -->
              <?php
                if($ads && $ads != null){
                  $j = 0;
                  foreach ($ads as $key => $value) {
                    $j++;
              ?>      
              <tr>
                <td><?= $j;?></td>
                <td><a href=""><img src="<?= base_url($value->user_image); ?>" alt=""><?= $value->username;?> <span>Join: <?= date('d, M Y',strtotime($value->user_join)); ?></span></a></td>
                <td><?= $value->ads_price_name; ?>(<?= $value->ad_cost_per_day; ?>$/per day)</td>
                <td><?= date('d, M Y',strtotime($value->ad_start_date)); ?></td>
                <td><?= date('d, M Y',strtotime($value->ad_end_date)); ?></td>
                <td><img src="<?= base_url($value->ad_photo); ?>" alt=""></td>
                <td><?= $value->ad_total_days; ?></td>
                <td>$<?= $value->ad_total_cost; ?></td>
                <td>
                  <span class="db-list-rat">
                    <?= ($value->ad_status == 1)? 'Active' : 'In-Active';?>
                  </span>
                </td>
                <td><a href="<?= base_url('admin/ads-edit/'.$value->id); ?>" class="db-list-edit">Edit</a></td>
                <td>
                <a href="#" onclick="javascript:deleteConfirm('<?php echo base_url('admin/ads-history-delete/'.$value->id);?>');" class="db-list-edit">Delete</a>
                </td>
                <td>
                  <?php if ($value->ad_status == 0) { ?>
                      <button class="db-list-ststus db-list-appro" onclick="updateAdStatus(1,<?= $value->id;?>)">Approve this</button>
                  <?php } else { ?>
                      <button class="db-list-ststus db-list-appro" onclick="updateAdStatus(0,<?= $value->id;?>)">Active</button>
                  <?php } ?>                  
                </td>
              </tr>
              <?php    
                  }
                } else {
              ?>
                  <tr>
                    <td colspan="12">No Data Available!</td>

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
