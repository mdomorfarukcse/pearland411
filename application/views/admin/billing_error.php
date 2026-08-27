<?php
/**
 * Shown when the billing screen for one business fails.
 *
 * ENVIRONMENT is 'production', so display_errors is off and a fatal reaches
 * the browser as a bare "HTTP ERROR 500" — no message, no file, nothing to act
 * on. This screen touches eight tables, several of which carry columns that no
 * migration in this repo ever created, so a blank 500 turned every schema gap
 * into a guessing game played one deploy at a time. This says what broke.
 *
 * Super-admin-only route, so the message is not exposed to anyone who should
 * not see it. From Admin::billing_customer(): $listing_id, $message, $where.
 */
?>
<!-- START -->
<style>
    .bxe-wrap { max-width: 820px; }
    .bxe-card { border: 1px solid #f0a79f; background: #fdecea; border-radius: 6px; padding: 20px 22px; margin: 0 0 18px; }
    .bxe-card h3 { font-size: 17px; margin: 0 0 8px; color: #96271c; }
    .bxe-msg { display: block; background: #fff; border: 1px solid #e8c7c2; border-radius: 4px;
               padding: 12px 14px; margin: 12px 0; font-family: "SFMono-Regular", Menlo, Consolas, monospace;
               font-size: 13px; line-height: 1.55; color: #7a1f16; word-break: break-word; }
    .bxe-where { color: #8a5f5a; font-size: 12.5px; }
    .bxe-next { border: 1px solid #e3e3e3; border-radius: 6px; padding: 18px 22px; background: #fff; }
    .bxe-next h4 { font-size: 15px; margin: 0 0 10px; }
    .bxe-next li { margin-bottom: 8px; line-height: 1.6; font-size: 14px; }
    .bxe-next code { background: #f4f4f6; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
</style>
<section>
    <div class="ad-com">
        <div class="ad-dash leftpadd">
            <div class="ud-cen">
                <div class="log-bor">&nbsp;</div>
                <span class="udb-inst">Customer billing</span>

                <div class="ud-cen-s2 bxe-wrap">
                    <h2>This billing page could not load</h2>

                    <div class="bxe-card">
                        <h3>What went wrong</h3>
                        <code class="bxe-msg"><?= e($message) ?></code>
                        <p class="bxe-where">In <?= e($where) ?> &middot; business #<?= (int) $listing_id ?></p>
                    </div>

                    <div class="bxe-next">
                        <h4>Most likely cause</h4>
                        <p style="font-size:14px;line-height:1.6;margin:0 0 12px;">
                            If the message mentions an <strong>unknown column</strong>, a database migration
                            has not been run. Billing needs
                            <code>0028</code>, <code>0029</code> and <code>0030</code> &mdash; see
                            <code>database/README.md</code>. Run them, then reload this page.
                        </p>
                        <h4>Otherwise</h4>
                        <ul>
                            <li>The same error, with a full stack trace, is in
                                <code>application/logs/log-<?= date('Y-m-d') ?>.php</code>.</li>
                            <li>Nothing was charged and nothing was changed &mdash; this page had not got
                                as far as writing anything.</li>
                        </ul>
                        <p style="margin:14px 0 0;">
                            <a href="<?= base_url('admin/billing') ?>" class="btn btn-secondary">&larr; Back to all businesses</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- END -->
