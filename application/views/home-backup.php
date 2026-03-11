<style>
    .caro-home {
        margin-top: 4% !important;
    }

    .home_slider .container-fluid {}

    .hom-head {

        padding: 0px !important;
        margin-bottom: 0px !important;
    }

    .hom-top {
        transition: all 0.5s ease;
        background: #1e68ac;
        box-shadow: none;
    }

    .dmact .top-ser {
        display: block;
    }

    .caro-home {
        margin-top: 90px;
        float: left;
        width: 100%;
    }

    .carousel-item:before {
        background: none;
    }

    .home_slider {
        padding: 20px 0px 5px 0px;
    }

    .home_slider .owl-nav,
    .home_slider .owl-dots {
        display: none;
    }

    .home_slider .eve-box div:nth-child(2) {
        padding: 10px 5px 5px 15px;
    }

    .home_slider .eve-box div:nth-child(2) h4 {
        padding-bottom: 0px !important;
    }

    .home_slider .eve-box div:nth-child(3) {
        width: 100%;
        padding: 10px 15px 10px 15px;
    }

    .home_slider .pro-eve-box {
        margin: 5px !important;
    }

    .home_slider .us-ppg-com {
        padding-top: 0px !important;
    }

    .mt-15 {
        margin-top: 15px !important;
    }

    .home_slider .us-ppg-blog .pro-eve-box p {
        font-size: 12px;
        margin: 0px;
        line-height: 14px;
        margin-bottom: 6px;
        position: absolute;
        margin-top: -200px;
        background: #3aa372;
        color: #fff;
        padding: 3px 8px;
        font-weight: 500;
        border-radius: 50px;
    }

    .carousel-indicators li {
        width: 29px !important;
    }

    /*--========== NEWS & MAGAZINE  =========--*/

    .news-top-menu {
        margin-top: 60px;
        background: #000000;
        float: none;
        width: 100%;
    }

    .news-menu {
        float: left;
        width: 100%;
    }

    .news-menu ul {
        margin: 0 auto;
        display: table;
    }

    .news-menu ul li {
        float: left;
    }

    .news-menu ul li a {
        color: #eaeaeb;
        font-size: 14px;
        padding: 10px 12px;
        display: inline-block;
        font-weight: 400;
        position: relative;
        border-bottom: 2px solid #0b0b0c;
    }

    .news-menu ul li a:after {
        content: '';
        position: absolute;
        width: 1px;
        height: 12px;
        background: #515156;
        right: 0px;
        top: 15px;
    }

    .news-menu ul li a.act {
        border-bottom: 2px solid #b71d16;
        color: #ffffff;
        font-weight: 500;
        background: #010101;
    }

    .news-menu ul li a:hover {
        border-bottom: 2px solid #dc4e41;
    }

    .news-hom-ban {
        float: left;
        width: 100%;
        text-align: center;
        background: #161e24;
        padding: 45px 0px;
        color: #fff;
        position: relative;
    }

    .news-hom-ban:before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        background: url('../images/face.jpg');
        background-size: 240px;
        opacity: 0.3;
    }

    .news-hom-ban-inn {
        position: relative;
    }

    .news-hom-ban-inn h1 {
        font-size: 40px;
        font-weight: 600;
    }

    .news-hom-ban-inn h1 b {
        /* font-weight: 700; */
        color: #fff;
        background: #b71d16;
        padding: 0px 15px;
        border-radius: 2px;
    }

    .news-hom-ban-inn p {
        font-size: 16px;
        font-weight: 400;
        margin-bottom: 0px;
    }

    .news-last {
        float: left;
        width: 100%;
        margin-bottom: 90px;
    }

    .news-hom-ban-sli {
        float: left;
        width: 100%;
        overflow: hidden;
    }

    .news-hom-ban-sli-inn ul {
        position: relative;
    }

    .news-hom-ban-sli-inn ul li {
        float: left;
        width: 20%;
    }

    @media screen and (max-width:768px) {
        .news-menu ul {
            white-space: nowrap;
            overflow: hidden;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
            display: block;
        }

        .news-menu ul li {
            float: initial;
            display: contents;
        }
    }

    @media screen and (max-width:980px) {
        .news-top-menu {
            margin-top: 55px;
        }
    }

    .news-rhs-cate {
        float: left;
        width: 100%;
        margin: 0 0 30px 0;
    }

    .news-rhs-cate ul li {
        margin-bottom: 8px;
    }

    .news-rhs-cate ul li a {
        background: url(<?= base_url() ?>assets/images/home4.jpg);
        display: block;
        padding: 15px 30px;
        border-radius: 5px;
        position: relative;
        color: #fff;
        font-size: 18px;
        z-index: 0;
        background-position: -41px -464px;
    }

    .news-rhs-cate ul li:nth-child(1) a {
        background-position: -41px -464px;
    }

    .news-rhs-cate ul li:nth-child(2) a {
        background-position: -41px -669px;
    }

    .news-rhs-cate ul li:nth-child(3) a {
        background-position: -41px -923px;
    }

    .news-rhs-cate ul li:nth-child(4) a {
        background-position: -41px -991px;
    }

    .news-rhs-cate ul li:nth-child(5) a {
        background-position: -41px -962px;
    }

    .news-rhs-cate ul li:nth-child(6) a {
        background-position: -284px -878px;
    }

    .news-rhs-cate ul li:nth-child(7) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(8) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(9) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(10) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(11) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(12) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(13) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(14) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(15) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(16) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(17) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li:nth-child(18) a {
        background-position: 0px 0px;
    }

    .news-rhs-cate ul li a:before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0px;
        top: 0px;
        right: 0px;
        bottom: 0px;
        background: #0000006b;
        border-radius: 5px;
    }

    .news-rhs-cate ul li a:after {
        content: 'trending_flat';
        color: #fff;
        font-size: 24px;
        right: 30px;
        top: 13px;
        transition: all 0.5s ease;
    }

    .news-rhs-cate ul li a:hover:after {
        right: 25px;
    }

    .news-rhs-cate ul li a span {
        width: 28px;
        height: 28px;
        background: #e50c0c;
        display: inline-block;
        text-align: center;
        padding: 2px;
        line-height: 26px;
        border-radius: 50px;
        margin-right: 10px;
        color: #fff;
        font-weight: 500;
        position: relative;
        font-size: 15px;
    }

    .news-rhs-cate ul li a b {
        position: relative;
        font-weight: 500;
    }

    .hom-eve-lhs-3 {
        float: left;
        width: 100%;
    }

    /* NEW CSS HERE FROM ST */
    .py-80 {
        padding-top: 80px !important;
        padding-bottom: 80px !important;
    }

    .pt-80 {
        padding-top: 80px !important;
    }

    .pb-80 {
        padding-bottom: 80px !important;
    }

    .primaryBG {
        background-color: #8bc541 !important;
    }

    /* Mobile Responsive */

    @media (max-width: 767px) {

        .py-80 {
            padding-top: 0px !important;
            padding-bottom: 0px !important;
        }

    }
</style>

<section>
    <div class="home_slider">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-xs-12 col-sm-12">
                    <div class="home-city">
                        <div class="d-flex justify-content-between banner_d">
                            <div class="banner_slider mb-3">
                                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                                    <ol class="carousel-indicators">
                                        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active">
                                        </li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                                    </ol>
                                    <div class="carousel-inner">
                                        <?php

                                        if ($slide_latest_news) {
                                            $firstSlideCount = 1;
                                            foreach ($slide_latest_news as $key => $value) {
                                        ?>
                                                <div class="carousel-item <?= ($firstSlideCount == 1) ? 'active' : ''; ?>">
                                                    <div class="hcity">
                                                        <div>
                                                            <img src="<?= (empty($value->news_image)) ? base_url('assets/images/no_image.png') : base_url($value->news_image); ?>"
                                                                alt="">
                                                        </div>
                                                        <div>
                                                            <img src="<?= (empty($value->news_image)) ? base_url('assets/images/no_image.png') : base_url($value->news_image); ?>"
                                                                alt="">
                                                            <h4><?= $value->news_title ?></h4>
                                                            <p>By : <?= $value->name; ?></p>
                                                            <div class="list-rat-all">
                                                                <b>NEWS</b>
                                                            </div>
                                                        </div> <a href="<?= base_url('user/news_preview/' . $value->news_id) ?>"
                                                            class="fclick">&nbsp;</a>
                                                    </div>
                                                </div>
                                        <?php
                                                $firstSlideCount++;
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="banner_boxes">
                                <?php
                                $display = 0;
                                if ($spolight_listing) {

                                    foreach ($spolight_listing as $key => $value) {
                                        $display++;
                                        if ($display <= 4) {
                                ?>
                                            <div>
                                                <div class="hcity">
                                                    <div>
                                                        <img class="banner_bximg" src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_image.png') : base_url($value->listing_image); ?>"
                                                            alt="">
                                                    </div>
                                                    <div>
                                                        <h4><?= $value->listing_name; ?></h4>
                                                        <p>By : <?= $value->name; ?></p>
                                                        <div class="list-rat-all">
                                                            <b>Business Spotlight</b>
                                                        </div>
                                                    </div><a href="<?= base_url('user/listing_preview/' . $value->listing_slug) ?>"
                                                        terget="_blank" class="fclick">&nbsp;</a>
                                                </div>
                                            </div>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                            <!-- Advertisment Card -->
                            <div class="w-20 adver_card">
                                <div class="card" style="width: 18rem;">
                                    <img class="card-img-top" src="https://www.woodlandsonline.com/images/newsimage/81006/323scopio-b5992935-2e13-49c6-b0dd-1a80baff623b.jpg" alt="Card image cap">
                                    <div class="p-3 text-center">
                                        <p class="">Join Fielding’s in celebrating all things Irish with a special pub menu and handcrafted Irish-inspired cocktails from our bar team. On St. Patrick’s Day (Monday, March 17).
                                        </p>
                                        <a href="#" class="btn btn-warning text-white mt-4">Go somewhere</a>
                                    </div>
                                </div>
                            </div>
                            <!-- Advertisment Card -->

                        </div>
                        <!-- MORE BOXES -->
                        <div class="d-block more_boxes">
                            <div class=" d-flex w-full">
                                <?php
                                $display = 0;
                                $demoImg = "https://images.unsplash.com/photo-1587387119725-9d6bac0f22fb?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8aG9yaXpvbnRhbHxlbnwwfHwwfHx8MA%3D%3D";
                                if ($spolight_listing) {

                                    foreach ($spolight_listing as $key => $value) {
                                        $display++;
                                        if ($display <= 4) {
                                ?>
                                            <div class="w-full">
                                                <div class="hcity">
                                                    <div>
                                                        <img class="box_img" src="<?= (empty($value->listing_image)) ? $demoImg : base_url($value->listing_image); ?>"
                                                            alt="">
                                                    </div>
                                                    <div>
                                                        <h4><?= $value->listing_name; ?></h4>
                                                        <p>By : <?= $value->name; ?></p>
                                                        <div class="list-rat-all">
                                                            <b>Business Spotlight</b>
                                                        </div>
                                                    </div><a href="<?= base_url('user/listing_preview/' . $value->listing_slug) ?>"
                                                        terget="_blank" class="fclick">&nbsp;</a>
                                                </div>
                                            </div>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 banner_ads">
                    <div class="adimg">
                        <img src="https://www.woodlandsonline.com/images/imgs/CopenderoRange23_tallsq_C.jpg" alt="" class="img-responsive lhimg">

                    </div>
                    <div class="adimg">
                        <img src="https://www.woodlandsonline.com/images/imgs/AHTW-WOLAd-300x100-RD1-1-12-17.jpg" alt=""
                            class="img-responsive">
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- ADs START -->
<section>
    <div class="str">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="ban-ati-com ads-all-list">

                        <?php
                        if ($hpbsl) {
                        ?>
                            <a href="<?= base_url($hpbsl->ad_link) ?>">
                                <span>Ad</span>
                                <img src="<?= $hpbsl->ad_photo; ?>" height="80" alt="">
                            </a>
                        <?php
                        } else {
                        ?>
                            <a href="#">
                                <span>Ad</span>
                                <img src="https://unsplash.it/g/1100/100" height="100" alt="">
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End -->



<!-- START -->
<section class="feature_section">
    <div class="str">
        <div class="container">
            <div class="row">
                <div class="home-tit">
                    <h2><span>Feature Business</span> in Pearland411</h2>
                    <div class="divider_custom"></div>
                    <p>List your business here and make it featured Business</p>

                </div>

                <div class="hom-event">

                    <div class="hom-eve-com hom-eve-lhs">

                        <div class="hom-eve-lhs-1 col-md-4">
                            <?php
                            $display = 0;
                            if ($featured_listing) {
                                foreach ($featured_listing as $key => $value) {
                                    $display++;
                                    if ($display < 2) {
                            ?>
                                        <div class="eve-box">
                                            <div>
                                                <a href="<?= base_url('user/listing_preview/' . $value->listing_slug) ?>">
                                                    <img src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_photo.jpg') : base_url($value->listing_image); ?>"
                                                        alt="">
                                                    <span><b><?= $value->category_name ?></b></span>
                                                </a>
                                            </div>
                                            <div>
                                                <h4>
                                                    <a
                                                        href="<?= base_url('user/listing_preview/' . $value->listing_slug) ?>"><?= $value->listing_name ?></a>
                                                </h4>
                                                <?php if ($value->display_address == '1') { ?>
                                                    <span class="addr"><?= $value->listing_address ?></span>
                                                <?php } ?>
                                                <?php if ($value->display_phone == '1') { ?>
                                                    <span class="pho"><?= $value->listing_mobile; ?></span>
                                                <?php } ?>
                                            </div>
                                        </div>

                            <?php
                                    }
                                }
                            }

                            ?>
                        </div>

                        <div class="hom-eve-lhs-2 col-md-4">

                            <ul>
                                <?php
                                $display = 0;
                                if ($featured_listing) {
                                    foreach ($featured_listing as $key => $value) {
                                        $display++;
                                        if ($display > 2 && $display < 8) {
                                ?>
                                            <li>
                                                <div class="eve-box-list">
                                                    <img src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_photo.jpg') : base_url($value->listing_image); ?>"
                                                        alt="">
                                                    <h4 title="<?= $value->listing_name ?>"><?= $value->listing_name ?></h4>
                                                    <?php if ($value->display_address == '1') { ?>
                                                        <p><?= $value->listing_address ?></p>
                                                    <?php } ?>
                                                    <a href="<?= base_url('user/listing_preview/' . $value->listing_slug) ?>"
                                                        class="fclick"></a>
                                                </div>
                                            </li>
                                <?php
                                        }
                                    }
                                }
                                ?>


                            </ul>

                        </div>

                        <div class="hom-eve-lhs-2 col-md-4">

                            <div class="filt-com lhs-ads">

                                <ul>

                                    <li>

                                        <div class="ads-box">

                                            <?php

                                            if ($fbr1) {

                                            ?>

                                                <a href="<?= base_url($fbr1->ad_link) ?>">

                                                    <span>Ad</span>

                                                    <img src="<?= $fbr1->ad_photo; ?>" alt="">

                                                </a>

                                            <?php

                                            } else {

                                            ?>

                                                <a href="#">

                                                    <span>Ad</span>

                                                    <img src="https://unsplash.it/g/340/80" height="80" alt="">

                                                </a>

                                            <?php

                                            }

                                            ?>

                                        </div>

                                    </li>

                                    <li>

                                        <div class="ads-box">

                                            <?php

                                            if ($fbr2) {

                                            ?>

                                                <a href="<?= base_url($fbr2->ad_link) ?>">

                                                    <span>Ad</span>

                                                    <img src="<?= $fbr2->ad_photo; ?>" alt="">

                                                </a>

                                            <?php

                                            } else {

                                            ?>

                                                <a href="#">

                                                    <span>Ad</span>

                                                    <img src="https://unsplash.it/g/340/80" height="80" alt="">

                                                </a>

                                            <?php

                                            }

                                            ?>

                                        </div>

                                    </li>

                                    <li>

                                        <div class="ads-box">

                                            <?php

                                            if ($fbr3) {

                                            ?>

                                                <a href="<?= base_url($fbr3->ad_link) ?>">

                                                    <span>Ad</span>

                                                    <img src="<?= $fbr3->ad_photo; ?>" alt="">

                                                </a>

                                            <?php

                                            } else {

                                            ?>
                                                <a href="#">
                                                    <span>Ad</span>
                                                    <img src="https://unsplash.it/g/340/80" height="80" alt="">
                                                </a>
                                            <?php
                                            }
                                            ?>
                                        </div>

                                    </li>

                                </ul>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- END -->
<!-- ADs START -->
<section>
    <div class="str">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="ban-ati-com ads-all-list">
                        <?php
                        if ($hpbsl) {
                        ?>
                            <a href="<?= base_url($hpbsl->ad_link) ?>">
                                <span>Ad</span>
                                <img src="<?= $hpbsl->ad_photo; ?>" height="80" alt="">
                            </a>
                        <?php
                        } else {
                        ?>
                            <a href="#">
                                <span>Ad</span>
                                <img src="<?= base_url('assets/images/banner1.jpg') ?>" height="80" alt="">
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ban-ati-com ads-all-list">
                        <?php
                        if ($hpbsr) {
                        ?>
                            <a href="<?= base_url($hpbsr->ad_link) ?>">
                                <span>Ad</span>
                                <img src="<?= $hpbsr->ad_photo; ?>" height="80" alt="">
                            </a>
                        <?php
                        } else {
                        ?>
                            <a href="#">
                                <span>Ad</span>
                                <img src="<?= base_url('assets/images/banner1.jpg') ?>" height="80" alt="">
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ban-ati-com ads-all-list">
                        <?php
                        if ($hpbsl) {
                        ?>
                            <a href="<?= base_url($hpbsl->ad_link) ?>">
                                <span>Ad</span>
                                <img src="<?= $hpbsl->ad_photo; ?>" height="80" alt="">
                            </a>
                        <?php
                        } else {
                        ?>
                            <a href="#">
                                <span>Ad</span>
                                <img src="<?= base_url('assets/images/banner1.jpg') ?>" height="80" alt="">
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End -->

<!-- START -->

<section class="community_section">

    <div class="str str-full py-80">

        <div class="container">

            <div class="row">

                <div class="home-tit">

                    <h2>For The Community <span>By The Community</span></h2>
                    <div class="divider_custom"></div>

                </div>

                <div class="ho-popu-bod">

                    <!--Top Branding Hotels-->

                    <div class="col-md-4">

                        <div class="hot-page2-hom-pre-head">

                            <h4> <span>Business Spotlight </span></h4>

                        </div>

                        <div class="hot-page2-hom-pre">

                            <ul>

                                <!--LISTINGS-->

                                <?php

                                if ($spolight_listing) {

                                    foreach ($spolight_listing as $key => $value) {

                                ?>
                                        <li>

                                            <div class="hot-page2-hom-pre-1">

                                                <img src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_photo.jpg') : base_url($value->listing_image); ?>"
                                                    alt="">

                                            </div>

                                            <div class="hot-page2-hom-pre-2">

                                                <h5><?= $value->listing_name; ?></h5>

                                                <span>No: <?= $value->listing_address; ?></span>

                                            </div>

                                            <a href="<?= base_url('user/listing-preview/' . $value->listing_slug); ?>"
                                                class="fclick"></a>

                                        </li>

                                <?php

                                    }
                                }

                                ?>

                                <!--LISTINGS-->

                            </ul>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="hot-page2-hom-pre-head">

                            <h4><span>Specials & Coupons </span></h4>

                        </div>

                        <div class="hot-page2-hom-pre">

                            <ul>

                                <!--LISTINGS-->

                                <?php

                                if ($specialAndCoupons) {

                                    foreach ($specialAndCoupons as $key => $value) {

                                ?>
                                        <li>

                                            <div class="hot-page2-hom-pre-1">

                                                <img src="<?= (empty($value->listing_image)) ? base_url('assets/images/no_photo.jpg') : base_url($value->listing_image); ?>"
                                                    alt="">

                                            </div>

                                            <div class="hot-page2-hom-pre-2">

                                                <h5><?= $value->listing_name; ?></h5>

                                                <span>No: <?= $value->listing_address; ?></span>

                                            </div>

                                            <a href="<?= base_url('user/listing-preview/' . $value->listing_slug); ?>"
                                                class="fclick"></a>

                                        </li>

                                <?php

                                    }
                                } else {
                                    echo 'No Specials & Coupons';
                                }

                                ?>

                                <!--LISTINGS-->

                            </ul>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="hot-page2-hom-pre-head">

                            <h4><span>News </span></h4>

                        </div>

                        <div class="hot-page2-hom-pre">

                            <ul>

                                <!--LISTINGS-->

                                <?php
                                if ($latest_5news) {
                                    foreach ($latest_5news as $key => $value) {
                                ?>
                                        <li>
                                            <div class="hot-page2-hom-pre-1">
                                                <img src="<?= (empty($value->news_image)) ? base_url('assets/images/no_photo.jpg') : base_url($value->news_image); ?>"
                                                    alt="">
                                            </div>
                                            <div class="hot-page2-hom-pre-2">
                                                <h5><?= $value->news_title; ?></h5>
                                                <span>Date - <?= date('d-m-Y', strtotime($value->created_at)) ?></span>
                                            </div>
                                            <a href="<?= base_url('user/news_preview/' . $value->news_id); ?>" target="_blank"
                                                class="fclick"></a>
                                        </li>
                                <?php
                                    }
                                }
                                ?>

                                <!--LISTINGS-->

                            </ul>

                        </div>

                    </div>

                    <!--End Top Branding Hotels-->

                </div>

            </div>

        </div>

    </div>

</section>

<!-- END -->



<!-- START -->

<section class="buysell_section">

    <div class="hom-mpop-ser py-80">

        <div class="container">

            <div class="hom-mpop-main">

                <div class="home-tit">

                    <h2>

                        <span>Buy, Sell, Trade, and Rent within </span> The Pearland411

                    </h2>
                    <div class="divider_custom mb-5"></div>

                </div>

                <div class="col-md-6">
                    <div>
                        <h2 class="mb-4">Classifieds </h2>

                        <?php if (!empty($classifieds4)): ?>
                            <?php foreach ($classifieds4 as $classified): ?>
                                <div class="hom-mpop">
                                    <!-- POPULAR CLASSIFIED IMAGE -->
                                    <div class="col-md-3">
                                        <?php
                                        if (empty($classified->classifieds_image)) {
                                            echo "<img src='" . base_url() . "assets/images/no_photo.jpg' alt='No Photo' />";
                                        } else {
                                            echo "<img src='" . base_url() . $classified->classifieds_image . "' alt='" . htmlspecialchars($classified->title ?? 'Classified Image') . "' />";
                                        }
                                        ?>
                                    </div>

                                    <!-- POPULAR CLASSIFIED CONTENT -->
                                    <div class="col-md-9">
                                        <h3><?= htmlspecialchars($classified->classifieds_title ?? 'No Title') ?></h3>
                                        <h4><?= strip_tags(html_entity_decode($classified->classifieds_description)) ?? 'No Description' ?>
                                        </h4>
                                        <p><?= htmlspecialchars($classified->created_at ?? 'NO TIME') ?></p>
                                        <!-- The Rating Shoing Now Statically - Need to update dynamic -->
                                        <span class="rat-sh"><?= htmlspecialchars($classified->rating ?? '4.0') ?></span>
                                    </div>

                                    <a
                                        href="<?= base_url() ?>user/classifieds_details/<?= htmlspecialchars($classified->classifieds_id ?? '#') ?>">&nbsp;</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!--  POPULAR CLASSIFIED  Design sample Previous   -->

                        <div class="hom-mpop " style="display: none;">

                            <!--POPULAR CLASSIFIED IMAGE-->

                            <div class="col-md-3">

                                <img src="<?= base_url() ?>assets/images/listings/1.jpg" alt="" />

                            </div>

                            <!--POPULAR CLASSIFIED: CONTENT-->

                            <div class="col-md-9">

                                <h3>Titan Wedding Hall</h3>

                                <h4>Wedding halls</h4>

                                <p>Titan wedding happ, North street, No 2, Newyork, USA</p>

                                <span class="rat-sh">5.0</span>

                            </div>

                            <a href="<?= base_url() ?>listing_details_n">&nbsp;</a>

                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <div>
                        <h2 class="mb-4">Apartments & Rentals </h2>
                        <?php if (!empty($rentals4)): ?>
                            <?php foreach ($rentals4 as $rental): ?>

                                <div class="hom-mpop">
                                    <!-- POPULAR Rental IMAGE -->
                                    <div class="col-md-3">
                                        <?php
                                        if (empty($rental->rental_image)) {
                                            echo "<img src='" . base_url() . "assets/images/no_photo.jpg' alt='No Photo' />";
                                        } else {
                                            echo "<img src='" . base_url() . $rental->rental_image . "' alt='" . htmlspecialchars($rental->rental_title ?? 'rental Image') . "' />";
                                        }
                                        ?>
                                    </div>

                                    <!-- POPULAR rental CONTENT -->
                                    <div class="col-md-9">
                                        <h3><?= htmlspecialchars($rental->rental_title ?? 'No Title') ?></h3>
                                        <h4><?= strip_tags(html_entity_decode($rental->rental_address)) ?? 'NO Address' ?></h4>
                                        <h4><?= 'Date - ' . strip_tags(html_entity_decode($rental->created_at)) ?? 'NO Date' ?>
                                        </h4>
                                        <!-- The Rating Shoing Now Statically - Need to update dynamic -->
                                        <span class="rat-sh"><?= htmlspecialchars($rental->rating ?? '4.0') ?></span>
                                    </div>

                                    <a
                                        href="<?= base_url() ?>user/rental_details/<?= $rental->rental_id ?? '#' ?>">&nbsp;</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!--POPULAR RENTAL DESIGN SAMPLE-->
                        <div class="hom-mpop" style="display: none;">

                            <!--POPULAR LISTINGS IMAGE-->

                            <div class="col-md-3">

                                <img src="<?= base_url() ?>assets/images/listings/5.jpeg" alt="" />

                            </div>

                            <!--POPULAR LISTINGS: CONTENT-->

                            <div class="col-md-9">

                                <h3>IPM Business Software</h3>

                                <h4>Digital Products</h4>

                                <p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p>

                            </div><a href="<?= base_url() ?>listing_details_n">&nbsp;</a>

                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- END -->


<!-- START -->

<section>

    <div class="event-body events_section py-80" id="events">

        <div class="container">

            <div class="home-tit mb-0">

                <h2><span>Latest Events</span> in city </h2>
                <div class="divider_custom"></div>

            </div>

            <div class="us-ppg-com">

                <ul id="intseres">

                    <?php

                    if ($LatestEvents) {

                        foreach ($LatestEvents as $key => $value) {

                    ?>
                            <li>

                                <div class="eve-box">

                                    <div>

                                        <a href="<?= base_url('user/event_details/' . $value->event_id) ?>">

                                            <img src="<?= (empty($value->event_image)) ? base_url('assets/images/no_photo.jpg') : base_url($value->event_image); ?>"
                                                alt="">

                                            <span><?= date('M d', strtotime($value->event_start_date)); ?></span>

                                        </a>

                                    </div>
                                    <div>
                                        <h4>
                                            <a
                                                href="<?= base_url('user/event_details/' . $value->event_id) ?>"><?= $value->event_name; ?></a>
                                        </h4>

                                        <span class="addr">No: <?= $value->event_address; ?></span>

                                        <span class="pho"><?= $value->event_mobile; ?></span>

                                    </div>

                                    <div>

                                        <div class="auth">

                                            <img src="<?= base_url('assets/images/user/1.png') ?>" alt=""> <b>Hosted by</b>

                                            <br>

                                            <h4><?= $value->event_contact_name; ?></h4>

                                            <a target="_blank" href="<?= base_url('user/event_details/' . $value->event_id) ?>"
                                                class="fclick"></a>

                                        </div>

                                    </div>

                                </div>

                            </li>

                    <?php

                        }
                    }

                    ?>
                </ul>

            </div>

        </div>

    </div>

</section>

<!--END-->



<!-- START -->

<section class="video_section">

    <div class="hom-mpop-ser">

        <div class="container">

            <div class="hom-mpop-main">

                <div class="home-tit">

                    <h2>

                        <span>Video Directory </span>

                    </h2>
                    <div class="divider_custom"></div>

                </div>



                <div class="row">

                    <div class="col-md-6">
                        <?php
                        if ($videos1) {
                            foreach ($videos1 as $video) {
                                // var_dump($video);
                            }
                        }
                        ?>
                        <div class="video-box">
                            <div class="video-link">
                                <?php
                                if (empty($video->video_image)) {
                                    echo "<img src='" . base_url() . "assets/images/no_photo.jpg' alt='No Photo' />";
                                } else {
                                    echo "<img class='thumbnail' src='" . base_url() . $video->video_image . "' alt='" . htmlspecialchars($video->title ?? 'video Image') . "' />";
                                }
                                ?>
                            </div>
                            <a href="<?= base_url() ?>user/video_details/<?= htmlspecialchars($video->video_slug ?? '#') ?>">
                                <h4><?= $video->video_title; ?></h4>
                            </a>
                            <p><?= strip_tags(html_entity_decode($video->video_description)) ?? 'No Description' ?></p>

                        </div>
                        <?php


                        ?>
                        <!-- <div class="video-box">

							<div class="video-link">

								<img class="thumbnail" src="http://new.pearland411.com/assets/images/events/2.jpg">

							</div>

							<a href="">
								<h4>Between The Trees Business Talk - 070 - Jessica Munselle</h4>
							</a>

							<p>Jessica Munselle, Director of Community Development at 5Point Credit Union | Team Lead
								for The Chamber’s C.A.R.E.S. Committee visits with JJ Hollie about updates on the 2022
								Volunteer Appreciation Luncheon.</p>

						</div> -->

                        <div class="ban-ati-com ads-all-list">

                            <?php

                            if ($afl) {

                            ?>

                                <a href="<?= base_url($afl->ad_link) ?>">

                                    <span>Ad</span>

                                    <img src="<?= $afl->ad_photo; ?>" alt="">

                                </a>

                            <?php

                            } else {

                            ?>

                                <a href="#">

                                    <span>Ad</span>

                                    <img src="http://www.pearland411.com/graphic/banner.jpg" height="80" alt="">

                                </a>

                            <?php

                            }

                            ?>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div>

                            <!--POPULAR VIDEOS-->
                            <?php if (!empty($videos4)): ?>
                                <?php foreach ($videos4 as $video): ?>

                                    <div class="hom-mpop">
                                        <!-- POPULAR VIDEO IMAGE -->
                                        <div class="col-md-3">
                                            <div class="video-link">
                                                <?php
                                                if (empty($video->video_image)) {
                                                    echo "<img src='" . base_url() . "assets/images/no_photo.jpg' alt='No Photo' />";
                                                } else {
                                                    echo "<img class='thumbnail' src='" . base_url() . $video->video_image . "' alt='" . htmlspecialchars($video->title ?? 'video Image') . "' />";
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <!-- POPULAR VIDEO CONTENT -->
                                        <div class="col-md-9">
                                            <h3><?= htmlspecialchars($video->video_title ?? 'No Title') ?></h3>
                                            <h4><?= strip_tags(html_entity_decode($video->video_code)) ?? 'No Code' ?>
                                            </h4>
                                            <h4 class="video-description"><?= strip_tags(html_entity_decode($video->video_description)) ?? 'No Description' ?>
                                            </h4>

                                            <!-- The Rating Shoing Now Statically - Need to update dynamic -->
                                            <span class="rat-sh"><?= htmlspecialchars($video->rating ?? '4.0') ?></span>
                                        </div>

                                        <a
                                            href="<?= base_url() ?>user/video_details/<?= htmlspecialchars($video->video_slug ?? '#') ?>">&nbsp;</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Videos Video Design Sample -->
                            <div class="hom-mpop" style="display: none;">

                                <!--POPULAR LISTINGS IMAGE-->

                                <div class="col-md-3">

                                    <div class="video-link">

                                        <img src="<?= base_url() ?>assets/images/listings/5.jpeg" alt="" />

                                    </div>

                                </div>

                                <!--POPULAR LISTINGS: CONTENT-->

                                <div class="col-md-9">

                                    <h3>IPM Business Software</h3>

                                    <h4>Digital Products</h4>

                                    <p>No:2, 4th Avenue, Newyork, USA, Near to Airport</p>

                                </div><a href="<?= base_url() ?>listing_details_n">&nbsp;</a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<style>
    .video-link {

        display: block;

        overflow: visible;

        position: relative;

        background-color: #bbb;

        cursor: pointer;

    }



    .video-link .thumbnail {

        width: 100%;

        object-fit: contain;

        object-position: center;

    }



    .video-link::after {

        content: "";

        display: block;

        position: absolute;

        top: 0;

        left: 0;

        height: 100%;

        width: 100%;

        background-image: url("http://new.pearland411.com/assets/images/icon/play_icon.png");

        background-position: center center;

        background-repeat: no-repeat;

        background-size: 20%;

        filter: drop-shadow(0 0 10px rgba(0, 0, 0, .4));

    }



    .video-link:hover::after {

        background-size: 22%;

        -webkit-transition: background-size .2s ease-in-out;

        -moz-transition: background-size .2s ease-in-out;

        -o-transition: background-size .2s ease-in-out;

        transition: background-size .2s ease-in-out;

    }

    .video-box {
        padding: 11px;
        position: relative;
        overflow: hidden;
        background: #fdfeff;
        margin-bottom: 10px;
        -webkit-transition: all 0.5s ease;
        -moz-transition: all 0.5s ease;
        -o-transition: all 0.5s ease;
        transition: all 0.5s ease;
        box-shadow: 0px 3px 8px -3px rgba(11, 37, 58, 0.11);
        border: 1px solid #dbdbdb;

    }

    .video-box:hover {
        box-shadow: 0px 11px 9px -10px rgba(0, 0, 0, 0.52);
        transition: all 0.5s ease;
        transform: translateY(-5px);
        border: 1px solid #8bc541;
        border-radius: 5px;
    }
</style>