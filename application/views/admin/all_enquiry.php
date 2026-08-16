<?php
/**
 * /admin/enquiries — every message the public forms produced.
 *
 * The tabs filter on `enquiry_type`. Before migration 0026 a callback request
 * from the packages panel on /pricing-plan was stored as `contact`, exactly
 * like a general question from /contact-us, so a sales lead arrived in the
 * middle of an unsorted, unordered list and could sit there unread. Package
 * leads are their own type now, and the list is newest-first.
 *
 * From Admin::enquiries(): $enquiries, $enquiry_type, $enquiry_counts.
 */

$enquiry_type   = isset($enquiry_type) ? (string) $enquiry_type : '';
$enquiry_counts = (isset($enquiry_counts) && is_array($enquiry_counts)) ? $enquiry_counts : array();

/** Plain-English name for an enquiry_type, for the tabs and the Type column. */
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

/**
 * The admin page an enquiry points back at, when it has one.
 *
 * Only listing/blog/event enquiries carry a row id to link to. This used to be
 * computed with two variables declared OUTSIDE the loop and never reset, so
 * once one listing enquiry had been rendered every later row inherited its
 * link — and a contact-form row, which has no id column at all, produced
 * `admin//` plus a PHP notice for reading an empty property name.
 */
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

<style>
  .enq-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 18px;
  }
  .enq-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border: 1px solid #E4E6EC;
    border-radius: 999px;
    background: #fff;
    color: #5A6472;
    font-size: 13.5px;
    font-weight: 600;
    line-height: 1.2;
    text-decoration: none;
  }
  .enq-tab:hover { border-color: #79B82A; color: #20245B; text-decoration: none; }
  .enq-tab span {
    display: inline-block;
    min-width: 20px;
    padding: 1px 6px;
    border-radius: 999px;
    background: #F2F3F5;
    color: #5A6472;
    font-size: 12px;
    text-align: center;
  }
  .enq-tab.is-active { background: #20245B; border-color: #20245B; color: #fff; }
  .enq-tab.is-active span { background: rgba(255, 255, 255, .18); color: #fff; }
</style>

<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <!-- <span class="udb-inst">Listing Enquiry</span> --> <span class="udb-inst">Enquiry</span>
        <div class="ud-cen-s2">
         <h2>Enquiry Details</h2>

        <div class="enq-tabs">
          <?php
            $enq_total = 0;
            foreach ($enquiry_counts as $enq_count) $enq_total += (int) $enq_count;

            $enq_tabs = array('' => 'All');
            // Leads first — that is the point of splitting them out.
            foreach (array('package', 'contact') as $enq_key) {
              if (isset($enquiry_counts[$enq_key])) $enq_tabs[$enq_key] = $enq_label($enq_key);
            }
            foreach ($enquiry_counts as $enq_key => $enq_count) {
              if (!isset($enq_tabs[$enq_key])) $enq_tabs[$enq_key] = $enq_label($enq_key);
            }
          ?>
          <?php foreach ($enq_tabs as $enq_key => $enq_name): ?>
            <a class="enq-tab<?= $enq_key === $enquiry_type ? ' is-active' : '' ?>"
               href="<?= base_url('admin/enquiries') . ($enq_key === '' ? '' : '?type=' . rawurlencode($enq_key)) ?>">
              <?= e($enq_name) ?>
              <span><?= (int) ($enq_key === '' ? $enq_total : $enquiry_counts[$enq_key]) ?></span>
            </a>
          <?php endforeach; ?>
        </div>

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
            <th>Save</th>
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
          <a onclick="javascript:deleteConfirm('<?php echo base_url().'admin/delete-enquiry/'.$enq->enquiry_id;?>');"  href="#" class="db-list-edit">Delete</a>
        </td>
        <td>
<!--           <span class="enq-sav" data-toggle="tooltip" title="Click to save this enquiry">
            <i class="material-icons">favorite</i>
          </span> -->

          <?php if ($enq->enquiry_save == 0) { ?>
              <button class="db-list-ststus" onclick="isSaved(1,<?= $enq->enquiry_id;?>)">No</button>
          <?php } else { ?>
              <button class="db-list-ststus" onclick="isSaved(0,<?= $enq->enquiry_id;?>)">Yes</button>
          <?php } ?>
          <input type="hidden" id="updateField" value="enquiry_save">
          <input type="hidden" id="tableName" value="enquiries">
          <input type="hidden" id="tableIdName" value="enquiry_id">
          <input type="hidden" id="id" value="<?= $enq->enquiry_id;?>">


        </td>

      </tr>
      <?php 
        $i++;   
          }
        } else {
      ?>
        <tr>
          <td colspan="10">
            <?= $enquiry_type === ''
                  ? 'No enquiries yet.'
                  : 'No ' . e(strtolower($enq_label($enquiry_type))) . ' enquiries yet.' ?>
          </td>
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
