<!-- START -->
<section>
  <div class="ad-com">
    <div class="ad-dash leftpadd">
      <div class="ud-cen">
        <div class="log-bor">&nbsp;</div>
        <span class="udb-inst">video</span>
        <div class="ud-cen-s2">
          <h2>Video List</h2>
          <!-- Search Box -->
          <div class="ad-int-sear">
            <input type="text" id="videosSearchBox" class="form-control" placeholder="Search by Video Name...">
          </div>
          <a href="<?= base_url('user/add-video'); ?>" class="db-tit-btn" style="margin-bottom: 16px; display: inline-block;">Add Video</a>

          <div style="clear: both; width: 100%;">
            <?php if ($this->session->flashdata('message')) { ?>
              <div class="log-suc" style="width: 100%; margin: 16px 0 8px 0; clear: both; display: block;">
                <p style="margin: 0; padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;"><?= $this->session->flashdata('message') ?></p>
              </div>
            <?php } ?>

            <!-- Subscription Details -->
            <?php
            // Entity-specific subscription view logic for videos
            $entity = 'video';
            $label = 'Videos';

            // Safe defaults so the view doesn't break if controller doesn't pass subscription vars
            $has_subscription = isset($has_subscription) ? (bool)$has_subscription : false;
            $subscription_limits = isset($subscription_limits) && is_array($subscription_limits) ? $subscription_limits : [];
            $plan_name = isset($subscription_limits['plan_name']) ? $subscription_limits['plan_name'] : 'Free';
            $limit = isset($subscription_limits[$entity]) ? (int)$subscription_limits[$entity] : -1; // -1 means Unlimited

            // Source list from controller (fallback to empty array)
            $sourceList = (isset($videoList) && is_array($videoList)) ? $videoList : [];

            // Determine what to display: non-subscribed users see no data; subscribed users use pagination data
            $displayList = $has_subscription ? $sourceList : [];

            // Pagination + usage: use actual total from database instead of paginated count
            $per_page = isset($per_page) ? (int)$per_page : 10;
            $total_available = $has_subscription ? (isset($total_rows) ? (int)$total_rows : count($displayList)) : 0;
            $used_count = $has_subscription ? $total_available : 0; // Show actual count, not limited by plan
            $is_unlimited = ($limit === -1 || $limit === 0);

            // For unlimited plans, make sure we show all available videos by adjusting display list
            if ($has_subscription && $is_unlimited && isset($total_rows) && $total_rows > count($displayList)) {
              // If we have more total videos than what's in displayList, we need to show all
              // This handles cases where pagination limits the display but user has unlimited plan
              $displayList = $sourceList; // Use full source list for unlimited users
            }

            // Gating: non-subscribed OR reached limit (for limited plans only)
            $limit_reached = (!$has_subscription) || (!$is_unlimited && $used_count >= $limit);
            $percent = (!$is_unlimited && $limit > 0) ? min(100, round(($used_count / max(1, $limit)) * 100)) : 0;
            ?>

            <div class="subscription-info-card" style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:16px;margin:16px 0;width:100%;clear:both;display:block;">
              <?php if (!$has_subscription): ?>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                  <div style="width:36px;height:36px;border-radius:50%;background:#e3f2fd;display:flex;align-items:center;justify-content:center;color:#1976d2;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false">
                      <path d="M12 .587l3.668 7.431 8.199 1.192-5.933 5.787 1.401 8.163L12 18.896 4.665 23.16l1.401-8.163L.133 9.21l8.199-1.192z" />
                    </svg>
                  </div>
                  <div style="flex:1;min-width:220px;">
                    <div style="font-weight:600;color:#2c3e50;">Premium access required</div>
                    <div style="font-size:13px;color:#6c757d;">Subscribe to unlock <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> limits and more features.</div>
                  </div>
                  <a href="<?= site_url('pricing-plan') ?>" class="btn btn-primary btn-sm">View plans</a>
                </div>
              <?php else: ?>
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                  <div style="flex:1;min-width:240px;">
                    <div style="font-weight:600;color:#2c3e50;">Your plan: <span style="color:#0d6efd;"><?= htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div style="font-size:13px;color:#6c757d;">
                      <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:
                      <?php if (!$is_unlimited): ?>
                        <span class="<?= $limit_reached ? 'text-danger' : 'text-success' ?>">
                          <?= (int)$used_count ?> / <?= (int)$limit ?>
                        </span>
                      <?php else: ?>
                        <span class="badge badge-success">∞ Unlimited</span>
                      <?php endif; ?>
                    </div>
                    <?php if (!$is_unlimited && $limit > 0): ?>
                      <div class="progress" style="height:8px;margin-top:8px;">
                        <div class="progress-bar <?= $limit_reached ? 'bg-danger' : 'bg-success' ?>" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <?php if ($limit_reached): ?>
                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#upgradeModal">
                      Upgrade to add more
                    </button>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Upgrade / choose-a-plan modal (shown only when the add button is gated).
                 Shared partial so every dashboard tells the same story — see
                 views/partials/plan_limit_modal.php. -->
            <?php include APPPATH . 'views/partials/plan_limit_modal.php'; ?>

            <script>
              // Gate the existing "Add new post" button if limit is reached, without moving elements
              document.addEventListener('DOMContentLoaded', function() {
                var limitReached = <?= $limit_reached ? 'true' : 'false' ?>;
                if (limitReached) {
                  var addBtn = document.querySelector('.db-tit-btn');
                  if (addBtn) {
                    addBtn.setAttribute('href', '#');
                    addBtn.setAttribute('data-toggle', 'modal');
                    addBtn.setAttribute('data-target', '#upgradeModal');
                    addBtn.classList.add('disabled');
                    addBtn.addEventListener('click', function(e) {
                      e.preventDefault();
                    });
                  }
                }
              });
            </script>
            <!-- End Subscription Details -->
          </div> <!-- Close container div -->

          <?php if ($has_subscription): ?>
            <table data-admin-table class="responsive-table bordered" id="pg-resu">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Video Title</th>
                  <th>Category</th>
                  <th>Created by</th>
                  <th>Views</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="videosTableBody">
                <?php
                if (!empty($displayList)) {
                  $i = isset($start_number) ? (int)$start_number : 1;
                  $rendered = 0;
                  foreach ($displayList as $video) {
                    // Enforce plan limit across pagination using global index
                    if ($limit !== -1 && $i > $limit) {
                      $i++;
                      continue;
                    }
                ?>
                    <tr>
                      <td><?= $i; ?></td>
                      <td>
                        <img src="<?= (empty($video->video_image)) ? base_url('assets/images/no_photo.jpg') :  base_url($video->video_image) ?>"><?= $video->video_title; ?><span><?= date('d, M Y', strtotime($video->created_at)); ?></span>
                      </td>
                      <td><a href="" class="db-list-ststus" target="_blank"><?= $video->category_name ?></a></td>
                      <td><a href="" class="db-list-ststus" target="_blank"><?= $video->name; ?></a></td>
                      <td><?= isset($video->views) ? $video->views : 0 ?></td>
                      <td>
                        <span class="db-list-ststus">
                          <?= $video->video_status == 1 ? 'Active' : 'Inactive'; ?>
                        </span>
                      </td>

                      <td><?= admin_actions(array(
                          'view' => base_url('video-details/' . rawurlencode((string) $video->video_slug)),
                          'edit' => base_url('user/edit-video/' . rawurlencode((string) $video->video_slug)),
                      ), 'video') ?></td>
                    </tr>
                  <?php
                    $i++;
                    $rendered++;
                  }
                  if ($rendered === 0) {
                  ?>
                    <tr>
                      <td colspan="8"> <strong class="text-red">No Video Available!!</strong></td>
                    </tr>
                  <?php }
                } else {
                  ?>
                  <tr>
                    <td colspan="8"> <strong class="text-red">No Video Available!!</strong></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <div class="float-right mt-3">
              <?php if ($used_count > $per_page) {
                echo $link;
              } ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- END -->


<script>
  // Search/filter functionality for listings table
  document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.getElementById('videosSearchBox');
    const tableBody = document.getElementById('videosTableBody');
    const rows = Array.from(tableBody.getElementsByTagName('tr'));
    searchBox.addEventListener('input', function() {
      const query = searchBox.value.toLowerCase();
      rows.forEach(row => {
        const nameCell = row.querySelector('td:nth-child(2)');
        if (nameCell) {
          const nameText = nameCell.textContent.toLowerCase();
          row.style.display = nameText.includes(query) ? '' : 'none';
        }
      });
    });
  });
</script>