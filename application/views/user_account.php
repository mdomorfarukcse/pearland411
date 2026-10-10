<?php
// Extract user information
$user_name = isset($userInfo->name) ? $userInfo->name : 'Guest User';
$user_email = isset($userInfo->email_id) ? $userInfo->email_id : 'Not provided';
$user_mobile = isset($userInfo->mobile_number) ? $userInfo->mobile_number : 'Not provided';
$user_created = isset($userInfo->user_created) ? date('F Y', strtotime($userInfo->user_created)) : 'Unknown';

// Extract detailed subscription information
$current_subscription = isset($has_subscription) ? $has_subscription : null;
$plan_name = 'Free Plan';
$plan_price = '$0';
$billing_interval = 'month';
$plan_status = 'Basic';
$plan_duration = null;
$plan_start_date = null;
$plan_end_date = null;

if ($current_subscription) {
    $plan_name = isset($current_subscription->name) ? $current_subscription->name : 'Basic Plan';
    // Use currency if provided on subscription row
    $sub_currency = isset($current_subscription->currency) && $current_subscription->currency ? $current_subscription->currency : 'USD';
    $plan_price_raw = isset($current_subscription->price) ? (float)$current_subscription->price : 0;
    // If subscription has total_amount/tax/subtotal stored, prefer that
    $plan_subtotal = isset($current_subscription->total_amount) ? (float)$current_subscription->total_amount : $plan_price_raw;
    $plan_tax = isset($current_subscription->tax) ? (float)$current_subscription->tax : round($plan_subtotal * 0.10, 2);
    $plan_total = round($plan_subtotal + $plan_tax, 2);
    $plan_price = ($sub_currency ? htmlspecialchars($sub_currency) . ' ' : '$') . number_format($plan_total, 2);
    $billing_interval = billing_interval_noun(isset($current_subscription->billing_interval) ? $current_subscription->billing_interval : '');
    $plan_status = isset($current_subscription->status) ? ucfirst($current_subscription->status) : 'Active';
    $plan_start_date = isset($current_subscription->start_date) ? date('M d, Y', strtotime($current_subscription->start_date)) : null;
    $plan_end_date = isset($current_subscription->end_date) ? date('M d, Y', strtotime($current_subscription->end_date)) : null;
    $plan_duration = isset($current_subscription->duration_days) ? $current_subscription->duration_days : null;
    $plan_duration_months = isset($current_subscription->duration_months) ? $current_subscription->duration_months : null;
    // If duration months not stored, try to compute from start_date and end_date if available
    if (!$plan_duration_months && isset($current_subscription->start_date) && isset($current_subscription->end_date)) {
        if (!$plan_duration_months) {
            // First try: extract from current subscription row if it contains start/end
            if (isset($current_subscription->start_date) && isset($current_subscription->end_date)) {
                $start = new DateTime($current_subscription->start_date);
                $end = new DateTime($current_subscription->end_date);
                $diff = $start->diff($end);
                // Use total days to compute months to avoid month-boundary quirks
                $total_days = isset($diff->days) ? (int)$diff->days : (
                    (int)$diff->y * 365 + (int)$diff->m * 30 + (int)$diff->d
                );
                $approx_months = (int) max(1, round($total_days / 30));
                $plan_duration_months = $approx_months;
                $plan_duration = $total_days; // keep days as backup for display
            }

            // Second try: if subscription lacks dates or months, attempt to read latest transaction payload
            if (!$plan_duration_months && isset($user_transactions) && is_array($user_transactions) && count($user_transactions) > 0) {
                // find latest successful transaction that belongs to this subscription
                foreach ($user_transactions as $tx) {
                    if (isset($tx->subscription_id) && $tx->subscription_id == (isset($current_subscription->id) ? $current_subscription->id : null)) {
                        if (isset($tx->response_payload) && $tx->response_payload) {
                            $payload = json_decode($tx->response_payload, true);
                            if ($payload && isset($payload['selected_duration'])) {
                                $sd = $payload['selected_duration'];
                                if (isset($sd['duration_months']) && $sd['duration_months']) {
                                    $plan_duration_months = (int)$sd['duration_months'];
                                    $plan_duration = isset($sd['duration_days']) ? (int)$sd['duration_days'] : null;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // existing behavior: compute from start/end if months not present
            if (isset($current_subscription->start_date) && isset($current_subscription->end_date)) {
                $start = new DateTime($current_subscription->start_date);
                $end = new DateTime($current_subscription->end_date);
                $diff = $start->diff($end);
                $total_days = isset($diff->days) ? (int)$diff->days : ((int)$diff->y * 365 + (int)$diff->m * 30 + (int)$diff->d);
                if (!$plan_duration) $plan_duration = $total_days;
            }
        }
    }
}

// Extract comprehensive usage information
$usage_data = [
    'listing' => isset($usage_counts['listing']) ? $usage_counts['listing'] : 0,
    'product' => isset($usage_counts['product']) ? $usage_counts['product'] : 0,
    'event' => isset($usage_counts['event']) ? $usage_counts['event'] : 0,
    'classified' => isset($usage_counts['classified']) ? $usage_counts['classified'] : 0,
    'rental' => isset($usage_counts['rental']) ? $usage_counts['rental'] : 0,
    'house' => isset($usage_counts['house']) ? $usage_counts['house'] : 0,
    'blog' => isset($usage_counts['blog']) ? $usage_counts['blog'] : 0,
    'video' => isset($usage_counts['video']) ? $usage_counts['video'] : 0,
    'news' => isset($usage_counts['news']) ? $usage_counts['news'] : 0,
    'ad' => isset($usage_counts['ad']) ? $usage_counts['ad'] : 0,
    'ad_boost' => isset($usage_counts['ad_boost']) ? $usage_counts['ad_boost'] : 0,
    'placeholder_images' => isset($usage_counts['placeholder_images']) ? $usage_counts['placeholder_images'] : 0,
];

// Extract comprehensive limit information.
// -1 and 0 both mean unlimited (Usermodel::canUserAddContent()); 0 used to
// print as a bare "0" — "Blog limit 0" beside posts the member had made.
$__lim_src = (isset($subscription_limits) && is_array($subscription_limits)) ? $subscription_limits : array();
$__lim = function ($key, $fallback) use ($__lim_src) {
    $subscription_limits = $__lim_src;
    if (!isset($subscription_limits[$key])) return $fallback;
    $v = (int) $subscription_limits[$key];
    return ($v === -1 || $v === 0) ? '∞' : $v;
};
$limits_data = [
    'listing' => $__lim('listing', 5),
    'product' => $__lim('product', 3),
    'event' => $__lim('event', 3),
    'classified' => $__lim('classified', 2),
    'rental' => $__lim('rental', 2),
    'house' => $__lim('house', 2),
    'blog' => $__lim('blog', 2),
    'video' => $__lim('video', 1),
    'news' => $__lim('news', 1),
    'ad' => $__lim('ad', 1),
    'ad_boost' => isset($subscription_limits['ad_boost']) ? ($subscription_limits['ad_boost'] == -1 ? '∞' : $subscription_limits['ad_boost']) : 0,
    'placeholder_images' => $__lim('placeholder_images', 5),
];

// Plan features for display
$plan_features = [];
if ($current_subscription) {
    $features = [];
    if ($limits_data['listing'] === '∞') $features[] = 'Unlimited Listings';
    else $features[] = $limits_data['listing'] . ' Listings';

    if ($limits_data['product'] === '∞') $features[] = 'Unlimited Products';
    else $features[] = $limits_data['product'] . ' Products';

    if ($limits_data['event'] === '∞') $features[] = 'Unlimited Events';
    else $features[] = $limits_data['event'] . ' Events';

    if ($limits_data['classified'] === '∞') $features[] = 'Unlimited Classifieds';
    else $features[] = $limits_data['classified'] . ' Classifieds';

    if ($limits_data['rental'] === '∞') $features[] = 'Unlimited Rentals';
    else $features[] = $limits_data['rental'] . ' Rentals';

    if ($limits_data['house'] === '∞') $features[] = 'Unlimited House Sales';
    else $features[] = $limits_data['house'] . ' House Sales';

    if ($limits_data['blog'] === '∞') $features[] = 'Unlimited Blogs';
    else $features[] = $limits_data['blog'] . ' Blogs';

    if ($limits_data['video'] === '∞') $features[] = 'Unlimited Videos';
    else $features[] = $limits_data['video'] . ' Videos';

    if ($limits_data['news'] === '∞') $features[] = 'Unlimited News';
    else $features[] = $limits_data['news'] . ' News';

    if ($limits_data['ad'] === '∞') $features[] = 'Unlimited Ads';
    else $features[] = $limits_data['ad'] . ' Ads';

    if ($limits_data['ad_boost'] > 0) {
        if ($limits_data['ad_boost'] === '∞') $features[] = 'Unlimited Ad Boosts';
        else $features[] = $limits_data['ad_boost'] . ' Ad Boosts';
    }

    if ($limits_data['placeholder_images'] === '∞') $features[] = 'Unlimited Placeholder Images';
    else $features[] = $limits_data['placeholder_images'] . ' Placeholder Images';

    // Add additional features based on plan
    if (isset($current_subscription->priority_listing) && $current_subscription->priority_listing) {
        $features[] = 'Priority Listings';
    }
    if (isset($current_subscription->allow_video) && $current_subscription->allow_video) {
        $features[] = 'Video Content Allowed';
    }
    if (isset($current_subscription->featured_listing) && $current_subscription->featured_listing) {
        $features[] = 'Featured Listings';
    }
    if (isset($current_subscription->priority_support) && $current_subscription->priority_support) {
        $features[] = 'Priority Support';
    }
    if (isset($current_subscription->analytics_access) && $current_subscription->analytics_access) {
        $features[] = 'Analytics Access';
    }

    $plan_features = $features;
}

// Backward compatibility
$listing_used = $usage_data['listing'];
$event_used = $usage_data['event'];
$blog_used = $usage_data['blog'];
$listing_limit = $limits_data['listing'];
$event_limit = $limits_data['event'];
$blog_limit = $limits_data['blog'];

// Get user initials for avatar
$initials = '';
if ($user_name && $user_name !== 'Guest User') {
    $name_parts = explode(' ', $user_name);
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (isset($name_parts[1])) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
} else {
    $initials = 'GU';
}

// Load user transactions from database
$user_transactions = [];
if (isset($user_transactions_data) && is_array($user_transactions_data)) {
    $user_transactions = $user_transactions_data;
} else {
    // This should be loaded from the controller, but providing fallback structure
    // In real implementation, controller should pass this data
    $user_transactions = isset($user_transactions) ? $user_transactions : [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .account-page-container {
            font-family: 'Arial', sans-serif;
            background: white;
            min-height: 100vh;
            padding: 20px;
            margin-bottom: 140px;
        }

        .account-page-container .modal-content {
            padding: 20px;
        }

        .account-page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .account-page-header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .account-page-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .account-main-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .account-sidebar {
            width: 280px;
            background: #1e68ac;
            padding: 30px 0;
        }

        .account-sidebar-nav {
            list-style: none;
        }

        .account-sidebar-nav li {
            margin-bottom: 5px;
        }

        .account-sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .account-sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .account-sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: #21d78d;
        }

        .account-sidebar-nav .nav-icon {
            margin-right: 12px;
            width: 20px;
            height: 20px;
        }

        .account-content-area {
            flex: 1;
            padding: 40px;
        }

        .account-content-section {
            display: none;
        }

        .account-content-section.active {
            display: block;
        }

        .account-section-title {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #21d78d;
        }

        .account-user-profile {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .account-profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .account-profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, blue, #21d78d);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            overflow: hidden;
        }

        .account-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .account-profile-info h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .account-profile-info p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .account-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .account-info-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .account-info-card h4 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .account-info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .account-info-item:last-child {
            border-bottom: none;
        }

        .account-info-label {
            color: #6c757d;
            font-weight: 500;
        }

        .account-info-value {
            color: #495057;
            font-weight: 600;
        }

        .account-edit-button {
            background: #21d78d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 25px;
        }

        .account-edit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 255, 0.3);
        }

        .subscription-plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .subscription-plan-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .subscription-plan-card.current-plan {
            border-color: #21d78d;
            background: linear-gradient(135deg, #f0fff4, #e6ffed);
        }

        .subscription-plan-card.current-plan::before {
            content: "Current Plan";
            position: absolute;
            top: 15px;
            right: -30px;
            background: #21d78d;
            color: white;
            padding: 5px 40px;
            font-size: 0.8rem;
            font-weight: bold;
            transform: rotate(45deg);
        }

        .subscription-plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .subscription-plan-name {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subscription-plan-price {
            color: blue;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .subscription-plan-features {
            list-style: none;
            margin-bottom: 25px;
        }

        .subscription-plan-features li {
            padding: 8px 0;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .subscription-plan-features li::before {
            content: "✓";
            color: #21d78d;
            font-weight: bold;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: -20px;
        }

        .subscription-status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 15px;
        }

        .subscription-status-active {
            background: #d4edda;
            color: #155724;
        }

        .subscription-usage-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .subscription-usage-table th {
            background: linear-gradient(45deg, blue, #1e40af);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .subscription-usage-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
        }

        .subscription-usage-table tr:hover {
            background: #f8f9fa;
        }

        .subscription-usage-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .subscription-usage-progress {
            height: 100%;
            background: linear-gradient(90deg, #21d78d, #16a085);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .account-main-wrapper {
                flex-direction: column;
            }

            .account-sidebar {
                width: 100%;
                padding: 20px 0;
            }

            .account-sidebar-nav {
                display: flex;
                overflow-x: auto;
                gap: 10px;
                padding: 0 20px;
            }

            .account-sidebar-nav li {
                margin: 0;
                white-space: nowrap;
            }

            .account-content-area {
                padding: 20px;
            }

            .account-info-grid {
                grid-template-columns: 1fr;
            }

            .subscription-plans-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="account-page-container">
        <div class="account-page-header">
            <h1>Account Management</h1>
            <p>Manage your account settings and subscription plans</p>
        </div>

        <?php if (isset($this) && method_exists($this, 'session') && $this->session->flashdata('message')): ?>
            <div style="max-width:1200px;margin:0 auto 16px auto;background:#e9f7ef;color:#155724;border:1px solid #c3e6cb;padding:12px 16px;border-radius:6px;">
                <?= $this->session->flashdata('message'); ?>
            </div>
        <?php endif; ?>

        <div class="account-main-wrapper">
            <nav class="account-sidebar">
                <ul class="account-sidebar-nav">
                    <li>
                        <a href="#" class="nav-link active" data-tab="settings">
                            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                            Account Settings
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-tab="subscription">
                            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" />
                            </svg>
                            Subscription
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-tab="transactions">
                            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM4 12a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM5 17a1 1 0 100-2h6a1 1 0 100 2H5z" />
                            </svg>
                            Transaction History
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="account-content-area">
                <!-- Account Settings Tab -->
                <section class="account-content-section active" id="settings">
                    <h2 class="account-section-title">Account Settings</h2>

                    <div class="account-user-profile">
                        <div class="account-profile-header">
                            <div class="account-profile-avatar">
                                <?php if (!empty($userInfo->profile_image)) { ?>
                                    <img src="<?= base_url($userInfo->profile_image) ?>" alt="Profile Picture" />
                                <?php } else { ?>
                                    <?= $initials ?>
                                <?php } ?>
                            </div>
                            <div class="account-profile-info">
                                <h3><?= htmlspecialchars($user_name) ?></h3>
                                <p><?= htmlspecialchars($plan_name) ?> Member</p>
                                <p>Member since <?= $user_created ?></p>
                            </div>
                        </div>
                        <button class="account-edit-button" data-toggle="modal" data-target="#editAccountModal">Edit Profile</button>
                    </div>

                    <!-- Edit Account Modal -->
                    <div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editAccountLabel">Edit Account</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="<?= base_url('user/update_account'); ?>" method="post" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <div class="row" style="display:flex;flex-wrap:wrap;gap:16px">
                                            <div style="flex:1 1 260px">
                                                <label>Full Name</label>
                                                <input type="text" name="name" value="<?= htmlspecialchars($user_name) ?>" class="form-control" required />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Email</label>
                                                <input type="email" name="email_id" value="<?= htmlspecialchars($user_email) ?>" class="form-control" required />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Phone</label>
                                                <input type="text" name="mobile_number" value="<?= htmlspecialchars($user_mobile) ?>" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Address</label>
                                                <input type="text" name="user_address" value="<?= isset($userInfo->user_address) ? htmlspecialchars($userInfo->user_address) : '' ?>" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>State</label>
                                                <input type="text" name="user_state" value="<?= isset($userInfo->user_state) ? htmlspecialchars($userInfo->user_state) : '' ?>" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Zip Code</label>
                                                <input type="text" name="user_zip_code" value="<?= isset($userInfo->user_zip_code) ? htmlspecialchars($userInfo->user_zip_code) : '' ?>" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Contact Email</label>
                                                <input type="email" name="user_contact_email" value="<?= isset($userInfo->user_contact_email) ? htmlspecialchars($userInfo->user_contact_email) : '' ?>" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Website</label>
                                                <input type="text" name="user_website" value="<?= isset($userInfo->user_website) ? htmlspecialchars($userInfo->user_website) : '' ?>" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>New Password (optional)</label>
                                                <input type="password" name="password" class="form-control" />
                                            </div>
                                            <div style="flex:1 1 260px">
                                                <label>Profile Picture</label>
                                                <input type="file" name="profile_image" accept="image/*" class="form-control" id="profileImageInput" />
                                                <?php if (!empty($userInfo->profile_image)) { ?>
                                                    <div style="margin-top:8px" id="currentProfileImage">
                                                        <img src="<?= base_url($userInfo->profile_image) ?>" alt="Profile" style="height:60px;border-radius:6px" />
                                                        <small style="display:block;color:#666;">Current profile picture</small>
                                                    </div>
                                                <?php } ?>
                                                <div id="newProfileImagePreview" style="margin-top:8px;display:none;">
                                                    <img id="previewImg" src="" alt="Preview" style="height:60px;border-radius:6px" />
                                                    <small style="display:block;color:#666;">New profile picture preview</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="account-info-grid">
                        <div class="account-info-card">
                            <h4>
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 1a6 6 0 0 0-6 6h12a6 6 0 0 0-6-6z" />
                                </svg>
                                Personal Information
                            </h4>
                            <div class="account-info-item">
                                <span class="account-info-label">Full Name</span>
                                <span class="account-info-value"><?= htmlspecialchars($user_name) ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Email</span>
                                <span class="account-info-value"><?= htmlspecialchars($user_email) ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Phone</span>
                                <span class="account-info-value"><?= htmlspecialchars($user_mobile) ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Member Since</span>
                                <span class="account-info-value"><?= $user_created ?></span>
                            </div>
                        </div>

                        <div class="account-info-card">
                            <h4>
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM8 3.5a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 3.5z" />
                                </svg>
                                Subscription Details
                            </h4>
                            <div class="account-info-item">
                                <span class="account-info-label">Current Plan</span>
                                <span class="account-info-value"><?= htmlspecialchars($plan_name) ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Plan Status</span>
                                <span class="account-info-value" style="color: <?= ($plan_status === 'Active') ? '#21d78d' : '#6c757d' ?>"><?= $plan_status ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Monthly Cost</span>
                                <span class="account-info-value"><?= $plan_price ?><?= $plan_price_raw !== 0 ? '/' . $billing_interval : '' ?></span>
                            </div>
                            <?php if ($plan_duration_months): ?>
                                <div class="account-info-item">
                                    <span class="account-info-label">Plan Duration</span>
                                    <span class="account-info-value"><?= htmlspecialchars($plan_duration_months) ?> month<?= ($plan_duration_months > 1 ? 's' : '') ?><?php if ($plan_duration && !$plan_duration_months) echo ' (' . $plan_duration . ' days)'; ?></span>
                                </div>
                            <?php elseif ($plan_duration): ?>
                                <div class="account-info-item">
                                    <span class="account-info-label">Plan Duration</span>
                                    <span class="account-info-value"><?= htmlspecialchars($plan_duration) ?> days</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($plan_end_date): ?>
                                <div class="account-info-item">
                                    <?php // "Next Billing" only if it will actually bill again. ?>
                                    <span class="account-info-label"><?= ($plan_status === 'Active' && !empty($current_subscription->auto_renew)) ? 'Next Billing' : 'Expires On' ?></span>
                                    <span class="account-info-value"><?= $plan_end_date ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="account-info-card">
                            <h4>
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
                                </svg>
                                Usage Summary
                            </h4>
                            <div class="account-info-item">
                                <span class="account-info-label">Total Content</span>
                                <span class="account-info-value"><?= array_sum($usage_data) ?> items</span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Most Used</span>
                                <span class="account-info-value">
                                    <?php
                                    $max_usage = max($usage_data);
                                    $most_used_type = array_search($max_usage, $usage_data);
                                    $type_names = [
                                        'listing' => 'Listings',
                                        'product' => 'Products',
                                        'event' => 'Events',
                                        'classified' => 'Classifieds',
                                        'rental' => 'Rentals',
                                        'house' => 'House Sales',
                                        'blog' => 'Blogs',
                                        'video' => 'Videos',
                                        'news' => 'News',
                                        'ad' => 'Ads'
                                    ];
                                    echo isset($type_names[$most_used_type]) ? $type_names[$most_used_type] : 'None';
                                    ?> (<?= $max_usage ?>)
                                </span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Available Resources</span>
                                <span class="account-info-value" style="color: #21d78d;">
                                    <?php
                                    $available_count = 0;
                                    foreach ($limits_data as $key => $limit) {
                                        if ($limit === '∞') {
                                            $available_count++;
                                        } elseif ($limit > $usage_data[$key]) {
                                            $available_count++;
                                        }
                                    }
                                    echo $available_count . ' of ' . count($limits_data);
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="account-info-card">
                            <h4>
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                </svg>
                                Account Activity
                            </h4>
                            <div class="account-info-item">
                                <span class="account-info-label">User ID</span>
                                <span class="account-info-value"><?= isset($userInfo->user_id) ? $userInfo->user_id : 'N/A' ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Member Since</span>
                                <span class="account-info-value"><?= $user_created ?></span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Content Created</span>
                                <span class="account-info-value"><?= $usage_data['listing'] + $usage_data['product'] + $usage_data['event'] ?> items</span>
                            </div>
                            <div class="account-info-item">
                                <span class="account-info-label">Account Status</span>
                                <span class="account-info-value" style="color: #21d78d;">
                                    <?php
                                    $account_status = 'Active';
                                    if (isset($userInfo->user_status)) {
                                        $account_status = ucfirst($userInfo->user_status);
                                    }
                                    echo $account_status;
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Subscription Tab -->
                <section class="account-content-section" id="subscription">
                    <h2 class="account-section-title">Subscription Management</h2>

                    <!-- Current Plan Overview -->
                    <div class="subscription-current-plan" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                            <div>
                                <h3 style="margin: 0 0 5px 0; font-size: 1.8rem;"><?= htmlspecialchars($plan_name) ?></h3>
                                <p style="margin: 0; opacity: 0.9; font-size: 1.1rem;">
                                    <?= $plan_price ?><?= $plan_price !== '$0' ? '/' . $billing_interval : '' ?>
                                    <span style="margin-left: 15px; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.9rem;">
                                        <?= $plan_status ?>
                                    </span>
                                </p>
                                <?php if ($plan_end_date): ?>
                                    <p style="margin: 10px 0 0 0; opacity: 0.8; font-size: 0.95rem;">
                                        <?= ($plan_status === 'Active' && !empty($current_subscription->auto_renew)) ? 'Renews on' : 'Expires on' ?>: <?= $plan_end_date ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <?php if ($current_subscription && count($plan_features) > 0): ?>
                                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Includes:</p>
                                    <p style="margin: 5px 0 15px 0; font-weight: 600;">
                                        <?= implode(' • ', array_slice($plan_features, 0, 3)) ?>
                                        <?php if (count($plan_features) > 3): ?>
                                            <br><small style="opacity: 0.8;">and <?= count($plan_features) - 3 ?> more features</small>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Upgrade Plan Button -->
                                <div style="margin-top: 15px;">
                                    <a href="<?= base_url('pricing-plan') ?>" style="background: #21d78d; color: white; padding: 10px 20px; border-radius: 25px; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                                        <span>🚀</span>
                                        <?= $current_subscription ? 'Upgrade Plan' : 'Choose Plan' ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Change / manage plan -->
                    <?php if (!empty($all_plans) && is_array($all_plans)): ?>
                    <div style="background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:24px; margin-bottom:30px;">
                        <h3 style="margin:0 0 6px 0; color:#2c3e50;">Change your plan</h3>
                        <p style="margin:0 0 16px 0; color:#6c757d; font-size:0.95rem;">Upgrades are prorated and take effect right away. Downgrades apply at your next renewal.</p>
                        <div id="plan-change-msg" style="display:none; padding:12px; border-radius:8px; margin-bottom:14px;"></div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
                            <?php foreach ($all_plans as $p): ?>
                                <?php $is_current = ($current_subscription && (int)$current_subscription->plan_id === (int)$p->id); ?>
                                <div style="border:1px solid <?= $is_current ? '#21d78d' : '#e9ecef' ?>; border-radius:10px; padding:16px;">
                                    <div style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($p->name) ?></div>
                                    <div style="color:#495057; margin:4px 0 12px;"><?= htmlspecialchars($p->currency ?: 'USD') ?> <?= number_format((float)$p->price, 2) ?></div>
                                    <?php if ($is_current): ?>
                                        <span style="font-size:0.85rem; color:#21a06e;">Current plan</span>
                                    <?php else: ?>
                                        <button type="button" class="plan-switch-btn" data-plan="<?= (int)$p->id ?>" style="background:#667eea; color:#fff; border:none; padding:8px 16px; border-radius:20px; cursor:pointer; font-size:0.9rem;">Switch to this</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($current_subscription && !empty($current_subscription->auto_renew)): ?>
                        <form method="post" action="<?= base_url('cancel-subscription') ?>" onsubmit="return confirm('Cancel auto-renew? Your plan stays active until it expires.');" style="margin-top:18px;">
                            <button type="submit" style="background:transparent; color:#c0392b; border:1px solid #c0392b; padding:8px 16px; border-radius:20px; cursor:pointer; font-size:0.9rem;">Cancel auto-renew</button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($helcim_configured)): ?>
                    <script type="text/javascript" src="<?= $helcimpay_js_url ?>"></script>
                    <?php endif; ?>
                    <script src="<?= base_url() ?>assets/js/pp_post.js"></script>
                    <script>
                    (function () {
                        var msg = document.getElementById('plan-change-msg');
                        function showMsg(text, ok) { if (!msg) return; msg.textContent = text; msg.style.display = 'block'; msg.style.background = ok ? '#d4edda' : '#f8d7da'; msg.style.color = ok ? '#155724' : '#721c24'; }
                        var btns = document.querySelectorAll('.plan-switch-btn');
                        Array.prototype.forEach.call(btns, function (btn) {
                            btn.addEventListener('click', function () {
                                var planId = btn.getAttribute('data-plan');
                                btn.disabled = true; var orig = btn.textContent; btn.textContent = 'Please wait…';
                                ppPost('<?= base_url('plan-change-init') ?>', { plan_id: planId }, function (res) {
                                    if (res.mode === 'downgrade' || res.mode === 'switched') { showMsg(res.message || 'Done.', true); setTimeout(function () { location.reload(); }, 1500); return; }
                                    if (res.mode === 'upgrade') {
                                        var token = res.checkoutToken;
                                        var handler = function (event) {
                                            if (!event.data || event.data.eventName !== 'helcim-pay-js-' + token) return;
                                            if (event.data.eventStatus === 'ABORTED') { window.removeEventListener('message', handler); showMsg('Upgrade cancelled.', false); btn.disabled = false; btn.textContent = orig; return; }
                                            if (event.data.eventStatus === 'SUCCESS') {
                                                window.removeEventListener('message', handler);
                                                var resp = event.data.eventMessage; if (typeof resp === 'string') { try { resp = JSON.parse(resp); } catch (e) {} }
                                                var container = (resp && resp.data) ? resp.data : resp;
                                                var txnData = (container && container.data) ? container.data : container;
                                                var hash = (container && container.hash) ? container.hash : ((resp && resp.hash) ? resp.hash : '');
                                                ppPost('<?= base_url('process-checkout') ?>',
                                                    { checkoutToken: token, transaction_json: JSON.stringify(txnData), hash: hash },
                                                    function (cr) {
                                                        if (cr.success) { location.href = cr.redirect; }
                                                        else { showMsg(cr.error || 'Upgrade could not be finalized.', false); btn.disabled = false; btn.textContent = orig; }
                                                    },
                                                    // The card was already charged by Helcim at this point.
                                                    function () {
                                                        showMsg('Your payment went through but we could not finish the upgrade. Please contact support before paying again.', false);
                                                        btn.textContent = 'Contact support';
                                                    });
                                            }
                                        };
                                        window.addEventListener('message', handler);
                                        if (typeof appendHelcimPayIframe === 'function') { appendHelcimPayIframe(token); }
                                        else { showMsg('Payment library failed to load.', false); btn.disabled = false; btn.textContent = orig; }
                                    }
                                }, function (m) { showMsg(m || 'Could not change plan.', false); btn.disabled = false; btn.textContent = orig; });
                            });
                        });
                    })();
                    </script>
                    <?php endif; ?>

                    <!-- Detailed Usage & Limits -->
                    <h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.4rem;">Usage & Limits Overview</h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px;">
                        <!-- Content Creation Limits -->
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 25px;">
                            <h4 style="margin: 0 0 20px 0; color: #495057; display: flex; align-items: center; gap: 8px;">
                                <svg width="18" height="18" fill="#28a745" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                                </svg>
                                Content Publishing
                            </h4>
                            <?php
                            $content_items = [
                                'listing' => 'Business Listings',
                                'product' => 'Products',
                                'event' => 'Events',
                                'blog' => 'Blog Posts',
                                'news' => 'News Articles'
                            ];
                            foreach ($content_items as $key => $label):
                                $percentage = 0;
                                if ($limits_data[$key] !== '∞' && $limits_data[$key] > 0) {
                                    $percentage = min(($usage_data[$key] / $limits_data[$key]) * 100, 100);
                                } elseif ($limits_data[$key] === '∞') {
                                    $percentage = min($usage_data[$key] * 2, 100);
                                }
                            ?>
                                <div style="margin-bottom: 15px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="font-weight: 500; color: #495057;"><?= $label ?></span>
                                        <span style="color: #6c757d; font-size: 0.9rem;"><?= $usage_data[$key] ?> / <?= $limits_data[$key] ?></span>
                                    </div>
                                    <div style="background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                        <div style="background: linear-gradient(90deg, #28a745, #20c997); height: 100%; width: <?= $percentage ?>%; transition: width 0.3s ease;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Marketplace & Ads -->
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 25px;">
                            <h4 style="margin: 0 0 20px 0; color: #495057; display: flex; align-items: center; gap: 8px;">
                                <svg width="18" height="18" fill="#fd7e14" viewBox="0 0 16 16">
                                    <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z" />
                                    <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0z" />
                                </svg>
                                Marketplace & Advertising
                            </h4>
                            <?php
                            $marketplace_items = [
                                'classified' => 'Classified Ads',
                                'rental' => 'Rental Listings',
                                'house' => 'House Sales',
                                'ad' => 'Banner Ads',
                                'ad_boost' => 'Ad Boosts'
                            ];
                            foreach ($marketplace_items as $key => $label):
                                $percentage = 0;
                                if ($limits_data[$key] !== '∞' && $limits_data[$key] > 0) {
                                    $percentage = min(($usage_data[$key] / $limits_data[$key]) * 100, 100);
                                } elseif ($limits_data[$key] === '∞') {
                                    $percentage = min($usage_data[$key] * 2, 100);
                                }
                            ?>
                                <div style="margin-bottom: 15px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="font-weight: 500; color: #495057;"><?= $label ?></span>
                                        <span style="color: #6c757d; font-size: 0.9rem;"><?= $usage_data[$key] ?> / <?= $limits_data[$key] ?></span>
                                    </div>
                                    <div style="background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                        <div style="background: linear-gradient(90deg, #fd7e14, #ff6b6b); height: 100%; width: <?= $percentage ?>%; transition: width 0.3s ease;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Media & Resources -->
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 25px;">
                            <h4 style="margin: 0 0 20px 0; color: #495057; display: flex; align-items: center; gap: 8px;">
                                <svg width="18" height="18" fill="#6f42c1" viewBox="0 0 16 16">
                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z" />
                                </svg>
                                Media & Resources
                            </h4>
                            <?php
                            $media_items = [
                                'video' => 'Video Content',
                                'placeholder_images' => 'Image Storage'
                            ];
                            foreach ($media_items as $key => $label):
                                $percentage = 0;
                                if ($limits_data[$key] !== '∞' && $limits_data[$key] > 0) {
                                    $percentage = min(($usage_data[$key] / $limits_data[$key]) * 100, 100);
                                } elseif ($limits_data[$key] === '∞') {
                                    $percentage = min($usage_data[$key] * 2, 100);
                                }
                            ?>
                                <div style="margin-bottom: 15px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="font-weight: 500; color: #495057;"><?= $label ?></span>
                                        <span style="color: #6c757d; font-size: 0.9rem;"><?= $usage_data[$key] ?> / <?= $limits_data[$key] ?></span>
                                    </div>
                                    <div style="background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                        <div style="background: linear-gradient(90deg, #6f42c1, #e83e8c); height: 100%; width: <?= $percentage ?>%; transition: width 0.3s ease;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Plan Features -->
                            <?php if (!empty($plan_features)): ?>
                                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                    <h5 style="margin: 0 0 15px 0; color: #495057; font-size: 0.95rem;">Plan Features</h5>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php foreach (array_slice($plan_features, 0, 8) as $feature): ?>
                                            <span style="background: #e7f3ff; color: #0c5460; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; border: 1px solid #b8daff;">
                                                <?= htmlspecialchars($feature) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($plan_features) > 8): ?>
                                            <span style="background: #f8f9fa; color: #6c757d; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; border: 1px solid #dee2e6;">
                                                +<?= count($plan_features) - 8 ?> more
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Detailed Usage Table -->
                    <div style="background: white; border: 1px solid #dee2e6; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="background: linear-gradient(90deg, #495057, #6c757d); color: white; padding: 20px;">
                            <h3 style="margin: 0; font-size: 1.2rem;">Complete Usage Breakdown</h3>
                            <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 0.9rem;">Detailed view of all your content limits and current usage</p>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Content Type</th>
                                        <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Used</th>
                                        <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Limit</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Usage Progress</th>
                                        <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $all_content_types = [
                                        'listing' => ['name' => 'Business Listings', 'icon' => '🏢', 'color' => '#28a745'],
                                        'product' => ['name' => 'Products', 'icon' => '📦', 'color' => '#007bff'],
                                        'event' => ['name' => 'Events', 'icon' => '📅', 'color' => '#ffc107'],
                                        'classified' => ['name' => 'Classified Ads', 'icon' => '📰', 'color' => '#6c757d'],
                                        'rental' => ['name' => 'Rental Listings', 'icon' => '🏠', 'color' => '#20c997'],
                                        'house' => ['name' => 'House Sales', 'icon' => '🏘️', 'color' => '#fd7e14'],
                                        'blog' => ['name' => 'Blog Posts', 'icon' => '✍️', 'color' => '#e83e8c'],
                                        'video' => ['name' => 'Video Content', 'icon' => '🎥', 'color' => '#6f42c1'],
                                        'news' => ['name' => 'News Articles', 'icon' => '📰', 'color' => '#dc3545'],
                                        'ad' => ['name' => 'Banner Ads', 'icon' => '📢', 'color' => '#17a2b8'],
                                        'ad_boost' => ['name' => 'Ad Boosts', 'icon' => '🚀', 'color' => '#795548'],
                                        'placeholder_images' => ['name' => 'Image Storage', 'icon' => '🖼️', 'color' => '#607d8b']
                                    ];

                                    foreach ($all_content_types as $key => $info):
                                        $used = $usage_data[$key];
                                        $limit = $limits_data[$key];

                                        // Calculate percentage
                                        $percentage = 0;
                                        if ($limit !== '∞' && $limit > 0) {
                                            $percentage = min(($used / $limit) * 100, 100);
                                        } elseif ($limit === '∞') {
                                            $percentage = min($used * 1.5, 100);
                                        }

                                        // Determine status
                                        $status = 'Available';
                                        $status_color = '#28a745';
                                        if ($limit !== '∞' && $limit > 0) {
                                            if ($percentage >= 100) {
                                                $status = 'Limit Reached';
                                                $status_color = '#dc3545';
                                            } elseif ($percentage >= 80) {
                                                $status = 'Nearly Full';
                                                $status_color = '#ffc107';
                                            } elseif ($percentage >= 50) {
                                                $status = 'Half Used';
                                                $status_color = '#fd7e14';
                                            }
                                        }
                                    ?>
                                        <tr style="border-bottom: 1px solid #dee2e6;">
                                            <td style="padding: 15px;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <span style="font-size: 1.2rem;"><?= $info['icon'] ?></span>
                                                    <div>
                                                        <div style="font-weight: 500; color: #495057;"><?= e($info['name']) ?></div>
                                                        <div style="font-size: 0.8rem; color: #6c757d; text-transform: capitalize;"><?= str_replace('_', ' ', $key) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="padding: 15px; text-align: center;">
                                                <span style="background: <?= $info['color'] ?>20; color: <?= $info['color'] ?>; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                                                    <?= $used ?>
                                                </span>
                                            </td>
                                            <td style="padding: 15px; text-align: center;">
                                                <span style="background: #f8f9fa; color: #495057; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                                                    <?= $limit ?>
                                                </span>
                                            </td>
                                            <td style="padding: 15px;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <div style="flex: 1; background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                                        <div style="background: linear-gradient(90deg, <?= $info['color'] ?>, <?= $info['color'] ?>80); height: 100%; width: <?= $percentage ?>%; transition: width 0.3s ease;"></div>
                                                    </div>
                                                    <span style="font-size: 0.8rem; color: #6c757d; min-width: 40px;"><?= number_format($percentage, 1) ?>%</span>
                                                </div>
                                            </td>
                                            <td style="padding: 15px; text-align: center;">
                                                <span style="background: <?= $status_color ?>20; color: <?= $status_color ?>; padding: 4px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 500;">
                                                    <?= $status ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Footer -->
                        <div style="background: #f8f9fa; padding: 20px; border-top: 1px solid #dee2e6;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <strong style="color: #495057;">Plan Summary:</strong>
                                    <span style="color: #6c757d; margin-left: 8px;">
                                        <?= htmlspecialchars($plan_name) ?> - <?= $plan_price ?><?= $plan_price !== '$0' ? '/' . $billing_interval : '' ?>
                                    </span>
                                </div>
                                <div>
                                    <?php if ($plan_status === 'Active' && $plan_end_date): ?>
                                        <span style="color: #28a745; font-weight: 500;">✓ Active until <?= $plan_end_date ?></span>
                                    <?php elseif ($plan_status !== 'Active'): ?>
                                        <span style="color: #ffc107; font-weight: 500;">⚠ Status: <?= $plan_status ?></span>
                                    <?php else: ?>
                                        <span style="color: #28a745; font-weight: 500;">✓ Plan Active</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Transaction History Tab -->
                <section class="account-content-section" id="transactions">
                    <h2 class="account-section-title">Transaction History</h2>

                    <!-- Transactions Overview -->
                    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 30px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div>
                                <h3 style="margin: 0 0 5px 0; font-size: 1.5rem;">Payment History</h3>
                                <p style="margin: 0; opacity: 0.9;">View all your subscription payments and transactions</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; font-weight: bold;">
                                        <?php
                                        // Get total transactions count
                                        $total_transactions = 0;
                                        if (isset($user_transactions) && is_array($user_transactions)) {
                                            $total_transactions = count($user_transactions);
                                        }
                                        echo $total_transactions;
                                        ?>
                                    </div>
                                    <div style="opacity: 0.9; font-size: 0.85rem;">Total Transactions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div style="background: white; border: 1px solid #dee2e6; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <div style="background: linear-gradient(90deg, #495057, #6c757d); color: white; padding: 20px;">
                            <h3 style="margin: 0; font-size: 1.2rem;">All Transactions</h3>
                            <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 0.9rem;">Complete history of your payments and subscriptions</p>
                        </div>

                        <?php if (isset($user_transactions) && !empty($user_transactions)): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                                    <thead>
                                        <tr style="background: #f8f9fa;">
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Date</th>
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Transaction ID</th>
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Plan</th>
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Amount</th>
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Status</th>
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Type</th>
                                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">Payment Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user_transactions as $transaction): ?>
                                            <tr style="border-bottom: 1px solid #e9ecef;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                                <td style="padding: 15px; color: #495057;">
                                                    <?= date('M d, Y', strtotime($transaction->created_at)) ?><br>
                                                    <small style="color: #6c757d;"><?= date('g:i A', strtotime($transaction->created_at)) ?></small>
                                                </td>
                                                <td style="padding: 15px; color: #495057; font-family: monospace;">
                                                    <?= htmlspecialchars(isset($transaction->txn_ref) ? $transaction->txn_ref : $transaction->id) ?>
                                                    <?php if (isset($transaction->gateway_transaction_id) && $transaction->gateway_transaction_id): ?>
                                                        <br><small style="color: #6c757d;">AuthNet: <?= substr($transaction->gateway_transaction_id, -8) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 15px; color: #495057;">
                                                    <strong><?= htmlspecialchars(isset($transaction->plan_name) ? $transaction->plan_name : 'Plan #' . $transaction->plan_id) ?></strong>
                                                    <?php if (isset($transaction->subscription_id) && $transaction->subscription_id): ?>
                                                        <br><small style="color: #6c757d;">Subscription</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 15px; color: #495057;">
                                                    <strong style="font-size: 1.1rem; color: #28a745;">
                                                        $<?= number_format(floatval($transaction->amount), 2) ?>
                                                    </strong>
                                                    <?php if (isset($transaction->currency)): ?>
                                                        <br><small style="color: #6c757d;"><?= strtoupper($transaction->currency) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 15px;">
                                                    <?php
                                                    $status = isset($transaction->status) ? $transaction->status : 'unknown';
                                                    $status_colors = [
                                                        'completed' => '#28a745',
                                                        'success' => '#28a745',
                                                        'paid' => '#28a745',
                                                        'pending' => '#ffc107',
                                                        'processing' => '#17a2b8',
                                                        'failed' => '#dc3545',
                                                        'cancelled' => '#6c757d',
                                                        'refunded' => '#fd7e14'
                                                    ];
                                                    $bg_color = isset($status_colors[$status]) ? $status_colors[$status] : '#6c757d';
                                                    ?>
                                                    <span style="background: <?= $bg_color ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                                        <?= ucfirst($status) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 15px; color: #495057;">
                                                    <?php
                                                    $type = isset($transaction->transaction_type) ? $transaction->transaction_type : 'payment';
                                                    $type_display = str_replace('_', ' ', ucwords($type));
                                                    echo htmlspecialchars($type_display);
                                                    ?>
                                                    <?php if (isset($transaction->subscription_id) && $transaction->subscription_id): ?>
                                                        <br><small style="color: #6c757d;">Auto-Renewal</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 15px; color: #495057;">
                                                    <?php
                                                    $method = isset($transaction->payment_method) ? $transaction->payment_method : 'credit_card';
                                                    if ($method == 'credit_card') {
                                                        echo '💳 Credit Card';
                                                        // Show card details if available
                                                        if (isset($transaction->auth_net_response_code)) {
                                                            echo '<br><small style="color: #6c757d;">Auth Code: ' . $transaction->auth_net_response_code . '</small>';
                                                        }
                                                    } else {
                                                        echo ucfirst(str_replace('_', ' ', $method));
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Transactions Summary Footer -->
                            <div style="background: #f8f9fa; padding: 20px; border-top: 1px solid #dee2e6;">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                    <div>
                                        <strong>Total Transactions: <?= count($user_transactions) ?></strong>
                                        <br><small style="color: #6c757d;">Showing all transaction history</small>
                                    </div>
                                    <div style="text-align: right;">
                                        <?php
                                        $total_amount = 0;
                                        foreach ($user_transactions as $t) {
                                            if (isset($t->status) && in_array($t->status, ['completed', 'success', 'paid'])) {
                                                $total_amount += floatval($t->amount);
                                            }
                                        }
                                        ?>
                                        <div style="font-size: 1.2rem; font-weight: bold; color: #28a745;">
                                            Total Paid: $<?= number_format($total_amount, 2) ?>
                                        </div>
                                        <small style="color: #6c757d;">Successful payments only</small>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- No Transactions Found -->
                            <div style="text-align: center; padding: 60px 20px;">
                                <div style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;">💳</div>
                                <h4 style="color: #495057; margin-bottom: 10px;">No Transactions Found</h4>
                                <p style="color: #6c757d; margin-bottom: 25px;">You haven't made any payments yet.</p>
                                <a href="<?= base_url('pricing-plan') ?>" style="background: #28a745; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                                    Browse Plans
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link');
            const contentSections = document.querySelectorAll('.account-content-section');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all nav links and content sections
                    navLinks.forEach(nav => nav.classList.remove('active'));
                    contentSections.forEach(section => section.classList.remove('active'));

                    // Add active class to clicked nav link
                    this.classList.add('active');

                    // Show corresponding content section
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Modal is triggered by data-toggle attributes; no JS needed here.

            // Profile image preview functionality
            const profileImageInput = document.getElementById('profileImageInput');
            const newImagePreview = document.getElementById('newProfileImagePreview');
            const previewImg = document.getElementById('previewImg');

            if (profileImageInput) {
                profileImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Check if it's an image
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImg.src = e.target.result;
                                newImagePreview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            alert('Please select an image file.');
                            profileImageInput.value = '';
                            newImagePreview.style.display = 'none';
                        }
                    } else {
                        newImagePreview.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>

</html>