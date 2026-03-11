<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Pearland411</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <style>
        .p411-contact-page * {
            box-sizing: border-box;
        }

        .p411-contact-page {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .p411-contact-page .p411-banner {
            background: linear-gradient(135deg, rgba(24, 15, 94, 0.95), rgba(24, 15, 94, 0.85)), url('./assets/images/pearland-contact.jpg');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            color: #ffffff;
            text-align: center;
        }

        .p411-contact-page .p411-banner h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .p411-contact-page .p411-banner p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.7;
            color: #ffffff;
        }

        .p411-contact-page .p411-section {
            padding: 60px 0;
        }

        .p411-contact-page .p411-contact-info {
            padding: 40px 30px;
        }

        .p411-contact-page .p411-info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .p411-contact-page .p411-info-item:last-child {
            margin-bottom: 0;
        }

        .p411-contact-page .p411-info-icon-svg {
            width: 50px;
            height: 50px;
            min-width: 50px;
            background: linear-gradient(135deg, #180f5e, #2a1a8a);
            color: #7bb73f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }

        .p411-contact-page .p411-info-icon-svg svg {
            width: 24px;
            height: 24px;
        }

        .p411-contact-page .p411-info-content {
            flex: 1;
        }

        .p411-contact-page .p411-info-content h3 {
            color: #180f5e;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 0;
        }

        .p411-contact-page .p411-info-content p {
            color: #666;
            margin-bottom: 5px;
            line-height: 1.6;
            font-size: 15px;
        }

        .p411-contact-page .p411-info-content p:last-child {
            margin-bottom: 0;
        }

        .p411-contact-page .p411-form-section {
            background: #f8f9fa;
        }

        .p411-contact-page .p411-form-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .p411-contact-page .p411-form-container h2 {
            color: #180f5e;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .p411-contact-page .p411-form-container p {
            color: #666;
            margin-bottom: 30px;
        }

        .p411-contact-page .p411-form-group {
            margin-bottom: 25px;
        }

        .p411-contact-page .p411-form-group label {
            color: #180f5e;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .p411-contact-page .p411-form-group label .p411-required {
            color: #7bb73f;
        }

        .p411-contact-page .p411-form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }

        .p411-contact-page .p411-form-control:focus {
            outline: none;
            border-color: #7bb73f;
        }

        .p411-contact-page .p411-form-control.p411-textarea {
            min-height: 150px;
            resize: vertical;
        }

        .p411-contact-page .p411-submit-btn {
            background: linear-gradient(135deg, #7bb73f, #69a535);
            color: #ffffff;
            border: none;
            padding: 14px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .p411-contact-page .p411-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123, 183, 63, 0.4);
        }

        .p411-contact-page .p411-map-section {
            padding: 0;
        }

        .p411-contact-page .p411-map-container {
            width: 100%;
            height: 450px;
            border: none;
        }

        .p411-contact-page .p411-final-note {
            background: #180f5e;
            color: #ffffff;
            padding: 100px 0 200px 0;
            text-align: center;
        }

        .p411-contact-page .p411-final-note h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .p411-contact-page .p411-final-note p {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .p411-contact-page .p411-final-note .p411-accent {
            color: #7bb73f;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .p411-contact-page .p411-banner h1 {
                font-size: 2rem;
            }

            .p411-contact-page .p411-banner p {
                font-size: 1rem;
            }

            .p411-contact-page .p411-banner {
                padding: 60px 0;
            }

            .p411-contact-page .p411-form-container {
                padding: 30px 20px;
            }

            .p411-contact-page .p411-contact-info {
                padding: 30px 20px;
                margin-bottom: 30px;
            }

            .p411-contact-page .p411-info-item {
                margin-bottom: 30px;
            }

            .p411-contact-page .p411-section {
                padding: 40px 0;
            }

            .p411-contact-page .p411-map-container {
                height: 350px;
            }
        }

        @media (max-width: 576px) {
            .p411-contact-page .p411-banner h1 {
                font-size: 1.6rem;
            }

            .p411-contact-page .p411-info-icon-svg {
                width: 45px;
                height: 45px;
                min-width: 45px;
                margin-right: 15px;
            }

            .p411-contact-page .p411-info-content h3 {
                font-size: 1.1rem;
            }

            .p411-contact-page .p411-submit-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="p411-contact-page">
        <!-- Banner Section -->
        <section class="p411-banner">
            <div class="container">
                <h1>Get in Touch with Pearland411</h1>
                <p>Have a question, feedback, or need support? We'd love to hear from you! Our team is always ready to help you connect, grow, and make the most of your Pearland411 experience.</p>
            </div>
        </section>

        <!-- Contact Information & Form Section -->
        <section class="p411-section p411-form-section">
            <div class="container">
                <div class="row">
                    <!-- Left Side - Contact Information -->
                    <div class="col-lg-5">
                        <div class="p411-contact-info">
                            <div class="p411-info-item">
                                <div class="p411-info-icon-svg">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor" />
                                    </svg>
                                </div>
                                <div class="p411-info-content">
                                    <h3>Address</h3>
                                    <p><strong>Pearland411</strong></p>
                                    <p>Pearland, Texas, USA</p>
                                </div>
                            </div>

                            <div class="p411-info-item">
                                <div class="p411-info-icon-svg">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" fill="currentColor" />
                                    </svg>
                                </div>
                                <div class="p411-info-content">
                                    <h3>Phone</h3>
                                    <p><strong>281-482-2031</strong></p>
                                    <p>Call us anytime</p>
                                </div>
                            </div>

                            <div class="p411-info-item">
                                <div class="p411-info-icon-svg">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor" />
                                    </svg>
                                </div>
                                <div class="p411-info-content">
                                    <h3>Email</h3>
                                    <p><strong>sales@pearland411.com</strong></p>
                                    <p>We reply within 24 hours</p>
                                </div>
                            </div>

                            <div class="p411-info-item">
                                <div class="p411-info-icon-svg">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor" />
                                    </svg>
                                </div>
                                <div class="p411-info-content">
                                    <h3>Working Hours</h3>
                                    <p><strong>Mon - Fri:</strong> 9:00 AM – 6:00 PM</p>
                                    <p><strong>Sat:</strong> 10:00 AM – 4:00 PM</p>
                                    <p><strong>Sun:</strong> Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Contact Form -->
                    <div class="col-lg-7">
                        <div class="p411-form-container">
                            <h2>Send Us a Message</h2>
                            <p>Fill out the form below and we'll get back to you as soon as possible.</p>
                            <form id="p411ContactForm">
                                <div class="p411-form-group">
                                    <label>Full Name <span class="p411-required">*</span></label>
                                    <input type="text" class="p411-form-control" name="fullName" required>
                                </div>
                                <div class="p411-form-group">
                                    <label>Email Address <span class="p411-required">*</span></label>
                                    <input type="email" class="p411-form-control" name="email" required>
                                </div>
                                <div class="p411-form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" class="p411-form-control" name="phone">
                                </div>
                                <div class="p411-form-group">
                                    <label>Subject <span class="p411-required">*</span></label>
                                    <input type="text" class="p411-form-control" name="subject" required>
                                </div>
                                <div class="p411-form-group">
                                    <label>Message <span class="p411-required">*</span></label>
                                    <textarea class="p411-form-control p411-textarea" name="message" required></textarea>
                                </div>
                                <button type="submit" class="p411-submit-btn">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="p411-map-section">
            <iframe
                class="p411-map-container"
                width="100%"
                height="400"
                style="border:0;"
                loading="lazy"
                allowfullscreen
                src="https://maps.google.com/maps?q=Pearland%2C%20TX%2C%20USA&z=19&output=embed">
            </iframe>

        </section>

        <!-- Final Note Section -->
        <section class="p411-final-note">
            <div class="container">
                <h3>We Value Your Time and Feedback</h3>
                <p>Whether you're a business owner seeking listing support or a visitor exploring local services,</p>
                <p>the Pearland411 team is here to assist you every step of the way.</p>
                <p class="p411-accent">📩 Let's build a stronger Pearland — together!</p>
            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('p411ContactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for contacting us! We will get back to you soon.');
            this.reset();
        });
    </script>
</body>

</html>