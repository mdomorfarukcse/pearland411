<?php
/**
 * /admin/saved-enquiry — the enquiries someone ticked "Save" on.
 *
 * Same shape as admin/all_enquiry.php, minus the filter tabs (a saved list is
 * already a hand-picked subset). Two things were wrong here and are fixed:
 *
 *  * Every field was echoed raw. `enquiry_name`, `enquiry_message` and the
 *    rest are typed by the public into /contact-us and the callback panel and
 *    stored unescaped by design ("escape on output"), so a message containing
 *    a <script> tag ran inside the admin panel. They go through e() now.
 *  * The back-link variables were declared outside the loop and never reset,
 *    so once one listing enquiry had rendered every later row inherited its
 *    link, and rows with no id column produced `admin//` plus a notice for
 *    reading an empty property name.
 *
 * From Admin::saved_enquiry(): $enquiries.
 */

/** Plain-English name for an enquiry_type. Mirrors admin/all_enquiry.php. */
$enq_label = function ($type) {
  $labels = array(
    'package'     => 'Marketing package',
    'contact'     => 'Contact form',
    'listing'     => 'Listing',
    'blog'        => 'Blog',
    'event'       => 'Event',
    'news'        => 'News',
    'video'       => 'Video',
    'product'     => 'Product',
    'rental'      => 'Rental',
    'classifieds' => 'Classifieds',
    'general'     => 'General',
  );
  $type = (string) $type;

  return isset($labels[$type]) ? $labels[$type] : ($type !== '' ? ucfirst($type) : 'Other');
};

/** The admin page this enquiry points back at, or '' when it has none. */
$enq_link = function ($enq) {
  $targets = array(
    'listing' => array('listing_preview', 'listing_id'),
    'blog'    => array('blog_details',    'blog_id'),
    'event'   => array('event_details',   'event_id'),
  );
  $type = (string) $enq->enquiry_type;

  if (!isset($targets[$type])) return '';

  list($route, $id_field) = $targets[$type];
  $id = isset($enq->$id_field) ? trim((string) $enq->$id_field) : '';

  return $id === '' ? '' : base_url('admin/' . $route . '/' . rawurlencode($id));
};
?>

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
            <th>Department</th>
            <th>Page Name</th>
            <th>Delete</th>
         </tr>
       </thead>
       <tbody>
      <?php
        if ($enquiries != NULL) {
          $i = 1;
          foreach ($enquiries as $enq) {
          $link = $enq_link($enq);
      ?>
      <tr>
        <td><?= $i; ?></td>
        <td>
          <?= e($enq->enquiry_name); ?> <span><?= date('d, M Y',strtotime($enq->enquiry_created_at)); ?></span>
        </td>
        <td>
          <?= e($enq->enquiry_email); ?>
        </td>
        <td>
          <?= e($enq->enquiry_mobile); ?>
        </td>
        <td>
          <?= e_nl2br($enq->enquiry_message); ?>
        </td>
        <td>
          <?= e($enq_label($enq->enquiry_type)); ?>
        </td>
        <td>
          <?= e(isset($enq->enquiry_department) && trim((string) $enq->enquiry_department) !== ''
                ? $enq->enquiry_department
                : '—'); ?>
        </td>
        <td>
          <?php if ($link !== ''): ?>
            <a href="<?= e_attr($link); ?>"><?= e($enq->enquiry_source); ?></a>
          <?php else: ?>
            <?= e($enq->enquiry_source); ?>
          <?php endif; ?>
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
          <td colspan="9">No saved enquiries yet.</td>
        </tr>
      <?php } ?>
      </tbody>
    </table>

  </div>
</div>
<?php /* The pager here was five hard-coded links to "#" — it never paged
         anything. The rows-per-page control on [data-admin-table] is the
         real one. */ ?>
</div>
</div>
</section>
<!-- END -->
