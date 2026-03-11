    <?php if (isset($_SESSION['logged_user']) && !empty($_SESSION['logged_user'])) { ?>



      <!-- START -->



      <section>



        <div class="ad-menu-lhs mshow">



          <div class="ad-menu">



            <ul>



              <li class="ic-db">



                <a href="<?= base_url() ?>admin/dashboard" class="">Dashboard</a>



              </li>



              <li class="ic-cat">



                <a href="#" class="">Listing Category</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/all_listing_category">All Category</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add_new_listing_category">Add new Category</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/all_listing_sub_category">All Sub Category</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add_new_listing_sub_category">Add new Sub Category</a>



                    </li>



                  </ol>



                </div>



              </li>



              <li class="ic-li"> <a href="#" class="">listings</a>



                <div>



                  <ol>



                    <li> <a href="<?= base_url() ?>admin/all_listings">All listings</a>



                    </li>



                    <li> <a href="<?= base_url() ?>admin/add_new_listings">Add new listings</a>



                    </li>



                    <li> <a href="<?= base_url() ?>admin/create_duplicate_listing">Create duplicate listings</a>



                    </li>



                    <li> <a href="<?= base_url() ?>admin/claim_listing">All Claim Requests</a>



                    </li>



                    <li> <a href="<?= base_url() ?>admin/trash_listing">Trash listings</a>



                    </li>



                  </ol>



                </div>



              </li>



              <li class="ic-cat">



                <a href="#" class="">Product Category</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/all_product_category">All Category</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add_new_product_category">Add new Category</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/all_product_sub_category">All Sub Category</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add_new_product_sub_category">Add new Sub Category</a>



                    </li>



                  </ol>



                </div>



              </li>



              <li class="ic-prod">



                <a href="#" class="">Products</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/products">All Products</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add-product">Add new Product</a>



                    </li>



                  </ol>



                </div>



              </li>

              <li class="ic-eve">



                <a href="#" class="">Events</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/all-events">All Events</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add-new-event">Add new Events</a>



                    </li>



                  </ol>



                </div>



              </li>



              <li class="ic-blo">



                <a href="#" class="">Blogs</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/blog-list">All Blogs</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/add-blog">Add new Blogs</a>



                    </li>



                  </ol>



                </div>



              </li>























              <li class="ic-user">



                <a href="#" class="">Users</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-new-user-requests.html">New User Requests</a>



                    </li>



                    <li>



                      <a href="admin-new-cod-requests.html">New COD Payment Requests</a>



                    </li>



                    <li>



                      <a href="admin-all-users.html">All Users</a>



                    </li>



                    <li>



                      <a href="admin-all-users-general.html">All Users - General</a>



                    </li>



                    <li>



                      <a href="admin-all-users-service-provider.html">All Users - Service Providers</a>



                    </li>



                    <li>



                      <a href="admin-free-users.html">Free Users</a>



                    </li>



                    <li>



                      <a href="admin-standard-users.html">Standard Users</a>



                    </li>



                    <li>



                      <a href="admin-premium-users.html">Premium Users</a>



                    </li>



                    <li>



                      <a href="admin-premium-plus-users.html">Premium Plus Users</a>



                    </li>



                    <li>



                      <a href="admin-non-paid-users.html">All Non-Paid Users</a>



                    </li>



                    <li>



                      <a href="admin-paid-users.html">All Paid Users</a>



                    </li>



                    <li>



                      <a href="admin-add-new-user.html">Add new User</a>



                    </li>



                  </ol>



                </div>



              </li>

              <li class="ic-poi">



                <a href="#" class="">Listing Promotions</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-all-promotions.html">All Promotions</a>



                    </li>



                    <li>



                      <a href="admin-promote-now.html">Create New Promotions</a>



                    </li>



                    <li>



                      <a href="admin-all-points.html">All Points History</a>



                    </li>



                    <li>



                      <a href="admin-point-setting.html">Points Setting</a>



                    </li>



                  </ol>



                </div>



              </li>

              <li class="ic-enq">



                <a href="#" class="">Enquiry & Get Quote</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/enquiries">All Enquiry</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/saved_enquiry">Saved Enquiry</a>



                    </li>



                  </ol>



                </div>



              </li>

              <li class="ic-ads">



                <a href="#" class="">Ads</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-current-ads.html">Current Ads</a>



                    </li>



                    <li>



                      <a href="admin-create-ads.html">Create new Ads</a>



                    </li>



                    <li>



                      <a href="admin-ads-request.html">Ad Request & Enquiry</a>



                    </li>



                    <li>



                      <a href="admin-ads-price.html">Ad Pricing</a>



                    </li>



                    <li>



                      <a href="seo-google-adsense.html">Google AdSense</a>



                    </li>



                    



                  </ol>



                </div>



              </li>

              <li class="ic-sub">



                <a href="#" class="">Sub Admin</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-sub-admin-all.html">All Sub Admins</a>



                    </li>



                    <li>



                      <a href="admin-sub-admin-create.html">Create new Sub Admin</a>



                    </li>



                  </ol>



                </div>



              </li>



              <li class="ic-sub">



                <a href="#" class="">Footer</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-footer.html">Footer CMS</a>



                    </li>



                    <li>



                      <a href="admin-footer-popular-tags.html">Footer popular tags</a>



                    </li>



                  </ol>



                </div>



              </li>



              <li class="ic-slid">



                <a href="#" class="">Slider Images</a>



                <div>



                  <ol>



                    <li>



                      <a href="<?= base_url() ?>admin/slider_all">All Slider Images</a>



                    </li>



                    <li>



                      <a href="<?= base_url() ?>admin/slider_create">Add New Slider</a>



                    </li>



                  </ol>



                </div>



              </li>





              <li class="ic-coup">



                <a href="#" class="">Coupon and deals</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-coupons.html">All Coupons</a>



                    </li>



                    <li>



                      <a href="admin-add-new-coupons.html">Add new coupon</a>



                    </li>



                  </ol>



                </div>



              </li>

              <li class="ic-noti">



                <a href="#" class="">Send Notifications</a>



                <div>



                  <ol>



                    <li>



                      <a href="admin-notification-all.html">All Notifications</a>



                    </li>



                    <li>



                      <a href="admin-create-notification.html">Create New Notifications</a>



                    </li>



                  </ol>



                </div>



              </li>

              <li class="ic-pri">



                <a class="" href="admin-price.html">Pricing Plans</a>



              </li>

              <li class="ic-set">



                <a class="" href="admin-setting.html">Setting</a>



              </li>

              <li class="ic-lgo">



                <a href="logout.html">Log out</a>



              </li>



            </ul>



          </div>



        </div>



      </section>



      <!-- END -->  



      <?php } ?>