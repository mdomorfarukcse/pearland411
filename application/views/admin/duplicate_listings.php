<?php
/**
 * /admin/duplicate_listings — businesses the directory holds more than once.
 *
 * From Admin::duplicate_listings(): $groups, an array of arrays of listing
 * rows. Each inner array is one business that has been entered more than once.
 *
 * Migration 0032 clears the unambiguous cases by itself, so on a database that
 * has run it this page is usually empty. What is left, and what turns up here
 * afterwards, is the pairs where more than one row has an owner — those are
 * somebody's property, quite possibly somebody's paid package, and no rule
 * gets to retire one.
 */
$groups = (isset($groups) && is_array($groups)) ? $groups : array();
?>
<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">Duplicate listings</span>

        <div class="ud-cen-s2">
          <h2>Duplicate listings</h2>

          <?php if ($this->session->flashdata('message')): ?>
            <div class="log-suc"><p><?= e($this->session->flashdata('message')) ?></p></div>
          <?php endif; ?>

          <p style="margin:10px 0 18px;color:#5A6072;font-size:14px;max-width:70ch;">
            Businesses with the same name <em>and</em> the same phone number, address or website.
            Hiding one is a soft delete &mdash; it goes to
            <a href="<?= base_url('admin/trash_listings') ?>">Trash</a>, keeps its reviews and
            enquiries, and can be restored. The name on its own is never enough to call two rows
            the same business, so nothing on this page happens by itself.
          </p>

          <?php if (empty($groups)): ?>
            <div class="log-suc">
              <p>Nothing is listed twice. Every published business appears once.</p>
            </div>
          <?php else: ?>
            <p style="margin-bottom:12px;">
              <strong><?= count($groups) ?></strong>
              <?= count($groups) === 1 ? 'business appears' : 'businesses appear' ?> more than once.
            </p>

            <?php foreach ($groups as $group): ?>
              <?php $keep = $group[0]; ?>
              <div style="margin-bottom:18px;border:1px solid #e9ecef;border-radius:8px;overflow:hidden;">
                <div style="padding:10px 14px;background:#F2F3F5;font-weight:700;color:#20245B;">
                  <?= e($keep->listing_name) ?>
                  <span style="font-weight:400;color:#6b7280;">&mdash; <?= count($group) ?> entries</span>
                </div>

                <div class="table-responsive">
                  <table class="table" style="margin:0;">
                    <thead>
                      <tr>
                        <th>Listing</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Owner</th>
                        <th>Added</th>
                        <th style="width:190px;">&nbsp;</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($group as $i => $row): ?>
                        <tr<?= $i === 0 ? ' style="background:#F6FAF0;"' : '' ?>>
                          <td>
                            <a href="<?= e_url(base_url('listing-details/' . rawurlencode((string) $row->listing_slug))) ?>"
                               target="_blank" rel="noopener"><?= e($row->listing_name) ?></a>
                            <small style="display:block;color:#6b7280;">#<?= (int) $row->listing_id ?></small>
                          </td>
                          <td><?= e($row->category_name ?? '—') ?></td>
                          <td><?= e($row->listing_mobile ?? '') ?></td>
                          <td><?= e($row->listing_address ?? '') ?></td>
                          <td>
                            <?php if ((int) ($row->user_id ?? 0) > 0): ?>
                              <span class="badge badge-warning" title="Claimed — check with the owner before hiding this one">Claimed</span>
                            <?php else: ?>
                              <span style="color:#6b7280;">Unclaimed</span>
                            <?php endif; ?>
                          </td>
                          <td><?= e(us_date($row->created_at ?? '')) ?></td>
                          <td>
                            <?php // Admin::edit_listing takes the SLUG, not the id. ?>
                            <a class="db-list-edit" href="<?= e_url(base_url('admin/edit_listing/' . rawurlencode((string) $row->listing_slug))) ?>">Edit</a>
                            <?php if ($i > 0): ?>
                              <form method="post" style="display:inline;margin-left:8px;"
                                    action="<?= base_url('admin/hide_duplicate_listing') ?>"
                                    onsubmit="return confirm('Hide &quot;<?= e_attr($row->listing_name) ?>&quot; (#<?= (int) $row->listing_id ?>) as a duplicate? It moves to Trash and can be restored.');">
                                <input type="hidden" name="listing_id" value="<?= (int) $row->listing_id ?>">
                                <input type="hidden" name="kept_listing_id" value="<?= (int) $keep->listing_id ?>">
                                <button type="submit" style="border:0;background:none;padding:0;color:#6D263D;cursor:pointer;">Hide as duplicate</button>
                              </form>
                            <?php else: ?>
                              <span style="color:#6b7280;">Keeping this one</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- END -->
