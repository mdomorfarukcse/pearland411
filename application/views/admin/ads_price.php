
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
        <table data-admin-table class="responsive-table bordered">
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
              <td>
                <?= e($value->name);?>
                <?php // The names alone don't separate "#1 Right Side Banner"
                      // from "#1 Right Banner (Category)". The slot_key is what
                      // the views ask for and what a package's plans.slug must
                      // equal, so it is the only unambiguous identifier here. ?>
                <?php if (!empty($value->slot_key)): ?>
                  <br><code class="ads-price-slot"><?= e($value->slot_key) ?></code>
                <?php endif; ?>
              </td>
              <td>
                <?php // Never base_url() a possibly-empty column — that resolves
                      // to the site root and renders as a broken image. ?>
                <img class="ads-price-preview" src="<?= base_url(ad_zone_preview($value)) ?>"
                     alt="<?= e_attr($value->name . ' ad zone, shown in place on the page') ?>"
                     width="240" height="180" loading="lazy" decoding="async">
              </td>
              <td><?= e($value->size); ?></td>
              <td><?= e($value->cost); ?>$</td>
              <td><span class="db-list-rat"><?= ($value->status ==  1) ? 'Active':'In-Active';?></span></td>
              <td><a href="<?= base_url('admin/ads-price-edit/'.$value->id);?>" class="db-list-edit">Edit</a></td>
            </tr>
            <?php
                }
              } else {
            ?>
            <tr>
              <td colspan="7">No Data Available!</td>
            </tr>
            <?php } ?>
          </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- END -->    
