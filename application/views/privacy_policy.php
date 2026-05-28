    <style>
        /* .privacy-policy-wrapper {
            background: linear-gradient(135deg, #180f5e 0%, #2a1a8f 100%);
            padding: 60px 0;
            min-height: 100vh;
        } */

        .privacy-policy-wrapper .privacy-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .privacy-policy-wrapper .privacy-header {
            background: linear-gradient(135deg, #180f5e 0%, #2a1a8f 100%);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .privacy-policy-wrapper .privacy-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1200&q=80') center/cover;
            opacity: 0.1;
            z-index: 0;
        }

        .privacy-policy-wrapper .privacy-header-content {
            position: relative;
            z-index: 1;
        }

        .privacy-policy-wrapper .privacy-header h1 {
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .privacy-policy-wrapper .privacy-header .last-updated {
            color: #7bb73f;
            font-size: 1rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        .privacy-policy-wrapper .privacy-content {
            padding: 50px 40px 150px 40px;
        }

        .privacy-policy-wrapper .privacy-intro {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .privacy-policy-wrapper .privacy-section {
            margin-bottom: 40px;
        }

        .privacy-policy-wrapper .privacy-section h2 {
            color: #180f5e;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #7bb73f;
            display: inline-block;
        }

        .privacy-policy-wrapper .privacy-section p {
            color: #666;
            font-size: 1rem;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .privacy-policy-wrapper .privacy-section ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 20px;
        }

        .privacy-policy-wrapper .privacy-section ul li {
            color: #666;
            font-size: 1rem;
            line-height: 1.8;
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
        }

        .privacy-policy-wrapper .privacy-section ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #7bb73f;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .privacy-policy-wrapper .privacy-highlight {
            background: #f8f9fa;
            border-left: 4px solid #7bb73f;
            padding: 20px 25px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .privacy-policy-wrapper .privacy-highlight p {
            margin-bottom: 0;
            color: #180f5e;
            font-weight: 500;
        }

        .privacy-policy-wrapper .contact-section {
            background: linear-gradient(135deg, #180f5e 0%, #2a1a8f 100%);
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            margin-top: 50px;
        }

        .privacy-policy-wrapper .contact-section h3 {
            color: #ffffff;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .privacy-policy-wrapper .contact-section p {
            color: #ffffff;
            margin-bottom: 20px;
        }

        .privacy-policy-wrapper .contact-section a {
            color: #7bb73f;
            font-weight: 600;
            text-decoration: none;
            font-size: 1.1rem;
        }

        .privacy-policy-wrapper .contact-section a:hover {
            color: #a0d15f;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .privacy-policy-wrapper {
                padding: 30px 0;
            }

            .privacy-policy-wrapper .privacy-header {
                padding: 40px 25px;
            }

            .privacy-policy-wrapper .privacy-header h1 {
                font-size: 2rem;
            }

            .privacy-policy-wrapper .privacy-content {
                padding: 30px 25px;
            }

            .privacy-policy-wrapper .privacy-section h2 {
                font-size: 1.4rem;
            }

            .privacy-policy-wrapper .contact-section {
                padding: 30px 25px;
            }
        }

        @media (max-width: 576px) {
            .privacy-policy-wrapper .privacy-header h1 {
                font-size: 1.75rem;
            }

            .privacy-policy-wrapper .privacy-section h2 {
                font-size: 1.3rem;
            }

            .privacy-policy-wrapper .privacy-intro {
                font-size: 1rem;
            }
        }
    </style>
<?php /* fragment view — chrome comes from header.php + footer.php */ ?>
    <div class="privacy-policy-wrapper">
        <div class="privacy-header">
            <div class="privacy-header-content">
                <h1>Privacy Policy</h1>
                <span class="last-updated">Last Updated: October 2025</span>
            </div>
        </div>
        <div class="container">
            <div class="privacy-container">


                <div class="privacy-content">
                    <div class="privacy-intro">
                        At Pearland411, we respect your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard the data you share with us while using our website (https://pearland411.com).
                    </div>

                    <div class="privacy-section">
                        <h2>1. Information We Collect</h2>
                        <p>We may collect the following types of information:</p>
                        <ul>
                            <li><strong>Personal Information:</strong> When you create an account, submit a listing, or contact us, we may collect details such as your name, email address, phone number, and business information.</li>
                            <li><strong>Non-Personal Information:</strong> We automatically collect technical data such as your browser type, IP address, device information, and pages visited for analytical and security purposes.</li>
                            <li><strong>Cookies:</strong> Pearland411 uses cookies to enhance your browsing experience and remember user preferences. You can disable cookies anytime through your browser settings.</li>
                        </ul>
                    </div>

                    <div class="privacy-section">
                        <h2>2. How We Use Your Information</h2>
                        <p>We use your information to:</p>
                        <ul>
                            <li>Create and manage your account or listings</li>
                            <li>Display your business information publicly on our site</li>
                            <li>Respond to your inquiries and support requests</li>
                            <li>Improve website functionality and user experience</li>
                            <li>Send updates, promotional offers, or important notices (you can unsubscribe anytime)</li>
                            <li>Ensure security and prevent fraudulent activities</li>
                        </ul>
                    </div>

                    <div class="privacy-section">
                        <h2>3. How We Protect Your Data</h2>
                        <p>Your privacy is important to us. We implement appropriate technical and organizational measures to safeguard your data from unauthorized access, disclosure, alteration, or destruction. However, no online platform can guarantee complete security — users share information at their own discretion.</p>
                        <div class="privacy-highlight">
                            <p>We take data security seriously and continuously update our security protocols to protect your information.</p>
                        </div>
                    </div>

                    <div class="privacy-section">
                        <h2>4. Sharing of Information</h2>
                        <p>We do not sell or rent personal information to third parties. We may share data only with:</p>
                        <ul>
                            <li>Service providers who assist us in operating the website (e.g., hosting, analytics)</li>
                            <li>Legal authorities, if required by law or to protect our rights</li>
                            <li>Other users, when you choose to make your listing publicly visible</li>
                        </ul>
                    </div>

                    <div class="privacy-section">
                        <h2>5. Your Rights</h2>
                        <p>You have the right to:</p>
                        <ul>
                            <li>Access and review the personal information we hold about you</li>
                            <li>Request corrections or deletion of your data</li>
                            <li>Opt out of marketing communications at any time</li>
                        </ul>
                        <p>To exercise these rights, please contact us at <strong>support@pearland411.com</strong>.</p>
                    </div>

                    <div class="privacy-section">
                        <h2>6. Third-Party Links</h2>
                        <p>Pearland411 may include links to external websites. We are not responsible for the privacy practices or content of these third-party sites. Please review their policies before sharing any personal data.</p>
                    </div>

                    <div class="privacy-section">
                        <h2>7. Data Retention</h2>
                        <p>We retain your personal data as long as your account is active or as needed to provide services. Once you delete your account or request data removal, we securely delete or anonymize your data unless required by law to retain it.</p>
                    </div>

                    <div class="privacy-section">
                        <h2>8. Updates to This Policy</h2>
                        <p>We may update this Privacy Policy periodically to reflect changes in our practices or for legal reasons. Updates will be posted on this page with a revised "Last Updated" date.</p>
                    </div>

                    <div class="contact-section">
                        <h3>Questions or Concerns?</h3>
                        <p>If you have any questions about this Privacy Policy, please contact us:</p>
                        <a href="mailto:support@pearland411.com">support@pearland411.com</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

