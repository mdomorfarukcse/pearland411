<!doctype html>

<html lang="en">

<head>
  <title>Pearland Admin Panel</title>
  <!--== META TAGS ==-->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="theme-color" content="#76cef1" />
  <!--== FAV ICON(BROWSER TAB ICON) ==-->
  <link rel="shortcut icon" href="<?= base_url() ?>assets/images/fav.ico" type="image/x-icon">
  <!--== GOOGLE FONTS ==-->
  <link href="https://fonts.googleapis.com/css?family=Oswald:700|Source+Sans+Pro:300,400,600,700&display=swap" rel="stylesheet">
  <!--== WEB ICON FONTS ==-->

  <link rel="preload" as="font" href="css/icon.woff2" type="font/woff2" crossorigin="anonymous">

  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!--== CSS FILES ==-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <link rel="stylesheet" href="<?= base_url() ?>assets/admin/css/admin-style.css">

  <link rel="stylesheet" href="<?= base_url() ?>assets/css/fonts.css">

  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
  <!-- Select2 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body>
  <?php if (isset($_SESSION['logged_user']) && !empty($_SESSION['logged_user'])) { ?>
    <!-- START -->
    <section>
      <div class="ad-head" style="display: block !important;">
        <div class="head-s1">
          <div class="menu">
            <i class="material-icons mopen">menu</i>
            <i class="material-icons mclose">close</i>
          </div>
          <div class="logo">
            <img src="<?= base_url('assets/images/pearland-logo.png'); ?>" class="ic-logo" />
          </div>
        </div>

        <div class="head-s3">
          <div class="head-pro">
            <img src="<?= base_url() ?>assets/images/user/3.jpg" alt="">
            <b>Profile by</b><br>
            <h4>Admin User</h4>
            <a href="<?= base_url() ?>admin_setting" class="fclick"></a>
          </div>
        </div>
      </div>
    </section>
    <!-- END -->

    <!-- START -->
    <section>
      <div class="ad-menu-lhs mshow">
        <div class="ad-menu">
          <ul>
            <li class="ic-db">
              <a href="<?= base_url() ?>admin/dashboard" class="">Dashboard</a>
            </li>

            <li class="ic-li"> <a href="#" class="">listings</a>

              <div>

                <ol>

                  <li> <a href="<?= base_url() ?>admin/all_listings">All listings</a>

                  </li>

                  <li> <a href="<?= base_url() ?>admin/add_new_listings">Add new listings</a>

                  </li>

                  <li> <a href="<?= base_url() ?>admin/create_duplicate_listing">Create duplicate listings</a></li>

                  <li> <a href="<?= base_url() ?>admin/claimed_listings">All Claim Requests</a>

                  </li>

                  <li> <a href="<?= base_url() ?>admin/trash_listing">Trash listings</a>

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
            <li class="ic-blo">
              <a href="#" class="">News</a>
              <div>
                <ol>
                  <li>
                    <a href="<?= base_url() ?>admin/news-list">All News</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/add-news">Add News</a>
                  </li>
                </ol>
              </div>
            </li>
            <li class="ic-blo">
              <a href="#" class="">Video</a>
              <div>
                <ol>
                  <li>
                    <a href="<?= base_url() ?>admin/all-video-category">Video Categories</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/video-list">All Video</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/add-video">Add Video</a>
                  </li>
                </ol>
              </div>
            </li>
            <li class="ic-blo">
              <a href="#" class="">Classifieds</a>
              <div>
                <ol>
                  <li>
                    <a href="<?= base_url() ?>admin/all-classifieds-category">Classifieds Categories</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/classifieds-list">All Classifieds</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/add-classifieds">Add Classifieds</a>
                  </li>
                </ol>
              </div>
            </li>
            <!-- <li class="ic-blo">
                <a href="#" class="">Marketplace</a>
                <div>
                  <ol>
                    <li>
                      <a href="<?= base_url('admin/all-marketplace-category'); ?>">Marketplace Categories</a>
                    </li>
                    <li>
                      <a href="<?= base_url('admin/marketplace-list'); ?>">All Marketplace</a>
                    </li>
                    <li>
                      <a href="<?= base_url('admin/add-marketplace'); ?>">Add Marketplace</a>
                    </li>
                  </ol>
                </div>
              </li> -->
            <li class="ic-blo">
              <a href="#" class="">Apartments & Rentals</a>
              <div>
                <ol>
                  <li>
                    <a href="<?= base_url() ?>admin/all-rental-category">Rentals Categories</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/rental-list">All Rentals</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/add-rental">Add Rentals</a>
                  </li>
                </ol>
              </div>
            </li>
            <li class="ic-blo">
              <a href="#" class="">House For Sale</a>
              <div>
                <ol>
                  <li>
                    <a href="<?= base_url() ?>admin/all-house-category">House Categories</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/house-list">All House</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/add-house">Add House</a>
                  </li>
                </ol>
              </div>
            </li>
            <li class="ic-blo">
              <a href="#">Ads</a>
              <div>
                <ol>
                  <li>
                    <a href="<?= base_url() ?>admin/current-ads">Current Ads</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/create-ads">Create new Ads</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/ads-request">Ad Request &amp; Enquiry</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/ads-price">Ad Pricing</a>
                  </li>
                  <li>
                    <a href="<?= base_url('admin/create-ads-price'); ?>">Create Ads Pricing</a>
                  </li>
                  <li>
                    <a href="<?= base_url() ?>admin/ads-history">Ads History</a>
                  </li>

                </ol>
              </div>
            </li>
            <li class="ic-pay">
              <a href="<?= base_url('/admin/pricing-plan'); ?>">Pricing Plans</a>
            </li>
            <li class="ic-pay">
              <a href="<?= base_url('/admin/all-subscriptions'); ?>">All Subscriptions</a>
            </li>
            <li class="ic-pay">
              <a href="<?= base_url('/admin/newsletter-subscribers'); ?>">Newsletter Subscribers</a>
            </li>
            <li class="ic-dum">
              <a href="<?= base_url('placeholder-images'); ?>">PlaceHolder Images</a>
            </li>
            <li class="ic-set">
              <a href="<?= base_url('admin-setting'); ?>">SEO & Setting</a>
            </li>

            <li class="ic-lgo">
              <a href="<?= base_url('logout') ?>">Log out</a>
            </li>

          </ul>

        </div>

      </div>

    </section>

    <!-- END -->

  <?php } ?>