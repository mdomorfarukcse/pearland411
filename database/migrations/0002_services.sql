-- ---------------------------------------------------------------------------
-- 0002_services.sql
-- ---------------------------------------------------------------------------
-- IT / web services catalog + service-request (quote) intake.
--
-- This is the primary monetization channel for the client (see audit
-- §6.2 #2). Listed businesses on the directory are the target buyers.
--
-- Run once after 0001_auth_flows.sql:
--   mysql -u <user> -p <db> < database/migrations/0002_services.sql
--
-- Seed values at the bottom are an editable starting set modelled on
-- a typical IT/web services shop (web design, hosting, SEO, etc.).
-- Edit them in admin (/admin/services) after install.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services` (
    `service_id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(160) NOT NULL,
    `name`              VARCHAR(160) NOT NULL,
    `short_description` VARCHAR(500) NOT NULL,
    `body`              MEDIUMTEXT NULL,
    `icon`              VARCHAR(100) NULL,
    `image`             VARCHAR(255) NULL,
    `category`          VARCHAR(80)  NULL,
    `base_price`        DECIMAL(10,2) NULL,
    `price_unit`        ENUM('one_time','monthly','annual','project','hourly','custom')
                              NOT NULL DEFAULT 'custom',
    `sort_order`        INT NOT NULL DEFAULT 0,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`        DATETIME NOT NULL,
    `updated_at`        DATETIME NOT NULL,
    PRIMARY KEY (`service_id`),
    UNIQUE KEY `services_slug_uq` (`slug`),
    KEY `services_active_sort_idx` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `service_requests` (
    `request_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id`   BIGINT UNSIGNED NULL,
    `user_id`      BIGINT UNSIGNED NULL,
    `name`         VARCHAR(160) NOT NULL,
    `email`        VARCHAR(255) NOT NULL,
    `phone`        VARCHAR(40)  NULL,
    `company`      VARCHAR(160) NULL,
    `budget_range` VARCHAR(40)  NULL,
    `message`      TEXT         NOT NULL,
    `status`       ENUM('new','in_review','quoted','won','lost','spam')
                          NOT NULL DEFAULT 'new',
    `admin_notes`  TEXT         NULL,
    `ip`           VARCHAR(45)  NULL,
    `user_agent`   VARCHAR(255) NULL,
    `created_at`   DATETIME NOT NULL,
    `updated_at`   DATETIME NOT NULL,
    PRIMARY KEY (`request_id`),
    KEY `service_requests_service_idx` (`service_id`),
    KEY `service_requests_user_idx`    (`user_id`),
    KEY `service_requests_status_idx`  (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed catalog. Replace via admin to match the client's actual offerings.
INSERT INTO `services`
    (`slug`, `name`, `short_description`, `body`, `icon`, `category`,
     `base_price`, `price_unit`, `sort_order`, `is_active`,
     `created_at`, `updated_at`)
VALUES
    ('website-design-development',
     'Website Design & Development',
     'Custom-built, mobile-first websites for local businesses — built to convert visitors into customers.',
     '<p>From a simple brochure site to a full custom build, we design and develop websites that look professional, load fast, and read clearly on every device.</p><ul><li>Custom layout designed around your brand</li><li>Mobile-first responsive build</li><li>SEO-friendly structure out of the box</li><li>Connected to your Google Business Profile</li><li>Up to 10 content pages in the starter package</li></ul>',
     'web', 'Web Development', 1499.00, 'project', 10, 1, NOW(), NOW()),

    ('website-hosting',
     'Website Hosting & SSL',
     'Fast, secure hosting on US-based servers with daily backups, free SSL, and uptime monitoring.',
     '<p>Managed hosting designed for small business websites. We handle the server so you do not have to.</p><ul><li>SSD storage, US data centers</li><li>Free SSL certificate (HTTPS)</li><li>Daily off-site backups, 30-day retention</li><li>24/7 uptime monitoring</li><li>Email support included</li></ul>',
     'cloud', 'Hosting & Infrastructure', 29.00, 'monthly', 20, 1, NOW(), NOW()),

    ('website-maintenance',
     'Website Maintenance & Care Plan',
     'We keep your site updated, backed up, secure, and online — so it does not break the week you finally need it.',
     '<p>Monthly care plans for sites we host or sites we did not build. Includes plugin and core updates, security scans, malware cleanup, and a fixed monthly allowance of content edits.</p><ul><li>Monthly software / plugin updates</li><li>Security monitoring + malware cleanup</li><li>Up to 1 hour of content edits / month</li><li>Monthly performance + uptime report</li></ul>',
     'tools', 'Hosting & Infrastructure', 99.00, 'monthly', 30, 1, NOW(), NOW()),

    ('search-engine-optimization',
     'Search Engine Optimization (SEO)',
     'Local SEO that gets your business found in Pearland-area Google searches and the local 3-pack.',
     '<p>We focus on local SEO that drives real foot traffic and phone calls. Includes keyword research, on-page optimization, Google Business Profile management, citations cleanup, and monthly reporting.</p><ul><li>Local keyword research and content plan</li><li>On-page SEO + technical fixes</li><li>Google Business Profile optimization</li><li>NAP citation cleanup</li><li>Monthly ranking + traffic report</li></ul>',
     'search', 'Marketing', 499.00, 'monthly', 40, 1, NOW(), NOW()),

    ('logo-brand-identity',
     'Logo & Brand Identity',
     'A clean, professional logo + brand kit you can use everywhere — site, signage, business cards, social.',
     '<p>Includes three logo concepts, two revision rounds, and a finished brand kit (logo files in all formats, color palette, typography guide).</p><ul><li>3 initial logo concepts</li><li>2 revision rounds</li><li>Final logo in PNG, SVG, PDF, EPS</li><li>Color + typography guide</li><li>Business card design (optional add-on)</li></ul>',
     'palette', 'Design & Branding', 599.00, 'project', 50, 1, NOW(), NOW()),

    ('ecommerce-development',
     'eCommerce / Online Store',
     'WooCommerce or Shopify stores set up, designed, and connected to your payment processor — ready to sell.',
     '<p>Complete eCommerce setup including theme customization, product import for up to 50 SKUs, payment gateway integration (Stripe / Square / Authorize.Net), shipping configuration, and tax setup. Training included.</p>',
     'shopping_cart', 'Web Development', 2499.00, 'project', 60, 1, NOW(), NOW()),

    ('it-support-helpdesk',
     'Business IT Support & Helpdesk',
     'On-call IT support for small business owners — printers, email, Wi-Fi, computer issues. We answer the phone.',
     '<p>Block-of-hours or monthly retainer support for everyday business IT problems. Remote first; on-site available in the Pearland / Houston metro area.</p><ul><li>Email and phone support</li><li>Remote screen-share troubleshooting</li><li>On-site visits available</li><li>Network / Wi-Fi setup + repair</li><li>Printer + email configuration</li></ul>',
     'support_agent', 'IT Support', 125.00, 'hourly', 70, 1, NOW(), NOW()),

    ('business-email-microsoft-365',
     'Business Email & Microsoft 365 Setup',
     'Set up name@yourbusiness.com on Google Workspace or Microsoft 365 — done right, with backups and spam filtering.',
     '<p>Includes domain configuration, mailbox setup, mobile device sync, spam and phishing protection, and onboarding for up to 5 users. Ongoing user management available as an add-on.</p>',
     'mail', 'IT Support', 199.00, 'project', 80, 1, NOW(), NOW()),

    ('paid-ads-google-meta',
     'Paid Ads — Google & Meta',
     'Targeted Google Ads and Facebook/Instagram campaigns for local businesses, managed monthly.',
     '<p>Campaign strategy, ad copywriting, creative, audience targeting, and weekly optimization. You pay the ad spend directly to Google / Meta; this is the management fee.</p><ul><li>Strategy + landing-page review</li><li>Up to 3 campaigns / month</li><li>Weekly optimization</li><li>Monthly performance call</li></ul>',
     'campaign', 'Marketing', 599.00, 'monthly', 90, 1, NOW(), NOW()),

    ('social-media-management',
     'Social Media Management',
     'Posting, engagement, and growth on Facebook and Instagram — managed by us, branded for you.',
     '<p>Includes content calendar, 12 branded posts per month across Facebook + Instagram, community management (comments + messages), and monthly reporting.</p>',
     'share', 'Marketing', 449.00, 'monthly', 100, 1, NOW(), NOW());
