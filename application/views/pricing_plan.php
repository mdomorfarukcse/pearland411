<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans - Pearland</title>
    <style>
        /* ======= Root Variables ======= */
        :root {
            --primary-color: #180f5e;
            --accent-color: #7bb73f;
            --text-dark: #333;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --border-color: #e0e0e0;
        }

        /* ========================= Pricing Section Start ========================= */
        .ads-pricing-section {
            padding: 60px 0;
            margin-bottom: 120px;
            background: linear-gradient(135deg, #ffffff 0%, var(--bg-light) 100%);
        }

        .ads-pricing-section .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .ads-pricing-section .section-header h2 {
            font-size: 35px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 12px;
        }

        .ads-pricing-section .section-header .highlight {
            color: var(--accent-color);
        }

        .ads-pricing-section .section-header .subtitle {
            font-size: 16px;
            color: var(--text-light);
            max-width: 550px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .ads-pricing-section .pri {
            padding: 0px !important;
        }

        .ads-pricing-section .pri ul {
            display: flex;
            justify-content: center;
            align-items: start;
            flex-wrap: wrap;
            gap: 30px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ads-pricing-section .pri .row {
            justify-content: center;
        }

        .ads-pricing-section .pri ul li {
            flex: 1;
            max-width: 370px;
            min-width: 280px;
        }

        .ads-pricing-section .pri-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .ads-pricing-section .pri-box:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .ads-pricing-section .pri-box .c2 {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2a1a7a 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .ads-pricing-section .pri-box .c2 h4 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: white;
        }

        .ads-pricing-section .pri-box .c2 p {
            font-size: 14px;
            margin: 0;
            opacity: 0.9;
        }

        .ads-pricing-section .pri-box .c3 {
            padding: 30px 20px !important;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .ads-pricing-section .pri-box .c3 h2 {
            font-size: 42px;
            font-weight: 800;
            color: var(--accent-color);
            margin: 0 0 5px 0;
            line-height: 1;
        }

        .ads-pricing-section .pri-box .c3 p {
            font-size: 14px;
            color: var(--text-light);
            margin: 0;
            text-transform: capitalize;
        }

        .ads-pricing-section .pri-box .c4 {
            padding: 25px 20px;
        }

        .ads-pricing-section .pri-box .c4 .feature-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .ads-pricing-section .pri-box .c4 .feature-item:last-child {
            border-bottom: none;
        }

        .ads-pricing-section .pri-box .c4 .feature-item svg {
            margin-right: 12px;
            flex-shrink: 0;
        }

        .ads-pricing-section .pri-box .c4 .feature-item span {
            font-size: 14px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .ads-pricing-section .pri-box .c5 {
            padding: 20px;
            text-align: center;
        }

        .ads-pricing-section .pri ul li:nth-child(3) .pri-box .c3 {
            padding: 40px 20px 20px 20px !important;
        }

        .ads-pricing-section .pri ul li:nth-child(3) .pri-box .c2 {
            background: var(--accent-color) !important;
            color: white !important;
        }

        .ads-pricing-section .pri ul li:nth-child(3) .pri-box .c2 h4 {
            color: white !important;
        }

        .ads-pricing-section .pri-box .c5 a {
            background: var(--accent-color) !important;
            color: white !important;
            border-radius: 50px !important;
            padding: 10px 25px !important;
            font-size: 14px !important;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .ads-pricing-section .pri-box .c5 a:hover {
            background: var(--primary-color) !important;
        }

        @media (max-width: 768px) {
            .ads-pricing-section .section-header h2 {
                font-size: 30px;
            }

            .ads-pricing-section .pri ul {
                flex-direction: column;
                align-items: center;
            }

            .ads-pricing-section .pri ul li {
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="ads-pricing-section">
        <div class="section-header">
            <div class="container">
                <h2>Choose Your <span class="highlight">Advertisement</span> Plan</h2>
                <p class="subtitle">Select the perfect plan for your business needs and start growing your presence today</p>
            </div>
        </div>
        <section class="pri">
            <div class="container">
                <div class="row">
                    <div>
                        <ul>
                            <?php if (!empty($plans)): ?>
                                <?php foreach ($plans as $index => $plan): ?>
                                    <li>
                                        <div class="pri-box">
                                            <div class="c2">
                                                <h4><?= strtoupper(htmlspecialchars($plan->name)) ?></h4>
                                                <p><?= htmlspecialchars($plan->description) ?></p>
                                            </div>
                                            <div class="c3">
                                                <h2>
                                                    $<?= number_format($plan->price, 0) ?>
                                                </h2>
                                                <p>Per <?= htmlspecialchars($plan->billing_interval) ?></p>
                                            </div>
                                            <div class="c4">
                                                <!-- Listings -->
                                                <div class="feature-item">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                    </svg>
                                                    <span><?= $plan->listing_limit == -1 ? 'Unlimited' : $plan->listing_limit ?> Listings</span>
                                                </div>

                                                <!-- Products -->
                                                <?php if ($plan->product_limit > 0 || $plan->product_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->product_limit == -1 ? 'Unlimited' : $plan->product_limit ?> Products</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Events -->
                                                <?php if ($plan->event_limit > 0 || $plan->event_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->event_limit == -1 ? 'Unlimited' : $plan->event_limit ?> Events</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Classifieds -->
                                                <?php if ($plan->classified_limit > 0 || $plan->classified_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->classified_limit == -1 ? 'Unlimited' : $plan->classified_limit ?> Classifieds</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Rentals -->
                                                <?php if ($plan->rental_limit > 0 || $plan->rental_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->rental_limit == -1 ? 'Unlimited' : $plan->rental_limit ?> Rentals</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Houses -->
                                                <?php if ($plan->house_limit > 0 || $plan->house_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->house_limit == -1 ? 'Unlimited' : $plan->house_limit ?> Houses</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Blogs -->
                                                <?php if ($plan->blog_limit > 0 || $plan->blog_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->blog_limit == -1 ? 'Unlimited' : $plan->blog_limit ?> Blogs</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Videos -->
                                                <?php if ($plan->video_limit > 0 || $plan->video_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->video_limit == -1 ? 'Unlimited' : $plan->video_limit ?> Videos</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- News -->
                                                <?php if ($plan->news_limit > 0 || $plan->news_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->news_limit == -1 ? 'Unlimited' : $plan->news_limit ?> News</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Ads -->
                                                <?php if ($plan->ad_limit > 0 || $plan->ad_limit == -1): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span><?= $plan->ad_limit == -1 ? 'Unlimited' : $plan->ad_limit ?> Ads</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Featured Listing -->
                                                <?php if ($plan->featured_listing): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span>Featured Listing</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Priority Support -->
                                                <?php if ($plan->priority_support): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span>Priority Support</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Analytics Access -->
                                                <?php if ($plan->analytics_access): ?>
                                                    <div class="feature-item">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#22c55e" />
                                                        </svg>
                                                        <span>Analytics Dashboard</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="c5">
                                                <?php
                                                // Check if user is logged in and has current plan
                                                $is_logged_in = isset($this) && method_exists($this, 'session') && $this->session->userdata('logged_user');
                                                $current_user_plan_id = null;

                                                if ($is_logged_in && isset($user_subscription) && $user_subscription) {
                                                    $current_user_plan_id = $user_subscription->plan_id;
                                                }

                                                // Determine button text and URL
                                                if ($is_logged_in && $current_user_plan_id == $plan->id): ?>
                                                    <a href="<?= base_url('user/my-account') ?>" class="cta1" style="background: #28a745 !important;">View Details</a>
                                                <?php else: ?>
                                                    <a href="checkout?plan_id=<?= $plan->id ?>" class="cta1">
                                                        <?= ($is_logged_in && $current_user_plan_id) ? 'Upgrade Plan' : 'Get Started' ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback if no plans found -->
                                <li>
                                    <div class="pri-box">
                                        <div class="c2">
                                            <h4>PLANS LOADING...</h4>
                                            <p>Please wait while we load pricing plans</p>
                                        </div>
                                        <div class="c3">
                                            <h2>$0</h2>
                                            <p>Loading...</p>
                                        </div>
                                        <div class="c4">
                                            <div class="feature-item">
                                                <span>Loading plans from database...</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- ================ Pricing Section ================z -->
</body>

</html>