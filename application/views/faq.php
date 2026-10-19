    <style>
        .p411-faq-page {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #2d3748;
        }

        /* Hero Banner Section */
        .p411-faq-page .p411-faq-banner {
            background: linear-gradient(135deg, #180f5e 0%, #2d1a7a 50%, #180f5e 100%);
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .p411-faq-page .p411-faq-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1600') center/cover;
            opacity: 0.06;
        }

        .p411-faq-page .p411-faq-banner-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }

        .p411-faq-page .p411-faq-banner-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .p411-faq-page .p411-faq-banner-content .p411-faq-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            font-weight: 300;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .p411-faq-page .p411-faq-banner-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 25px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.9;
        }

        /* FAQ Section */
        .p411-faq-page .p411-faq-section {
            padding: 90px 0 180px 0;
            background: #f8f9fb;
        }

        .p411-faq-page .p411-faq-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .p411-faq-page .p411-faq-item {
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .p411-faq-page .p411-faq-item:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .p411-faq-page .p411-faq-question {
            padding: 25px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .p411-faq-page .p411-faq-question:hover {
            background: #f8f9fb;
            border-left-color: #7bb73f;
        }

        .p411-faq-page .p411-faq-question.active {
            background: #f8f9fb;
            border-left-color: #7bb73f;
        }

        .p411-faq-page .p411-faq-question-text {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .p411-faq-page .p411-faq-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #180f5e, #2d1a7a);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.95rem;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .p411-faq-page .p411-faq-question h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #180f5e;
            margin: 0;
            line-height: 1.5;
        }

        .p411-faq-page .p411-faq-icon {
            width: 22px;
            height: 22px;
            margin-left: 15px;
            transition: transform 0.3s ease;
            flex-shrink: 0;
            display: inline-block;
        }

        .p411-faq-page .p411-faq-question.active .p411-faq-icon svg {
            transform: rotate(180deg);
        }

        .p411-faq-page .p411-faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
        }

        .p411-faq-page .p411-faq-answer.show {
            max-height: 500px;
            padding: 0 30px 25px 90px;
        }

        .p411-faq-page .p411-faq-answer p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin: 0;
        }

        /* The refund & cancellation answer is the whole policy: longer than the
           500px every other answer fits in, and it has lists the theme strips
           of their bullets. */
        .p411-faq-page .p411-faq-answer.show:has(.p411-refund-policy) {
            max-height: 3000px;
        }
        .p411-faq-page .p411-faq-answer ul {
            list-style: disc;
            margin: 4px 0 12px 22px;
            padding: 0;
            color: #555;
            font-size: 1.05rem;
            line-height: 1.7;
        }
        .p411-faq-page .p411-faq-answer ul ul {
            list-style: circle;
            margin-bottom: 4px;
        }
        .p411-faq-page .p411-faq-answer li {
            margin-bottom: 6px;
        }
        .p411-faq-page .p411-faq-answer .p411-refund-policy p em {
            display: block;
            margin-top: 10px;
            font-style: normal;
            font-weight: 600;
            color: #180f5e;
        }

        .p411-faq-page .p411-faq-answer a {
            color: #7bb73f;
            font-weight: 600;
            text-decoration: none;
        }

        .p411-faq-page .p411-faq-answer a:hover {
            color: #5a9c2e;
            text-decoration: underline;
        }


        /* Responsive Styles */
        @media (max-width: 768px) {
            .p411-faq-page .p411-faq-banner {
                padding: 70px 0;
            }

            .p411-faq-page .p411-faq-banner-content h1 {
                font-size: 2.5rem;
            }

            .p411-faq-page .p411-faq-banner-content .p411-faq-subtitle {
                font-size: 1.1rem;
            }

            .p411-faq-page .p411-faq-banner-icon {
                font-size: 3rem;
            }

            .p411-faq-page .p411-faq-section {
                padding: 60px 0;
            }

            .p411-faq-page .p411-faq-question {
                padding: 20px;
            }

            .p411-faq-page .p411-faq-number {
                width: 35px;
                height: 35px;
                font-size: 0.85rem;
                margin-right: 15px;
            }

            .p411-faq-page .p411-faq-question h3 {
                font-size: 1.1rem;
            }

            .p411-faq-page .p411-faq-answer.show {
                padding: 0 20px 20px 70px;
            }

            .p411-faq-page .p411-faq-answer p {
                font-size: 1rem;
            }

            .p411-faq-page .p411-support-box {
                padding: 35px 25px;
            }

            .p411-faq-page .p411-support-box h3 {
                font-size: 1.6rem;
            }

            .p411-faq-page .p411-support-box p {
                font-size: 1rem;
            }
        }
    </style>
<?php /* fragment view — chrome comes from header.php + footer.php */ ?>
    <div class="p411-faq-page">
        <!-- Banner Section -->
        <section class="p411-faq-banner">
            <div class="container">
                <div class="p411-faq-banner-content">
                    <div class="p411-faq-banner-icon">
                        <!-- Question Circle SVG -->
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" fill="white" />
                            <path d="M12 7a3 3 0 0 1 3 3c0 2-2 2-2 4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" />
                            <circle cx="12" cy="17" r="1.2" fill="#7bb73f" />
                        </svg>
                    </div>
                    <h1>Frequently Asked Questions (FAQ)</h1>
                    <p class="p411-faq-subtitle">Find answers to the most common questions about using Pearland411 — from creating listings to exploring local services.</p>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="p411-adrow" style="margin-top:22px;">
                <?php $slot = $ad_slots['top'] ?? '';
                include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>
        </div>

        <!-- FAQ Section -->
        <section class="p411-faq-section">
            <div class="container">
                <div class="p411-faq-container">
                    <!-- FAQ Item 1 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">01</div>
                                <h3>What is Pearland411?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>Pearland411 is an online business directory and community platform designed to help people in Pearland, Texas, discover local businesses, events, deals, and services all in one place.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">02</div>
                                <h3>How can I add my business to Pearland411?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>Simply click the "Add Your Business" button on the homepage, create a free account, and fill out your business details. Once approved, your listing will be visible to everyone searching for local services.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">03</div>
                                <h3>Do I need to pay to list my business?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>No — you can start with a free listing. However, to enjoy additional features such as featured placement, unlimited listings, and higher visibility, you can upgrade to our Premium Plan.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">04</div>
                                <h3>How long does it take for my listing to be approved?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>A business you add yourself goes live as soon as you submit it, and we email you a confirmation with a link to it. If you are <strong>claiming</strong> a business that is already listed, we check that it is yours first &mdash; usually within 1&ndash;2 business days &mdash; and you can follow the claim under My Listings &rarr; <a href="<?= e_url(base_url('user/my-claims')) ?>">My claims</a>.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">05</div>
                                <h3>Can I edit or update my business information later?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>Yes, you can log in to your dashboard anytime to edit, update, or remove your listings whenever needed.</p>
                        </div>
                    </div>

                    <div class="p411-adrow" style="margin:6px 0 18px;">
                        <?php $slot = $ad_slots['inline'] ?? '';
                        $slot_creative = 'house-ad-cream';
                        include APPPATH . 'views/partials/ad_slot.php'; ?>
                    </div>

                    <!-- FAQ Item 6 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">06</div>
                                <h3>What types of listings are available on Pearland411?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>You can post business listings, classified ads, events, real estate listings, and more. The platform is designed to serve a wide range of local needs.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 7 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">07</div>
                                <h3>How can users contact listed businesses?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>Each business listing includes contact details like phone, email, address, and an "Inquiry" or "Get Quote" option that allows users to reach out directly.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 8 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">08</div>
                                <h3>Are the listings verified?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>Pearland411 reviews all submissions before publishing. However, we encourage users to verify details directly with businesses for the most accurate information.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 9 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">09</div>
                                <h3>What if I forgot my login details?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>You can reset your password easily using the "Forgot Password" option on the login page. A reset link will be sent to your registered email.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 10 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">10</div>
                                <h3>How can I contact Pearland411 support?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>You can reach our support team anytime through the <a href="<?= e_url(base_url('contact-us')) ?>">Contact Us</a> page or by emailing <a href="mailto:support@pearland411.com">support@pearland411.com</a>. We're happy to help with your queries or technical issues.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 11 -->
                    <div class="p411-faq-item">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">11</div>
                                <h3>Can I cancel my plan, and how?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <p>Yes, any time, yourself: sign in, open <a href="<?= e_url(base_url('user/my_account')) ?>">My Account</a> and press <strong>Cancel auto-renew</strong>. You are not charged again, and your plan stays active until the end of the term you have already paid for. Banners, videos and add-ons are one-time purchases that end on their own, so there is nothing to cancel. Rather not do it online? Contact support and we will do it for you.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 12 -->
                    <div class="p411-faq-item" id="faq-refunds">
                        <div class="p411-faq-question" onclick="toggleFAQ(this)">
                            <div class="p411-faq-question-text">
                                <div class="p411-faq-number">12</div>
                                <h3>What is your refund and cancellation policy?</h3>
                            </div>
                            <span class="p411-faq-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" fill="white" />
                                    <path d="M8 10l4 4 4-4" stroke="#7bb73f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="p411-faq-answer">
                            <?php // The same text as section 8 of /terms-of-use (partials/refund_policy.php). ?>
                            <?php $rp_hide_title = TRUE; include APPPATH . 'views/partials/refund_policy.php'; unset($rp_hide_title); ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <div class="container">
            <div class="p411-adrow" style="margin-bottom:22px;">
                <?php $slot = $ad_slots['bottom'] ?? '';
                include APPPATH . 'views/partials/ad_slot.php'; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const allQuestions = document.querySelectorAll('.p411-faq-question');
            const allAnswers = document.querySelectorAll('.p411-faq-answer');

            // Close all other FAQs
            allQuestions.forEach(q => {
                if (q !== element) {
                    q.classList.remove('active');
                }
            });
            allAnswers.forEach(a => {
                if (a !== answer) {
                    a.classList.remove('show');
                }
            });

            // Toggle current FAQ
            element.classList.toggle('active');
            answer.classList.toggle('show');
        }

        // /faq#refunds (linked from checkout and the pricing page) opens the
        // refund & cancellation answer instead of landing on a closed list.
        document.addEventListener('DOMContentLoaded', function () {
            if (location.hash !== '#refunds' && location.hash !== '#faq-refunds') return;
            var item = document.getElementById('faq-refunds');
            if (!item) return;
            toggleFAQ(item.querySelector('.p411-faq-question'));
            item.scrollIntoView({ block: 'start' });
        });
    </script>

<?php $cta_variant = "banner"; include APPPATH . "views/partials/web_dev_cta.php"; ?>
