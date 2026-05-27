<!--PRICING DETAILS-->
<section class="login-reg ">
  <div class="container">
    <div class="row">
      <div class="login-main add-list ad-table">
        <div class="log-bor">&nbsp;</div>
        <span class="steps">Ad details</span>
        <div class="ad-table-inn ud-cen-s2">
          <table data-admin-table class="responsive-table bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Ads Name</th>
                <th>Ads Preview</th>
                <th>Ads Size</th>
                <th>Cost P/Day</th>
                <th>Start Ads</th>
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
                <td><a href="<?= base_url('user/post-ads');?>" class="db-list-rat">Post your Ads</a></td>
              </tr>
              <?php
                  }
                } else {
              ?>
              <tr>
                <td colspan="6">No Data Available!</td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
      <!--END PRICING DETAILS-->