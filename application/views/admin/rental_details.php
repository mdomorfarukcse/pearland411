<!-- START -->
<section class="  eve-deta-pg">
    <div class="container">
        <div class="eve-deta-pg-main">
            <div class="lhs">
                <div class="img">
                    <img src="<?= base_url($rentals->rental_image); ?>" alt="">
                </div>
                <div class="head"> <span class="dat"><?= date('M d', strtotime($rentals->created_at)); ?></span>
                    <h1><?= $rentals->rental_title; ?></h1>
                </div>
            </div>
            <div class="rhs">
                <div class="list-rhs-form pglist-bg pglist-p-com">
                    <div class="quote-pop">
                        <?php if ($this->session->flashdata('message')) { ?>
                            <div class="log-suc">
                                <p><?= $this->session->flashdata('message') ?></p>
                            </div>
                        <?php } ?>
                        <h3>Register Now</h3>
                        <div id="event_detail_enq_success" class="log" style="display: none;">
                            <p>Your Enquiry Is Submitted Successfully</p>
                        </div>
                        <div id="event_detail_enq_same" class="log" style="display: none;">
                            <p>You cannot make enquiry on your own event</p>
                        </div>
                        <div id="event_detail_enq_fail" class="log" style="display: none;">
                            <p>Something Went Wrong!!!</p>
                        </div>
                        <?php
                        if (isset($logged_id)) { ?>
                            <form method="post" action="<?= base_url('user/add-enquiry/' . 'rental_details/' . $rentals->rental_id); ?>" name="detail_enquiry_form" id="detail_enquiry_form">

                                <input type="hidden" class="form-control" name="listing_id" value="" placeholder="" required>
                                <input type="hidden" class="form-control" name="event_id" value="" placeholder="" required>
                                <input type="hidden" class="form-control" name="news_id" value="<?= set_value('rental_id', $rentals->rental_id); ?>" placeholder="" required>
                                <input type="hidden" class="form-control" name="blog_id" value="" placeholder="" required>
                                <input type="hidden" class="form-control" name="product_id" value="" placeholder="" required>
                                <input type="hidden" class="form-control" name="receiving_user_id" value="<?= set_value('receiving_user_id', $rentals->user_id); ?>" placeholder="" required>
                                <input type="hidden" class="form-control" name="enquiry_sender_id" value="<?= set_value('enquiry_sender_id', $logged_id); ?>" placeholder="" required>
                                <input type="hidden" class="form-control" name="enquiry_source" value="<?= set_value('enquiry_source', $rentals->rental_title); ?>" placeholder="" required>
                                <input type="hidden" class="form-control" name="enquiry_type" value="<?= set_value('enquiry_type', 'classifieds'); ?>" placeholder="" required>

                                <div class="form-group ic-user">
                                    <input type="text" name="enquiry_name" value="<?= set_value('enquiry_name'); ?>" required="required" class="form-control" placeholder="Enter name*">
                                    <span class="text-danger"><?= form_error("enquiry_name") ? form_error("enquiry_name") : ''; ?></span>
                                </div>
                                <div class="form-group ic-eml">
                                    <input type="email" class="form-control" name="enquiry_email" placeholder="Enter email*" required="required" value="<?= set_value('enquiry_email'); ?>" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
                                    <span class="text-danger"><?= form_error("enquiry_email") ? form_error("enquiry_email") : ''; ?></span>
                                </div>
                                <div class="form-group ic-pho">
                                    <input type="text" class="form-control" name="enquiry_mobile" value="<?= set_value('enquiry_mobile'); ?>" placeholder="Enter mobile number *" pattern="[7-9]{1}[0-9]{9}" title="Phone number starting with 7-9 and remaing 9 digit with 0-9" required>
                                    <span class="text-danger"><?= form_error("enquiry_mobile") ? form_error("enquiry_mobile") : ''; ?></span>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"><?= set_value('enquiry_message'); ?></textarea>
                                    <span class="text-danger"><?= form_error("enquiry_message") ? form_error("enquiry_message") : ''; ?></span>
                                </div>
                                <button type="submit" id="detail_enquiry_submit" name="enquiry_submit" class="btn btn-primary">Submit</button>
                            </form>
                        <?php } else { ?>
                            <div class="form-notes">
                                <a href="<?= base_url('user/index'); ?>"><button class="btn btn-primary"> Please Log In First</button></a>
                                <!-- <button id="detail_enquiry_submit" name="enquiry_submit" class="btn btn-primary">Submit</button> -->
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--END-->
<!-- START -->
<section class=" eve-deta-body blog-deta-body">
    <div class="container">
        <div class="eve-deta-body-main">
            <div class="lhs">
                <p><?= strip_tags(html_entity_decode($rentals->rental_description)); ?></p>

            </div>
            <div class="rhs">

                <div class="sec-4">
                    <h4>Rental Information:</h4>
                    <ul id="pg-resu">
                        <li><b>Name</b>: <?= $rentals->rental_title; ?></li>
                        <li><b>Bedroom(s)</b>: <?= $rentals->bedrooms; ?></li>
                        <li><b>Living Area</b>: <?= $rentals->living_area; ?> Sq.Ft.</li>
                        <li><b>Bathroom(s)</b>: <?= $rentals->bathroom; ?></li>
                        <li><b>Property Age</b>: <?= $rentals->property_age; ?></li>
                        <li><b>Garage</b>: <?= $rentals->garage; ?></li>
                        <li><b>Floors</b>: <?= $rentals->floors; ?></li>
                        <li><b>Can Sublet</b>: <?= ($rentals->can_sublet == 1) ? 'Yes' : 'No'; ?></li>
                        <?php if ($rentals->display_contact == 1) { ?>
                            <li><b>Address</b>: <?= $rentals->rental_address; ?></li>
                        <?php } ?>

                        <li><b>Price</b>: <?= $rentals->rental_price; ?></li>
                        <!--<li><b>Date</b>: <? //= date('d, M Y',strtotime($rentals->created_at));
                                                ?></li>-->
                    </ul>
                </div>
            </div>
        </div>
        <div class="pro-bot-shar">
            <h4>Share this post</h4>
            <ul>
                <li>
                    <div class="sh-pro-shar sh-pro-face"> <a target="_blank" href="https://www.facebook.com/sharer/sharer.html?u=blog/12-days-fitness-chanllege?src=facebook&quote=12 Days Fitness Chanllege">Facebook</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-twi"> <a target="_blank" href="http://twitter.com/share?text=12 Days Fitness Chanllege&url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fblog%2F12-days-fitness-chanllege%3Fsrc%3Dtwitter">Twitter</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-what"> <a target="_blank" href="whatsapp://send?text=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fblog%2F12-days-fitness-chanllege%3Fsrc%3Dwhatsapp" data-action="share/whatsapp/share">WhatsApp</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-link"> <a target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fblog%2F12-days-fitness-chanllege%3Fsrc%3Dlinkedin">Linkedin</a>
                    </div>
                </li>
                <li>
                    <div style="background-color: #da271a" class="sh-pro-shar sh-pro-pin"> <a target="_blank" href="https://www.pinterest.com/pin/create/bookmarklet/?media=images/blogs/6153pexels-photo-903171.jpeg&url=http%3A%2F%2Flocalhost%2Fdirectory%2Fbizbook%2Fblog%2F12-days-fitness-chanllege%26%26src%3Dpinterest&description=12 Days Fitness Chanllege">Pinterest</a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="pro-rel-posts">
            <h4>Related Posts</h4>
            <div class="us-ppg-com us-ppg-blog">
                <ul>
                    <li>
                        <div class="pro-eve-box">
                            <div>
                                <img src="<?= base_url() ?>/assets/images/blogs/blog1.jpeg">
                            </div>
                            <div> <span>07, Jan 2020</span>
                                <h2>12 Days Fitness Chanllege</h2>
                            </div>
                            <a href="<?= base_url() ?>/news_details" class="fclick">&nbsp;</a>
                        </div>
                    </li>
                    <li>
                        <div class="pro-eve-box">
                            <div>
                                <img src="<?= base_url() ?>/assets/images/blogs/blog2.jpg">
                            </div>
                            <div> <span>07, Jan 2020</span>
                                <h2>Bike Racing Techniques</h2>
                            </div>
                            <a href="<?= base_url() ?>/news_details" class="fclick">&nbsp;</a>
                        </div>
                    </li>
                    <li>
                        <div class="pro-eve-box">
                            <div>
                                <img src="<?= base_url() ?>/assets/images/blogs/blog3.jpg">
                            </div>
                            <div> <span>07, Jan 2020</span>
                                <h2>Best Island In The World</h2>
                            </div>
                            <a href="<?= base_url() ?>/news_details" class="fclick">&nbsp;</a>
                        </div>
                    </li>
                    <li>
                        <div class="pro-eve-box">
                            <div>
                                <img src="<?= base_url() ?>/assets/images/blogs/blog3.jpg">
                            </div>
                            <div> <span>07, Jan 2020</span>
                                <h2>Online Marketing 2020</h2>
                            </div>
                            <a href="<?= base_url() ?>/news_details" class="fclick">&nbsp;</a>
                        </div>
                    </li>
                    <li>
                        <div class="pro-eve-box">
                            <div>
                                <img src="<?= base_url() ?>/assets/images/blogs/blog4.jpg">
                            </div>
                            <div> <span>07, Jan 2020</span>
                                <h2>Home Interior Design Trends</h2>
                            </div>
                            <a href="<?= base_url() ?>/news_details" class="fclick">&nbsp;</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!--END-->