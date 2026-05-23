<style>
    .eve-deta-body {
        padding-top: 5px !important;
    }

    .list-rhs-form {
        border: none !important;
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
        border-radius: 24px;
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

    .publisher-info {
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 12px 0;
        font-size: 14px;
        color: #64748b;
        flex-wrap: wrap;
    }

    .author,
    .publish-date {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .author svg,
    .publish-date svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .author-name,
    .date-text {
        color: #334155;
        font-weight: 500;
    }
</style>

<!-- START -->
<section class="  eve-deta-pg" style="margin-top: 0px !important; padding-top:50px;">
    <div class="container">
        <div class="eve-deta-pg-main">
            <div class="lhs">
                <div class="img">
                    <img src="<?= base_url($news->news_image); ?>" alt="">
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
                        <form method="post" action="<?= base_url('user/add-enquiry/' . 'news_details/' . $news->news_slug); ?>" name="detail_enquiry_form" id="detail_enquiry_form">

                            <input type="hidden" class="form-control" name="listing_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="event_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="news_id" value="<?= set_value('news_id', $news->news_slug); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="blog_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="product_id" value="" placeholder="" required>
                            <input type="hidden" class="form-control" name="receiving_user_id" value="<?= set_value('receiving_user_id', $news->user_id); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="enquiry_sender_id" value="<?= set_value('enquiry_sender_id', $logged_id); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="enquiry_source" value="<?= set_value('enquiry_source', $news->news_title); ?>" placeholder="" required>
                            <input type="hidden" class="form-control" name="enquiry_type" value="<?= set_value('enquiry_type', 'news'); ?>" placeholder="" required>

                            <div class="form-group ic-user">
                                <input <?= $disabled; ?> type="text" name="enquiry_name" value="<?= set_value('enquiry_name'); ?>" required="required" class="form-control" placeholder="Enter name*">
                                <span class="text-danger"><?= form_error("enquiry_name") ? form_error("enquiry_name") : ''; ?></span>
                            </div>
                            <div class="form-group ic-eml">
                                <input <?= $disabled; ?> type="email" class="form-control" name="enquiry_email" placeholder="Enter email*" required="required" value="<?= set_value('enquiry_email'); ?>" pattern="^[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" title="Invalid email address">
                                <span class="text-danger"><?= form_error("enquiry_email") ? form_error("enquiry_email") : ''; ?></span>
                            </div>
                            <div class="form-group ic-pho">
                                <input <?= $disabled; ?> type="text" class="form-control" name="enquiry_mobile" value="<?= set_value('enquiry_mobile'); ?>" placeholder="Enter mobile number *" pattern="[0-9]{10,15}" title="Please enter a valid phone number" required>
                                <span class="text-danger"><?= form_error("enquiry_mobile") ? form_error("enquiry_mobile") : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <textarea <?= $disabled; ?> class="form-control" rows="3" name="enquiry_message" placeholder="Enter your query or message"></textarea>
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
            <div class="lhs">
                <div class="head">
                    <!-- Publisher Info with icon -->
                    <?php
                    // Fetch publisher info dynamically
                    $publisher = null;
                    if (!empty($news->user_id)) {
                        $this->db->select('name');
                        $this->db->from('user');
                        $this->db->where('user_id', $news->user_id);
                        $publisher = $this->db->get()->row();
                    }
                    $author_name = $publisher ? $publisher->name : 'Unknown';
                    $publish_date = !empty($news->created_at) ? date('d M, Y', strtotime($news->created_at)) : '';
                    ?>
                    <div class="publisher-info">
                        <div class="author">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span class="author-name"><?= htmlspecialchars($author_name); ?></span>
                        </div>
                        <div class="publish-date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span class="date-text"><?= $publish_date; ?></span>
                        </div>
                    </div>
                    <h1 class="pg_title"><?= e($news->news_title); ?></h1>
                </div>
                <p><?= strip_tags(html_entity_decode($news->news_description)); ?></p>

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
            <h4>Share this post</h4>
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
        <div class="pro-rel-posts">
            <h4>Related Posts</h4>
            <div class="us-ppg-com us-ppg-blog">
                <ul>
                    <?php if (!empty($related_news)) : ?>
                        <?php foreach ($related_news as $rel_news): ?>
                            <li>
                                <div class="pro-eve-box" style="border-radius: 10px;">
                                    <div>
                                        <img src="<?= base_url($rel_news->news_image); ?>" alt="<?= htmlspecialchars($rel_news->news_title); ?>">
                                    </div>
                                    <div>
                                        <?php $date = date('d, M Y', strtotime($rel_news->created_at)); ?>
                                        <span><?= $date ?></span>
                                        <h2><?= htmlspecialchars($rel_news->news_title); ?></h2>
                                    </div>
                                    <a href="<?= base_url('news-details/' . $rel_news->news_slug); ?>" class="fclick">&nbsp;</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>
                            <p>No related news found.</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<!--END-->