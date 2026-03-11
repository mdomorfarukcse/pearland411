<?php
// This view expects $success_data array from controller.
// Shape: $success_data = [
//   'subscription_id' => (int),
//   'transaction_id'  => (string),
//   'plan_name' => (string),
//   'amount' => (float),
//   'billing_info' => (string)
// ];   

// Extract data from controller
$subscription = isset($success_data) ? $success_data : [];

$planName       = isset($subscription['plan_name']) ? $subscription['plan_name'] : 'Selected Plan';
$planPrice      = isset($subscription['total']) ? (float)$subscription['total'] : (isset($subscription['amount']) ? (float)$subscription['amount'] : 0.00);
$planCurrency   = isset($subscription['currency']) && $subscription['currency'] ? $subscription['currency'] : 'USD'; // Use currency from controller if provided
$txnId          = isset($subscription['transaction_id']) ? $subscription['transaction_id'] : '—';
$subId          = isset($subscription['subscription_id']) ? $subscription['subscription_id'] : '—';
$billingInfo    = isset($subscription['billing_info']) ? $subscription['billing_info'] : '';
$currentDate    = isset($subscription['start_date']) ? $subscription['start_date'] : date('Y-m-d H:i:s');
$nextBilling    = isset($subscription['next_billing_date']) ? $subscription['next_billing_date'] : date('Y-m-d H:i:s', strtotime('+1 month'));

// Duration info
$durationMonths = isset($subscription['duration_months']) ? $subscription['duration_months'] : null;
$durationDays = isset($subscription['duration_days']) ? $subscription['duration_days'] : null;
?>

<style>
    .success-wrap {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f8fa;
        padding: 40px 16px;
        margin-bottom: 100px;
    }

    .success-card {
        max-width: 760px;
        width: 100%;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .06);
        overflow: hidden
    }

    .success-head {
        padding: 28px 28px 12px;
        border-bottom: 1px solid #f1f3f5;
        text-align: center
    }

    .success-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, #e8f5e9 0%, #e6f4ea 100%);
        color: #1f7a3a;
        font-weight: 700
    }

    .success-badge svg {
        width: 22px;
        height: 22px
    }

    .success-title {
        margin: 14px 0 6px;
        font-size: 26px;
        font-weight: 800;
        color: #0f172a
    }

    .success-sub {
        color: #475569;
        font-size: 14px
    }

    .success-body {
        padding: 24px;
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 18px
    }

    .panel {
        border: 1px solid #eef0f3;
        border-radius: 12px;
        padding: 18px
    }

    .panel h3 {
        margin: 0 0 12px;
        font-size: 16px;
        color: #111827
    }

    .kv {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        padding: 10px 0;
        border-bottom: 1px dashed #eef0f3;
        font-size: 14px
    }

    .kv:last-child {
        border-bottom: 0
    }

    .kv .k {
        color: #64748b
    }

    .kv .v {
        font-weight: 600;
        color: #0f172a
    }

    .total {
        font-size: 18px
    }

    .actions {
        padding: 0 24px 28px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center
    }

    .btn {
        appearance: none;
        border: 0;
        border-radius: 10px;
        padding: 12px 18px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px
    }

    .btn-primary {
        background: #7bb73f;
        color: #fff
    }

    .btn-primary:hover {
        background: #6aa434
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #0f172a
    }

    .btn-secondary:hover {
        background: #e2e8f0
    }

    @media (max-width: 820px) {
        .success-body {
            grid-template-columns: 1fr
        }
    }
</style>

<div class="success-wrap">
    <div class="success-card">
        <div class="success-head">
            <div class="success-badge">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" fill="#22c55e" opacity=".15" />
                    <path d="M8 12.5l2.5 2.5L16.5 9" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Payment Successful</span>
            </div>
            <h1 class="success-title">Your subscription is active</h1>
            <p class="success-sub">Thank you! We’ve emailed a receipt. You can review details below.</p>
        </div>

        <div class="success-body">
            <div class="panel">
                <h3>Order Details</h3>
                <div class="kv"><span class="k">Plan</span><span class="v"><?php echo htmlspecialchars($planName); ?></span></div>
                <?php if ($durationMonths || $durationDays): ?>
                    <div class="kv"><span class="k">Duration</span><span class="v"><?php if ($durationMonths) {
                                                                                        echo $durationMonths . ' month' . ($durationMonths > 1 ? 's' : '');
                                                                                    }
                                                                                    if ($durationDays && !$durationMonths) {
                                                                                        echo $durationDays . ' days';
                                                                                    }
                                                                                    if ($durationDays && $durationMonths) {
                                                                                        echo " ({$durationDays} days)";
                                                                                    } ?></span></div>
                <?php endif; ?>
                <div class="kv"><span class="k">Amount</span><span class="v"><?php echo htmlspecialchars($planCurrency); ?> <?php echo number_format(isset($subscription['subtotal']) ? (float)$subscription['subtotal'] : $planPrice, 2); ?></span></div>
                <?php if (isset($subscription['tax'])): ?>
                    <div class="kv"><span class="k">Tax</span><span class="v"><?php echo htmlspecialchars($planCurrency); ?> <?php echo number_format((float)$subscription['tax'], 2); ?></span></div>
                <?php endif; ?>
                <div class="kv"><span class="k">Customer</span><span class="v"><?php echo htmlspecialchars($billingInfo); ?></span></div>
                <div class="kv"><span class="k">Start date</span><span class="v"><?php echo date('M d, Y H:i', strtotime($currentDate)); ?></span></div>
                <div class="kv"><span class="k">Next billing</span><span class="v"><?php echo date('M d, Y H:i', strtotime($nextBilling)); ?></span></div>
            </div>
            <div class="panel">
                <h3>References</h3>
                <div class="kv"><span class="k">Transaction ID</span><span class="v"><?php echo htmlspecialchars($txnId); ?></span></div>
                <div class="kv"><span class="k">Subscription ID</span><span class="v">#<?php echo htmlspecialchars($subId); ?></span></div>
                <div class="kv total"><span class="k">Total Paid</span><span class="v"><?php echo htmlspecialchars($planCurrency); ?> <?php echo number_format($planPrice, 2); ?></span></div>
            </div>
        </div>

        <div class="actions">
            <a class="btn btn-primary" href="<?php echo base_url('user/my_account'); ?>">Go to Dashboard</a>

        </div>
    </div>
</div>

<script>
    // Optional: redirect to dashboard after 30s with confirmation
    setTimeout(function() {
        if (confirm('Go to your dashboard now?')) {
            window.location.href = '<?php echo base_url('user/dashboard'); ?>';
        }
    }, 30000);
</script>