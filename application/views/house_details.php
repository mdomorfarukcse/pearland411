<style>
    .eve-deta-pg {
        margin-top: 0px !important;
        padding-top: 50px !important;
    }

    .eve-deta-pg .lhs .img:before {
        display: none !important;
    }

    .eve-deta-body .rhs .sec-3 {
        padding-top: 0px !important;
        border-top: none !important;
    }

    .eve-deta-body .eve-deta-body-main .rhs {
        margin-top: -40px;
    }

    .eve-deta-body .eve-deta-body-main .lhs {
        border-right: none !important;
    }

    .ud-lhs-s1 {
        margin-bottom: 30px !important;
    }

    /* Sidebar Web Development Section */
    .sidebar-marketing-section {
        width: 100%;
        margin-bottom: 30px;
    }

    .sidebar-marketing-section .marketing-card {
        background: linear-gradient(145deg, #1e68ac 0%, #1e40af 100%);
        border-radius: 14px;
        padding: 40px 32px;
        position: relative;
        overflow: hidden;
        /* box-shadow: 0 20px 60px rgba(37, 99, 235, 0.3); */
    }

    .sidebar-marketing-section .marketing-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: pulseEffect 8s ease-in-out infinite;
    }

    @keyframes pulseEffect {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.5;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }

    .sidebar-marketing-section .marketing-card .card-content {
        position: relative;
        z-index: 2;
    }


    .sidebar-marketing-section .marketing-card .card-content .main-heading {
        color: white;
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 12px;
        line-height: 1.3;
    }

    .sidebar-marketing-section .marketing-card .card-content .description {
        color: rgba(255, 255, 255, 0.9);
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 28px;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper {
        margin-bottom: 28px;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature {
        display: flex;
        align-items: center;
        color: white;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature:last-child {
        margin-bottom: 0;
    }

    .sidebar-marketing-section .marketing-card .card-content .features-wrapper .feature .check-icon {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .sidebar-marketing-section .marketing-card .card-content .primary-button {
        background: white;
        color: #1e68ac;
        border: none;
        padding: 16px 32px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 99px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
        text-align: center;
        width: 100%;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .sidebar-marketing-section .marketing-card .card-content .primary-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
        background: #f8fafc;
    }

    .sidebar-marketing-section .marketing-card .card-content .primary-button:active {
        transform: translateY(0);
    }

    .pg_title {
        font-size: 40px !important;
        font-weight: 700 !important;
        margin-bottom: 20px;
    }

    @media (max-width: 480px) {
        .sidebar-marketing-section .marketing-card {
            padding: 32px 24px;
        }

        .sidebar-marketing-section .marketing-card .card-content .main-heading {
            font-size: 22px;
        }

        .sidebar-marketing-section .marketing-card .card-content .description {
            font-size: 14px;
        }

        .pg_title {
            font-size: 30px !important;
        }
    }

    /* Sidebar Web Development Section */

    .event-detail-modern {
        max-width: 800px;
        margin: 0 auto;
    }

    .event-detail-modern .head {
        border-bottom: 3px solid #1e68ac;
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .event-detail-modern .head h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        line-height: 1.3;
    }

    .event-detail-modern>p,
    .event-detail-modern>div:not(.head):not(.sec-1):not(.sec-2) {
        color: #475569;
        line-height: 1.8;
        font-size: 1rem;
        margin-bottom: 2.5rem;
    }

    .event-detail-modern .sec-1 {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-left: 4px solid #1e68ac;
        padding: 2rem 2.5rem;
        margin-bottom: 2.5rem;
    }

    .event-detail-modern .sec-1 h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 1.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .event-detail-modern .sec-1 ul {
        list-style: none;
        padding: 0;
        margin: 0;
        /* display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 1rem; */
    }

    .event-detail-modern .sec-1 ul li {
        padding: 0.75rem 0;
        color: #334155;
        font-size: 0.95rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
    }

    .event-detail-modern .sec-1 ul li:last-child {
        border-bottom: none;
    }

    .event-detail-modern .sec-1 ul li b {
        color: #1e68ac;
        font-weight: 600;
        display: inline-block;
        min-width: 120px;
    }

    .event-detail-modern .sec-2 {
        border-top: 2px solid #e2e8f0;
        padding-top: 2rem;
    }

    .event-detail-modern .sec-2 h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 1.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .event-detail-modern .sec-2 iframe,
    .event-detail-modern .sec-2 img {
        width: 100%;
        height: 400px;
        border: 2px solid #e2e8f0;
        border-radius: 4px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .list-rhs-form {
        border: none !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .event-detail-modern .head h1 {
            font-size: 1.5rem;
        }

        .event-detail-modern .sec-1 {
            padding: 1.5rem;
        }

        .event-detail-modern .sec-1 ul {
            grid-template-columns: 1fr;
        }

        .event-detail-modern .sec-2 iframe,
        .event-detail-modern .sec-2 img {
            height: 300px;
        }
    }
</style>

<!-- START -->
<section class="  eve-deta-pg">
    <div class="container">
        <div class="eve-deta-pg-main">
            <div class="lhs">
                <div class="img">
                    <!-- <img src="<?= base_url($houses->house_image); ?>" alt=""> -->
                    <img src="<?= !empty($houses->house_image) ? base_url($houses->house_image) : base_url('assets/images/no_image.png') ?>" alt="">
                </div>

            </div>
            <div class="rhs">
                <div class="list-rhs-form pglist-bg pglist-p-com">
                    <div class="quote-pop">
                        <?php if ($this->session->flashdata('message')) { ?>
                            <div class="log-suc">
                                <p><?= e($this->session->flashdata('message')); ?></p>
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
                        <?php $disabled = empty($logged_id) ? 'disabled' : ''; ?>
                        <form method="post" action="<?= base_url('user/add-enquiry/' . 'house_details/' . $houses->house_slug); ?>" name="detail_enquiry_form" id="detail_enquiry_form" data-validate>

                            <input type="hidden" class="form-control" name="listing_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="event_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="news_id" value="<?= set_value('house_id', $houses->house_slug); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="blog_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="product_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="receiving_user_id" value="<?= set_value('receiving_user_id', $houses->user_id); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="enquiry_sender_id" value="<?= set_value('enquiry_sender_id', $logged_id); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="enquiry_source" value="<?= set_value('enquiry_source', $houses->house_title); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="enquiry_type" value="<?= set_value('enquiry_type', 'house'); ?>" placeholder="" required>

                            <div class="form-group ic-user">
                                <input type="text" <?= $disabled; ?> name="enquiry_name" value="<?= set_value('enquiry_name'); ?>" required="required" class="form-control" placeholder="Enter name*">
                                <span class="text-danger"><?= form_error("enquiry_name") ? form_error("enquiry_name") : ''; ?></span>
                            </div>
                            <div class="form-group ic-eml">
                                <input type="email" <?= $disabled; ?> class="form-control" name="enquiry_email" placeholder="Enter email*" required="required" value="<?= set_value('enquiry_email'); ?>" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
                                <span class="text-danger"><?= form_error("enquiry_email") ? form_error("enquiry_email") : ''; ?></span>
                            </div>
                            <div class="form-group ic-pho">
                                <input type="tel" inputmode="tel" <?= $disabled; ?> class="form-control" name="enquiry_mobile" value="<?= set_value('enquiry_mobile'); ?>" placeholder="Enter mobile number *" pattern="[0-9+()\- ]{7,20}" title="Enter a phone number (7–20 characters, digits/+/()/-/space allowed)" required>
                                <span class="text-danger"><?= form_error("enquiry_mobile") ? form_error("enquiry_mobile") : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" <?= $disabled; ?> rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>
                                <span class="text-danger"><?= form_error("enquiry_message") ? form_error("enquiry_message") : ''; ?></span>
                            </div>
                            <?php if (!empty($logged_id)): ?>
                                <button type="submit" id="detail_enquiry_submit" name="enquiry_submit" class="btn btn-primary">Submit</button>
                            <?php else: ?>
                                <a href="<?= base_url('/login'); ?>"><button type="button" class="btn btn-primary" style="background:#f44336; border:none;">Please Log In First</button></a>
                            <?php endif; ?>
                        </form>

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
            <div class="lhs event-detail-modern">
                <div class="head">
                    <h1><?= $houses->house_title; ?></h1>
                </div>

                <p><?= strip_tags(html_entity_decode($houses->house_description)); ?></p>

                <div class="sec-1">
                    <h4>House Information:</h4>
                    <ul id="pg-resu">
                        <li><b>Name</b>: <?= $houses->house_title; ?></li>
                        <li><b>Bedroom(s)</b>: <?= $houses->bedrooms; ?></li>
                        <li><b>Living Area</b>: <?= $houses->living_area; ?> Sq.Ft.</li>
                        <li><b>Bathroom(s)</b>: <?= $houses->bathroom; ?></li>
                        <li><b>Property Age</b>: <?= $houses->property_age; ?></li>
                        <li><b>Garage</b>: <?= $houses->garage; ?></li>
                        <li><b>Floors</b>: <?= $houses->floors; ?></li>
                        <li><b>Can Sublet</b>: <?= ($houses->can_sublet == 1) ? 'Yes' : 'No'; ?></li>
                        <?php if ($houses->display_contact == 1) { ?>
                            <li><b>Address</b>: <?= $houses->house_address; ?></li>
                        <?php } ?>

                        <li><b>Price</b>: <?= $houses->house_price; ?></li>
                        <li><b>Date</b>: <?= date('d, M Y', strtotime($houses->created_at)); ?></li>
                    </ul>
                </div>

            </div>
            <div class="rhs ">
                <!-- Web Development Card -->
                <section class="sidebar-marketing-section">
                    <div class="marketing-card">
                        <div class="card-content">

                            <h2 class="main-heading">Do You Need a Website?</h2>
                            <p class="description">
                                Transform your business with a professional, modern website that drives results.
                            </p>

                            <div class="features-wrapper">
                                <div class="feature">
                                    <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
                                        <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Custom design tailored to your brand</span>
                                </div>
                                <div class="feature">
                                    <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
                                        <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Mobile-responsive & fast loading</span>
                                </div>
                                <div class="feature">
                                    <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" fill="rgba(255, 255, 255, 0.25)" />
                                        <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>SEO optimized for better visibility</span>
                                </div>
                            </div>

                            <a href="/contact-us" class="primary-button">Get Your Website</a>
                        </div>
                    </div>
                </section>
                <!-- Web Development Card -->

            </div>
        </div>
        <div class="pro-bot-shar">
            <h4>Share this post</h4>
            <ul>
                <li>
                    <div class="sh-pro-shar sh-pro-face"> <a target="_blank" href="<?= isset($share_urls['facebook']) ? $share_urls['facebook'] : '#' ?>">Facebook</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-twi"> <a target="_blank" href="<?= isset($share_urls['twitter']) ? $share_urls['twitter'] : '#' ?>">Twitter</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-what"> <a target="_blank" href="<?= isset($share_urls['whatsapp']) ? $share_urls['whatsapp'] : '#' ?>" data-action="share/whatsapp/share">WhatsApp</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-link"> <a target="_blank" href="<?= isset($share_urls['linkedin']) ? $share_urls['linkedin'] : '#' ?>">Linkedin</a>
                    </div>
                </li>
                <li>
                    <div style="background-color: #da271a" class="sh-pro-shar sh-pro-pin"> <a target="_blank" href="<?= isset($share_urls['pinterest']) ? $share_urls['pinterest'] : '#' ?>">Pinterest</a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="pro-rel-posts">
            <h4>Related Houses</h4>
            <div class="us-ppg-com us-ppg-blog"> 
                <ul>
                    <?php if (!empty($related_houses) && is_array($related_houses)) : ?>
                        <?php foreach ($related_houses as $rel) : ?>
                            <li>
                                <div class="pro-eve-box">
                                    <div>
                                        <img src="<?= !empty($rel->house_image) ? base_url($rel->house_image) : base_url('assets/images/no_image.png') ?>">
                                    </div>
                                    <div> <span><?= !empty($rel->created_at) ? date('M d, Y', strtotime($rel->created_at)) : '' ?></span>
                                        <h2><?= htmlentities($rel->house_title) ?></h2>
                                    </div>
                                    <a href="<?= base_url('house-details/' . $rel->house_slug) ?>" class="fclick">&nbsp;</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li>No related houses found.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<!--END-->