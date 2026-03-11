<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Pearland411</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <!-- FontAwesome removed, using SVG icons instead -->
    <style>
        .p411-about-page {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #2d3748;
            line-height: 1.6;
        }

        /* Hero Banner */
        .p411-about-page .p411-hero-banner {
            background: linear-gradient(135deg, #180f5e 0%, #2d1a7a 50%, #180f5e 100%);
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .p411-about-page .p411-hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1600') center/cover;
            opacity: 0.08;
        }

        .p411-about-page .p411-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }

        .p411-about-page .p411-hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .p411-about-page .p411-hero-content .p411-subtitle {
            font-size: 1.4rem;
            opacity: 0.95;
            font-weight: 300;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Main Content Sections */
        .p411-about-page .p411-section {
            padding: 90px 0;
        }

        .p411-about-page .p411-section-header {
            text-align: center;
            margin-bottom: 70px;
        }

        .p411-about-page .p411-section-header h2 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #180f5e;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .p411-about-page .p411-section-header .p411-underline {
            width: 80px;
            height: 4px;
            background: #7bb73f;
            margin: 20px auto 25px;
        }

        .p411-about-page .p411-section-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Who We Are Section */
        .p411-about-page .p411-intro-section {
            background: #ffffff;
        }

        .p411-about-page .p411-intro-image {
            position: relative;
            height: 500px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .p411-about-page .p411-intro-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .p411-about-page .p411-intro-text {
            padding-left: 50px;
        }

        .p411-about-page .p411-intro-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #180f5e;
            margin-bottom: 25px;
        }

        .p411-about-page .p411-intro-text p {
            font-size: 1.15rem;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.9;
        }

        .p411-about-page .p411-intro-text .p411-highlight {
            background: #f8f9fa;
            padding: 25px;
            border-left: 4px solid #7bb73f;
            border-radius: 4px;
            margin-top: 30px;
        }

        /* What We Do Cards */
        .p411-about-page .p411-services-section {
            background: #f8f9fb;
        }

        .p411-about-page .p411-service-card {
            background: white;
            padding: 45px 35px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #e8ecef;
            position: relative;
            overflow: hidden;
        }

        .p411-about-page .p411-service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #7bb73f, #5a9c2e);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .p411-about-page .p411-service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(24, 15, 94, 0.12);
        }

        .p411-about-page .p411-service-card:hover::before {
            transform: scaleX(1);
        }

        .p411-about-page .p411-service-card .p411-card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #180f5e, #2d1a7a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
            color: white;
        }

        .p411-about-page .p411-service-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #180f5e;
            margin-bottom: 15px;
        }

        .p411-about-page .p411-service-card p {
            color: #666;
            font-size: 1.05rem;
            line-height: 1.7;
            margin: 0;
        }

        /* Vision & Mission Section */
        .p411-about-page .p411-vm-section {
            background: linear-gradient(135deg, #180f5e 0%, #2d1a7a 100%);
            color: white;
            position: relative;
        }

        .p411-about-page .p411-vm-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1600') center/cover;
            opacity: 0.05;
        }

        .p411-about-page .p411-vm-box {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            padding: 50px 40px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .p411-about-page .p411-vm-box h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #7bb73f;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .p411-about-page .p411-vm-box h3 i {
            margin-right: 15px;
            font-size: 1.8rem;
        }

        .p411-about-page .p411-vm-box p {
            font-size: 1.15rem;
            line-height: 1.9;
            margin-bottom: 20px;
        }

        .p411-about-page .p411-vm-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .p411-about-page .p411-vm-box ul li {
            padding: 12px 0 12px 35px;
            position: relative;
            font-size: 1.1rem;
            line-height: 1.7;
        }

        /* Why Choose Section */
        .p411-about-page .p411-benefits-section {
            background: white;
        }

        .p411-about-page .p411-benefit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .p411-about-page .p411-benefit-item {
            display: flex;
            align-items: flex-start;
            padding: 25px;
            background: #f8f9fb;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .p411-about-page .p411-benefit-item:hover {
            background: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .p411-about-page .p411-benefit-item .p411-benefit-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #7bb73f, #5a9c2e);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
            font-size: 1.3rem;
        }

        .p411-about-page .p411-benefit-item h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #180f5e;
            margin-bottom: 8px;
        }

        .p411-about-page .p411-benefit-item p {
            color: #666;
            font-size: 1.05rem;
            margin: 0;
            line-height: 1.6;
        }

        /* Business Owners Section */
        .p411-about-page .p411-business-section {
            background: linear-gradient(to right, #f8f9fb 0%, #f8f9fb 50%, white 50%, white 100%);
            padding: 90px 0;
        }

        .p411-about-page .p411-business-content {
            background: white;
            padding: 60px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .p411-about-page .p411-business-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #180f5e;
            margin-bottom: 25px;
        }

        .p411-about-page .p411-business-content p {
            font-size: 1.15rem;
            color: #555;
            line-height: 1.9;
            margin-bottom: 20px;
        }

        .p411-about-page .p411-business-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 35px;
        }

        .p411-about-page .p411-feature-point {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fb;
            border-radius: 6px;
        }

        .p411-about-page .p411-feature-point i {
            color: #7bb73f;
            font-size: 1.3rem;
            margin-right: 12px;
        }

        .p411-about-page .p411-feature-point span {
            font-size: 1.05rem;
            color: #333;
            font-weight: 500;
        }

        .p411-about-page .p411-business-image {
            height: 450px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .p411-about-page .p411-business-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* CTA Section */
        .p411-about-page .p411-cta-section {
            background: linear-gradient(135deg, #7bb73f 0%, #5a9c2e 100%);
            padding: 80px 0 180px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .p411-about-page .p411-cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: p411MoveBackground 20s linear infinite;
        }

        @keyframes p411MoveBackground {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(50px, 50px);
            }
        }

        .p411-about-page .p411-cta-content {
            position: relative;
            z-index: 1;
        }

        .p411-about-page .p411-cta-content h2 {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }

        .p411-about-page .p411-cta-content p {
            font-size: 1.3rem;
            color: white;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto 40px;
        }

        .p411-about-page .p411-cta-button {
            display: inline-flex;
            align-items: center;
            background: #180f5e;
            color: white;
            padding: 18px 45px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(24, 15, 94, 0.3);
        }

        .p411-about-page .p411-cta-button:hover {
            background: #0f0840;
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(24, 15, 94, 0.4);
            color: white;
            text-decoration: none;
        }

        .p411-about-page .p411-cta-button i {
            margin-right: 10px;
            font-size: 1.3rem;
        }

        /* Responsive Styles */
        @media (max-width: 991px) {
            .p411-about-page .p411-intro-text {
                padding-left: 15px;
                margin-top: 40px;
            }

            .p411-about-page .p411-intro-image {
                height: 400px;
            }

            .p411-about-page .p411-business-section {
                background: #f8f9fb;
            }

            .p411-about-page .p411-business-content {
                padding: 40px 30px;
            }

            .p411-about-page .p411-business-features {
                grid-template-columns: 1fr;
            }

            .p411-about-page .p411-business-image {
                height: 350px;
                margin-top: 30px;
            }
        }

        @media (max-width: 768px) {
            .p411-about-page .p411-hero-content h1 {
                font-size: 2.5rem;
            }

            .p411-about-page .p411-hero-content .p411-subtitle {
                font-size: 1.1rem;
            }

            .p411-about-page .p411-section {
                padding: 60px 0;
            }

            .p411-about-page .p411-section-header h2 {
                font-size: 2rem;
            }

            .p411-about-page .p411-section-header {
                margin-bottom: 40px;
            }

            .p411-about-page .p411-intro-text h2 {
                font-size: 2rem;
            }

            .p411-about-page .p411-service-card {
                margin-bottom: 20px;
            }

            .p411-about-page .p411-vm-box {
                margin-bottom: 30px;
            }

            .p411-about-page .p411-benefit-grid {
                grid-template-columns: 1fr;
            }

            .p411-about-page .p411-cta-content h2 {
                font-size: 2rem;
            }

            .p411-about-page .p411-cta-content p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <div class="p411-about-page">
        <!-- Hero Section -->
        <section class="p411-hero-banner">
            <div class="container">
                <div class="p411-hero-content">
                    <h1>About Pearland411</h1>
                    <p class="p411-subtitle">Your Trusted Local Business Directory and Community Hub for Pearland, Texas</p>
                </div>
            </div>
        </section>

        <!-- Who We Are Section -->
        <section class="p411-section p411-intro-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="p411-intro-image">
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200" alt="Pearland Business District">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="p411-intro-text">
                            <h2>Who We Are</h2>
                            <p>Pearland411 is your trusted local business directory and community hub for Pearland, Texas. Our mission is simple — to connect residents, visitors, and local businesses in one convenient place.</p>
                            <p>From restaurants and real estate agents to events and service providers, we make it easy to discover everything Pearland has to offer.</p>
                            <div class="p411-highlight">
                                <strong>Our Commitment:</strong> We continuously work to ensure our platform remains accurate, user-friendly, and beneficial for everyone in the Pearland community.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- What We Do Section -->
        <section class="p411-section p411-services-section">
            <div class="container">
                <div class="p411-section-header">
                    <h2>What We Do</h2>
                    <div class="p411-underline"></div>
                    <p>We provide a powerful online platform that helps businesses grow their visibility and helps the community find trusted local services with ease.</p>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="p411-service-card">
                            <div class="p411-card-icon">
                                <!-- Rocket SVG -->
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 16.5L9.586 8.914a2 2 0 012.828 0l2.672 2.672a2 2 0 010 2.828L7.5 22H2v-5.5z" />
                                    <path d="M14.828 9.172l2.672 2.672a2 2 0 010 2.828L7.5 22H2v-5.5l7.586-7.586a2 2 0 012.828 0z" />
                                    <circle cx="12" cy="12" r="2" />
                                </svg>
                            </div>
                            <h3>Powerful Platform</h3>
                            <p>A comprehensive digital ecosystem designed to boost business visibility and connect the community with quality local services efficiently.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="p411-service-card">
                            <div class="p411-card-icon">
                                <!-- Shield SVG -->
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2l7 4v6c0 5.25-3.5 10-7 10s-7-4.75-7-10V6l7-4z" />
                                </svg>
                            </div>
                            <h3>Verified Listings</h3>
                            <p>Browse verified business listings, explore the latest news, discover local deals, and stay updated with upcoming events — all in one place.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="p411-service-card">
                            <div class="p411-card-icon">
                                <!-- Heart SVG -->
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                </svg>
                            </div>
                            <h3>Community First</h3>
                            <p>Our dedicated team ensures the platform remains accurate, user-friendly, and beneficial for everyone in Pearland.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vision & Mission Section -->
        <section class="p411-section p411-vm-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="p411-vm-box">
                            <h3>
                                <!-- Eye SVG -->
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 15px;vertical-align:middle;">
                                    <ellipse cx="12" cy="12" rx="10" ry="6" fill="white" />
                                    <circle cx="12" cy="12" r="4" fill="#7bb73f" />
                                    <circle cx="12" cy="12" r="2" fill="white" />
                                </svg>
                                Our Vision
                            </h3>
                            <p>To become Pearland's most reliable and community-driven online platform, where every local business gets recognized and every resident can access the services they need effortlessly.</p>
                            <p>We aim to empower local entrepreneurs and make Pearland's local economy thrive through strategic digital exposure and community engagement.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="p411-vm-box">
                            <h3>
                                <!-- Target SVG -->
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 15px;vertical-align:middle;">
                                    <circle cx="12" cy="12" r="10" fill="white" />
                                    <circle cx="12" cy="12" r="6" fill="#7bb73f" />
                                    <circle cx="12" cy="12" r="2" fill="white" />
                                </svg>
                                Our Mission
                            </h3>
                            <ul>
                                <li style="list-style:none;display:flex;align-items:flex-start;margin-bottom:10px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="margin-right:10px;min-width:18px;" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="3" fill="white" />
                                        <rect x="6" y="6" width="12" height="12" rx="2" fill="#7bb73f" />
                                    </svg>
                                    <span>Help businesses grow through local listings and targeted promotions</span>
                                </li>
                                <li style="list-style:none;display:flex;align-items:flex-start;margin-bottom:10px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="margin-right:10px;min-width:18px;" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="3" fill="white" />
                                        <rect x="6" y="6" width="12" height="12" rx="2" fill="#7bb73f" />
                                    </svg>
                                    <span>Offer Pearland residents a one-stop directory for all their needs</span>
                                </li>
                                <li style="list-style:none;display:flex;align-items:flex-start;margin-bottom:10px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="margin-right:10px;min-width:18px;" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="3" fill="white" />
                                        <rect x="6" y="6" width="12" height="12" rx="2" fill="#7bb73f" />
                                    </svg>
                                    <span>Support local events and initiatives to strengthen community connection</span>
                                </li>
                                <li style="list-style:none;display:flex;align-items:flex-start;margin-bottom:10px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="margin-right:10px;min-width:18px;" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="3" fill="white" />
                                        <rect x="6" y="6" width="12" height="12" rx="2" fill="#7bb73f" />
                                    </svg>
                                    <span>Provide trusted and verified information for users and businesses alike</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="p411-section p411-benefits-section">
            <div class="container">
                <div class="p411-section-header">
                    <h2>Why Choose Pearland411</h2>
                    <div class="p411-underline"></div>
                    <p>Discover what makes us the preferred choice for Pearland's local business community</p>
                </div>
                <div class="p411-benefit-grid">
                    <div class="p411-benefit-item">
                        <div class="p411-benefit-icon">
                            <!-- List SVG -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <rect x="4" y="6" width="16" height="2" />
                                <rect x="4" y="11" width="16" height="2" />
                                <rect x="4" y="16" width="16" height="2" />
                            </svg>
                        </div>
                        <div>
                            <h4>Comprehensive Listings</h4>
                            <p>Explore thousands of verified businesses across multiple categories.</p>
                        </div>
                    </div>
                    <div class="p411-benefit-item">
                        <div class="p411-benefit-icon">
                            <!-- Map Marker SVG -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C7.03 2 3 6.03 3 11c0 5.25 7.5 11 9 11s9-5.75 9-11c0-4.97-4.03-9-9-9zm0 13.5c-2.48 0-4.5-2.02-4.5-4.5s2.02-4.5 4.5-4.5 4.5 2.02 4.5 4.5-2.02 4.5-4.5 4.5z" />
                            </svg>
                        </div>
                        <div>
                            <h4>Local Focus</h4>
                            <p>Everything is centered around the Pearland community.</p>
                        </div>
                    </div>
                    <div class="p411-benefit-item">
                        <div class="p411-benefit-icon">
                            <!-- Mouse Pointer SVG -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 2l17 7-7 2-2 7-7-17z" />
                            </svg>
                        </div>
                        <div>
                            <h4>Easy to Use</h4>
                            <p>Simple navigation and search tools for quick access.</p>
                        </div>
                    </div>
                    <div class="p411-benefit-item">
                        <div class="p411-benefit-icon">
                            <!-- Users SVG -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="4" />
                                <circle cx="16" cy="8" r="4" />
                                <ellipse cx="12" cy="18" rx="10" ry="4" />
                            </svg>
                        </div>
                        <div>
                            <h4>Community Driven</h4>
                            <p>A platform built to support local people and businesses.</p>
                        </div>
                    </div>
                    <div class="p411-benefit-item">
                        <div class="p411-benefit-icon">
                            <!-- Tags SVG -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 10l10 10 10-10V2H2v8z" />
                                <circle cx="7" cy="7" r="2" />
                            </svg>
                        </div>
                        <div>
                            <h4>Free & Premium Options</h4>
                            <p>Start with a free listing or upgrade for maximum visibility.</p>
                        </div>
                    </div>
                    <div class="p411-benefit-item">
                        <div class="p411-benefit-icon">
                            <!-- Certificate SVG -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="8" />
                                <path d="M12 8v4l3 3" />
                            </svg>
                        </div>
                        <div>
                            <h4>Verified Information</h4>
                            <p>Trust in accurate, up-to-date business information.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- For Business Owners Section -->
        <section class="p411-business-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4">
                        <div class="p411-business-content">
                            <h2>For Business Owners</h2>
                            <p>Pearland411 is more than just a directory — it's a powerful marketing tool for your business. Register your account, add your business, and reach thousands of potential local customers.</p>
                            <p>Upgrade to our Premium Plan for exclusive features that put your business front and center.</p>
                            <div class="p411-business-features">
                                <div class="p411-feature-point">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 12px;vertical-align:middle;">
                                        <circle cx="12" cy="12" r="10" fill="white" />
                                        <path d="M8 12l2 2 4-4" stroke="#7bb73f" stroke-width="2" fill="none" />
                                    </svg>
                                    <span>Priority Placement</span>
                                </div>
                                <div class="p411-feature-point">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 12px;vertical-align:middle;">
                                        <circle cx="12" cy="12" r="10" fill="white" />
                                        <path d="M8 12l2 2 4-4" stroke="#7bb73f" stroke-width="2" fill="none" />
                                    </svg>
                                    <span>Unlimited Listings</span>
                                </div>
                                <div class="p411-feature-point">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 12px;vertical-align:middle;">
                                        <circle cx="12" cy="12" r="10" fill="white" />
                                        <path d="M8 12l2 2 4-4" stroke="#7bb73f" stroke-width="2" fill="none" />
                                    </svg>
                                    <span>Featured Visibility</span>
                                </div>
                                <div class="p411-feature-point">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 12px;vertical-align:middle;">
                                        <circle cx="12" cy="12" r="10" fill="white" />
                                        <path d="M8 12l2 2 4-4" stroke="#7bb73f" stroke-width="2" fill="none" />
                                    </svg>
                                    <span>Analytics Dashboard</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="p411-business-image">
                            <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1200" alt="Business Team Meeting">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="p411-cta-section">
            <div class="container">
                <div class="p411-cta-content">
                    <h2>Join the Community</h2>
                    <p>Whether you're a local resident, a small business owner, or a visitor exploring Pearland — Pearland411 welcomes you! Together, let's make Pearland stronger, more connected, and digitally empowered.</p>
                    <a href="/contact-us" class="p411-cta-button">
                        <!-- Map Marker SVG -->
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C7.03 2 3 6.03 3 11c0 5.25 7.5 11 9 11s9-5.75 9-11c0-4.97-4.03-9-9-9zm0 13.5c-2.48 0-4.5-2.02-4.5-4.5s2.02-4.5 4.5-4.5 4.5 2.02 4.5 4.5-2.02 4.5-4.5 4.5z" fill="#fff" />
                        </svg>
                        Discover. Connect. Grow
                    </a>
                </div>
            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>

</html>