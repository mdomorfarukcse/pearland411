<!-- START -->

<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Import Listings</span>

        <?php if ($this->session->flashdata('message')) { ?>
          <div class="log-suc">
            <p><?= $this->session->flashdata('message') ?></p>
          </div>
        <?php } ?>
        <?php if ($this->session->flashdata('error')) { ?>
          <div class="alert alert-danger" style="background:#fde2e7;color:#591A2D;border:1px solid #f1b3bf;padding:12px 16px;border-radius:10px;margin-bottom:14px;">
            <?= e($this->session->flashdata('error')) ?>
          </div>
        <?php } ?>

        <div class="ud-cen-s2">
          <h2>Import Listings from CSV / Excel</h2>

          <div style="max-width:820px;">
            <p style="margin-bottom:18px;color:#555;">
              Upload a <strong>.csv</strong> or <strong>.xlsx</strong> file to add many listings at once.
              The first row must be a header row. Only <code>listing_name</code> is required — every
              other column is optional. Not sure about the format?
              <a href="<?= base_url('admin/download-listing-template') ?>" style="color:#591A2D;font-weight:600;">Download the sample template</a>.
            </p>

            <form name="import_form" id="import_form" method="post"
                  action="<?= base_url('admin/import-listings') ?>" enctype="multipart/form-data"
                  style="background:#fff;border:1px solid #eee;border-radius:12px;padding:22px;margin-bottom:26px;">
              <div class="form-group" style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;">Choose file (.csv or .xlsx)</label>
                <input type="file" name="import_file" id="import_file" class="form-control"
                       accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
              </div>
              <button type="submit" name="import_submit" value="1" class="btn btn-primary">Import Listings</button>
              <a href="<?= base_url('admin/all_listings') ?>" class="skip" style="margin-left:14px;">Back to All Listings &raquo;</a>
            </form>

            <h4 style="margin-bottom:10px;">Supported columns</h4>
            <p style="color:#555;margin-bottom:12px;">
              Header names are matched loosely (case-insensitive, spaces or underscores are fine).
              Common aliases such as <code>Name</code>, <code>Phone</code>, <code>Zip</code>,
              <code>Website</code> and <code>Description</code> are also recognised.
            </p>

            <div style="overflow-x:auto;">
              <table class="responsive-table bordered" style="width:100%;border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #eee;">Column</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #eee;">Required</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #eee;">Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $columns = array(
                    array('listing_name', 'Yes', 'Business / listing title.'),
                    array('category', 'No', 'Category name (e.g. "Medical Services") or its numeric id. Unknown categories cause the row to be skipped.'),
                    array('sub_category', 'No', 'Sub-category name or numeric id.'),
                    array('user_email', 'No', 'Owner user\'s email. Falls back to a default user when blank or unmatched. You may use user_id instead.'),
                    array('listing_mobile', 'No', 'Phone / mobile number.'),
                    array('listing_whatsapp', 'No', 'WhatsApp number.'),
                    array('listing_email', 'No', 'Public contact email.'),
                    array('listing_website', 'No', 'Website URL.'),
                    array('listing_address', 'No', 'Street address.'),
                    array('county_name', 'No', 'County.'),
                    array('postal_code', 'No', 'ZIP / postal code.'),
                    array('listing_lat', 'No', 'Latitude. When both lat & lng are set, a Google Map embed is generated automatically.'),
                    array('listing_lng', 'No', 'Longitude.'),
                    array('listing_description', 'No', 'Full listing description.'),
                    array('service_locations', 'No', 'Areas served.'),
                    array('service_offered', 'No', 'Services offered.'),
                    array('listing_video', 'No', 'Video URL.'),
                    array('listing_seo_title', 'No', 'SEO title.'),
                    array('listing_seo_keywords', 'No', 'SEO keywords.'),
                    array('listing_seo_description', 'No', 'SEO meta description.'),
                  );
                  foreach ($columns as $c) { ?>
                    <tr>
                      <td style="padding:8px;border-bottom:1px solid #f2f2f2;"><code><?= $c[0] ?></code></td>
                      <td style="padding:8px;border-bottom:1px solid #f2f2f2;"><?= $c[1] ?></td>
                      <td style="padding:8px;border-bottom:1px solid #f2f2f2;color:#555;"><?= e($c[2]) ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

            <?php if (!empty($categories)) { ?>
              <h4 style="margin:22px 0 10px;">Available categories</h4>
              <p style="color:#555;">
                <?php
                $names = array();
                foreach ($categories as $cat) {
                  if (!empty($cat->category_name)) {
                    $names[] = e($cat->category_name);
                  }
                }
                echo implode(', ', $names);
                ?>
              </p>
            <?php } ?>

            <p style="color:#777;margin-top:20px;font-size:13px;">
              Imported listings are created as <strong>In-Active</strong> so you can review them
              before publishing. Old binary <code>.xls</code> files are not supported — re-save them
              as <code>.xlsx</code> or <code>.csv</code>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- END -->
