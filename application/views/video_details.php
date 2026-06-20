<style>
    .eve-deta-body {
        padding-top: 5px !important;
    }

    .list-rhs-form {
        border: none !important;
    }

    /* Guard the YouTube iframe + share buttons from being covered by any
       sibling overlay (sidebar marketing card, ad_slot, .fclick that
       might be escaping its container, etc). Without these the
       clicks on the video player and the social-share row were being
       intercepted and redirected to /post-your-ads on some setups. */
    .eve-deta-pg .eve-deta-pg-main .lhs,
    .eve-deta-pg .eve-deta-pg-main .rhs {
        position: relative;
        z-index: 6;
    }

    .eve-deta-pg .eve-deta-pg-main .lhs iframe {
        position: relative;
        z-index: 7;
        pointer-events: auto;
        border: 0;
        border-radius: 8px;
    }

    .eve-deta-body .pro-bot-shar {
        position: relative;
        z-index: 6;
    }

    .eve-deta-body .pro-bot-shar a {
        position: relative;
        z-index: 7;
    }

    .eve-deta-body .eve-deta-body-main .lhs {
        border-right: none !important;
    }

    .eve-deta-pg .lhs .img:before {
        display: none !important;
    }

    /* Sidebar Web Development Section */
    .sidebar-marketing-section {
        width: 100%;
        margin-bottom: 30px;
    }

    .sidebar-marketing-section .marketing-card {
        background: linear-gradient(145deg, #2563eb 0%, #1e40af 100%);
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

    .sidebar-marketing-section .marketing-card .card-content .icon-container {
        width: 64px;
        height: 64px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.25);
    }

    .sidebar-marketing-section .marketing-card .card-content .icon-container svg {
        width: 36px;
        height: 36px;
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
        color: #2563eb;
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
</style>

<!-- START -->

<section class=" eve-deta-pg" style="margin-top: 0px !important; padding-top:50px;">

    <div class="container">

        <div class="eve-deta-pg-main">

            <div class="lhs">
                <iframe fullscreen width="100%" height="400px" src="https://www.youtube.com/embed/<?= $video->video_code; ?>?autoplay=0&mute=1"></iframe>
            </div>

            <div class="rhs">

                <div class="list-rhs-form pglist-bg pglist-p-com">

                    <div class="quote-pop">

                        <h3>Get Quote</h3>

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
                        <form method="post" action="<?= base_url('user/add-enquiry/video_details/' . $video->video_slug); ?>" name="event_detail_enquiry_form" id="event_detail_enquiry_form">

                            <input type="hidden" class="form-control" name="listing_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="event_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="blog_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="product_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="news_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="classifieds_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="rental_id" value="" placeholder="">
                            <input type="hidden" class="form-control" name="video_id" value="<?= isset($video->video_slug) ? $video->video_slug : ''; ?>" placeholder="">

                            <input type="hidden" class="form-control" name="receiving_user_id" value="<?= isset($video->user_id) ? $video->user_id : ''; ?>" placeholder="">
                            <input type="hidden" class="form-control" name="enquiry_sender_id" value="<?= isset($logged_id) ? $logged_id : ''; ?>" placeholder="">
                            <input type="hidden" class="form-control" name="enquiry_source" value="<?= isset($video->video_title) ? $video->video_title : 'Website'; ?>" placeholder="">
                            <input type="hidden" class="form-control" name="enquiry_type" value="video" placeholder="">

                            <div class="form-group ic-user">

                                <input <?= $disabled; ?> type="text" name="enquiry_name" value="" required="required" class="form-control" placeholder="Enter name*">

                            </div>

                            <div class="form-group ic-eml">

                                <input <?= $disabled; ?> type="email" class="form-control" placeholder="Enter email*" required="required" value="" name="enquiry_email" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">

                            </div>

                            <div class="form-group ic-pho">

                                <input <?= $disabled; ?> type="text" class="form-control" value="" name="enquiry_mobile" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number" required>

                            </div>

                            <div class="form-group">

                                <textarea <?= $disabled; ?> class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>

                            </div>

                            <input type="hidden" id="source">

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

    </div>

</section>

<!--END-->

<!-- START -->

<section class=" eve-deta-body">

    <div class="container">

        <div class="eve-deta-body-main">

            <div class="lhs">
                <div class="head">
                    <h1 class="pg_title"><?= $video->video_title; ?></h1>
                </div>
                <!-- <?= preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', strip_tags($video->video_description)); ?> -->
                <p><?= strip_tags(html_entity_decode($video->video_description)); ?></p>

            </div>

            <div class="rhs">


                <!-- Web Development Card -->
                <section class="sidebar-marketing-section">
                    <div class="marketing-card">
                        <div class="card-content">
                            <div class="icon-container">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="3" width="20" height="14" rx="2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M8 21h8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12 17v4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M7 7h10M7 11h6" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                                </svg>
                            </div>

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
            <h4>Share this Video</h4>
            <ul>
                <li>
                    <div class="sh-pro-shar sh-pro-face">
                        <a target="_blank" href="<?= $share_urls['facebook']; ?>">Facebook</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-twi">
                        <a target="_blank" href="<?= $share_urls['twitter']; ?>">Twitter</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-what">
                        <a target="_blank" href="<?= $share_urls['whatsapp']; ?>">WhatsApp</a>
                    </div>
                </li>
                <li>
                    <div class="sh-pro-shar sh-pro-link">
                        <a target="_blank" href="<?= $share_urls['linkedin']; ?>">Linkedin</a>
                    </div>
                </li>
                <li>
                    <div style="background-color: #da271a" class="sh-pro-shar sh-pro-pin">
                        <a target="_blank" href="<?= $share_urls['pinterest']; ?>">Pinterest</a>
                    </div>
                </li>
            </ul>

        </div>

        <div class="pro-rel-events">
            <h4>Related Videos</h4>
            <div class="event-body">
                <div class="us-ppg-com">
                    <ul>
                        <?php if (!empty($related_videos)) : ?>
                            <?php foreach ($related_videos as $rel_video): ?>
                                <li>
                                    <div class="pro-eve-box" style="border-radius: 10px;">
                                        <div>
                                            <?php
                                            $image_path = !empty($rel_video->video_image) && file_exists(FCPATH . $rel_video->video_image)
                                                ? base_url($rel_video->video_image)
                                                : base_url('assets/images/no_image.png');
                                            ?>

                                            <img
                                                src="<?= $image_path; ?>"
                                                alt="<?= htmlspecialchars($rel_video->video_title); ?>">
                                        </div>
                                        <div>
                                            <?php $date = date('d <b>M</b>', strtotime($rel_video->created_at)); ?>
                                            <span><?= $date ?></span>
                                            <h2><?= htmlspecialchars($rel_video->video_title); ?></h2>
                                            <p><?= isset($rel_video->video_location) ? htmlspecialchars($rel_video->video_location) : '' ?></p>
                                        </div>
                                        <a href="<?= base_url('user/video-details/' . $rel_video->video_slug); ?>" class="fclick">&nbsp;</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>
                                <p>No related videos found.</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</section>

<!--END-->