<?php
/**
 * /admin/email-diagnostics — is the site's outgoing mail working?
 *
 * From Admin::email_diagnostics(). Built after the September 2026 outage, in
 * which signup verification and password-reset mail stopped reaching anyone
 * and nothing in the product could say why. The page answers, in order: what
 * the mailer is set to do, which of those settings are known to break
 * delivery, whether a test message gets through, and what the last few
 * attempts actually did.
 *
 * Styling is local to the page and mirrors admin_setting.php rather than
 * touching the shared 345 KB style.css.
 */
$flash_ok  = $this->session->flashdata('message');
$flash_err = $this->session->flashdata('error');

$cfg      = isset($mail_config) ? $mail_config : array();
$issues   = isset($mail_issues) ? $mail_issues : array();
$recent   = isset($recent) ? $recent : array();
$stats    = isset($stats) ? $stats : array('sent' => 0, 'failed' => 0);
$blockers = 0;
foreach ($issues as $i) {
	if ($i['level'] === 'error') $blockers++;
}
?>
<style>
	.maild-page { padding: 28px 32px 80px; max-width: 1040px; margin: 0 auto; }
	.maild-page h1 { font-family: 'Poppins', sans-serif; color: #28282B; margin: 0 0 4px; }
	.maild-page .lede { color: #6b7280; margin-bottom: 24px; }
	.maild-card {
		background: #fff; border: 1px solid #e6e8ee; border-radius: 14px;
		padding: 24px 26px; margin-bottom: 22px;
		box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
	}
	.maild-card h2 {
		font-family: 'Poppins', sans-serif; font-size: 1.05rem; font-weight: 700;
		color: #909D3E; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 4px;
	}
	.maild-card p.hint { color: #6b7280; font-size: 0.88rem; margin: 0 0 18px; }
	.alert { border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.9rem; }
	.alert strong { font-weight: 700; }
	.alert-ok { background: #e8eed4; color: #4d5a1a; border: 1px solid #c8d398; }
	.alert-err { background: #fde2e7; color: #591A2D; border: 1px solid #f1b3bf; }
	.alert-warn { background: #fff7e0; color: #6b4f00; border: 1px solid #ead9a0; }
	.maild-kv { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
	.maild-kv th {
		text-align: left; font-weight: 600; color: #28282B; width: 190px;
		padding: 9px 12px 9px 0; border-top: 1px dashed #eef0f4; vertical-align: top;
	}
	.maild-kv td { padding: 9px 0; border-top: 1px dashed #eef0f4; color: #4b5563; }
	.maild-kv tr:first-child th, .maild-kv tr:first-child td { border-top: 0; }
	.maild-kv code { background: #f4f5f8; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; }
	.maild-empty { color: #9ca3af; font-style: italic; }
	.maild-stat { display: inline-block; margin-right: 26px; }
	.maild-stat b { display: block; font-size: 1.6rem; font-family: 'Poppins', sans-serif; color: #28282B; }
	.maild-stat span { font-size: 0.8rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; }
	.maild-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
	.maild-form input[type="email"] {
		flex: 1 1 280px; padding: 9px 12px; border: 1px solid #dfe3ec;
		border-radius: 8px; font-size: 0.92rem;
	}
	.maild-btn {
		background: #909D3E; color: #fff; border: 0; border-radius: 8px;
		padding: 10px 22px; font-weight: 600; cursor: pointer; font-size: 0.92rem;
	}
	.maild-btn:hover { background: #7d8935; }
	.maild-log { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
	.maild-log th {
		text-align: left; background: #f7f8fb; color: #6b7280; font-weight: 600;
		padding: 9px 10px; border-bottom: 1px solid #e6e8ee;
		text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem;
	}
	.maild-log td { padding: 9px 10px; border-bottom: 1px solid #f1f2f6; vertical-align: top; color: #4b5563; }
	.maild-log tr:hover td { background: #fbfcfe; }
	.pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
	.pill-sent { background: #e8eed4; color: #4d5a1a; }
	.pill-failed { background: #fde2e7; color: #591A2D; }
	.maild-err {
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.76rem;
		color: #8a2d44; white-space: pre-wrap; word-break: break-word; max-width: 420px;
		display: block; margin-top: 4px;
	}
</style>

<div class="maild-page">

	<h1>Email diagnostics</h1>
	<p class="lede">
		Everything the site sends &mdash; signup confirmations, password resets, enquiry alerts,
		billing notices &mdash; goes through one mailer. This page shows how it is configured,
		lets you send a test, and records every attempt.
	</p>

	<?php if (!empty($flash_ok)): ?>
		<div class="alert alert-ok"><?= e($flash_ok) ?></div>
	<?php endif; ?>
	<?php if (!empty($flash_err)): ?>
		<div class="alert alert-err"><?= e($flash_err) ?></div>
	<?php endif; ?>

	<!-- ============================================== CONFIGURATION -->
	<div class="maild-card">
		<h2>Configuration</h2>
		<p class="hint">
			Read from <code>.env</code> at the repository root. Change a value there and reload
			this page &mdash; there is nothing to save here.
		</p>

		<?php if ($blockers > 0): ?>
			<div class="alert alert-err">
				<strong>Mail will not be delivered.</strong>
				<?= (int) $blockers ?> blocking problem<?= $blockers === 1 ? '' : 's' ?> below.
				Until <?= $blockers === 1 ? 'it is' : 'they are' ?> fixed, new accounts cannot be
				verified and nobody can reset a password.
			</div>
		<?php elseif (empty($issues)): ?>
			<div class="alert alert-ok">
				<strong>Configuration looks sound.</strong> Send a test message to confirm delivery.
			</div>
		<?php endif; ?>

		<?php foreach ($issues as $issue): ?>
			<div class="alert <?= $issue['level'] === 'error' ? 'alert-err' : 'alert-warn' ?>">
				<strong><?= e($issue['title']) ?></strong><br>
				<?= e($issue['detail']) ?>
			</div>
		<?php endforeach; ?>

		<table class="maild-kv">
			<?php foreach ($cfg as $key => $value): ?>
				<tr>
					<th><code><?= e($key) ?></code></th>
					<td>
						<?php if ($value === '' || $value === null): ?>
							<span class="maild-empty">not set</span>
						<?php else: ?>
							<?= e((string) $value) ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<?php if (empty($log_writable)): ?>
			<div class="alert alert-warn" style="margin-top:16px;">
				<strong>CodeIgniter's log directory is not writable.</strong>
				<code><?= e((string) $log_path) ?></code> is missing or read-only, so
				<code>log_message()</code> silently discards everything. The delivery log below
				still works &mdash; it writes to the database &mdash; but PHP-level errors are
				going nowhere. Create the directory and make it writable by the web server.
			</div>
		<?php endif; ?>
	</div>

	<!-- ==================================================== TEST SEND -->
	<div class="maild-card">
		<h2>Send a test message</h2>
		<p class="hint">
			Sends a real message through the same code path as a signup confirmation. If it fails,
			the exact error from the mail server is shown &mdash; that is normally enough to name
			the problem. If it succeeds but nothing arrives, the message is being accepted by the
			relay and discarded by the recipient, which is an authentication (SPF/DKIM/DMARC)
			problem on the sending domain rather than a setting here.
		</p>
		<form method="post" action="<?= base_url('admin/email-diagnostics') ?>" class="maild-form">
			<input type="email" name="test_email" required placeholder="you@example.com"
				   value="<?= e_attr(isset($test_default) ? $test_default : '') ?>">
			<button type="submit" name="send_test" value="1" class="maild-btn">Send test</button>
		</form>
	</div>

	<!-- ================================================= DELIVERY LOG -->
	<div class="maild-card">
		<h2>Delivery log</h2>

		<?php if (empty($has_log_table)): ?>
			<div class="alert alert-warn">
				<strong>Not recording yet.</strong> Run
				<code>database/migrations/0031_email_log.sql</code> to create the
				<code>email_log</code> table. Sending works without it; you just can't see
				what happened afterwards.
			</div>
		<?php else: ?>
			<p class="hint">
				Every message the site tried to send, whether or not it left. Counts cover the
				last 7 days; the table below lists the most recent attempts.
			</p>
			<p>
				<span class="maild-stat"><b><?= (int) $stats['sent'] ?></b><span>Sent, 7 days</span></span>
				<span class="maild-stat"><b><?= (int) $stats['failed'] ?></b><span>Failed, 7 days</span></span>
			</p>

			<?php if (empty($recent)): ?>
				<p class="maild-empty">Nothing recorded yet.</p>
			<?php else: ?>
				<table class="maild-log">
					<thead>
						<tr>
							<th>When</th>
							<th>To</th>
							<th>Type</th>
							<th>Subject</th>
							<th>Via</th>
							<th>Result</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($recent as $row): ?>
							<tr>
								<td><?= e(date('M j, g:ia', strtotime($row->created_at))) ?></td>
								<td><?= e($row->to_email) ?></td>
								<td><?= e($row->mail_type) ?></td>
								<td><?= e($row->subject) ?></td>
								<td><?= e($row->protocol) ?></td>
								<td>
									<?php if ($row->status === 'sent'): ?>
										<span class="pill pill-sent">sent</span>
									<?php else: ?>
										<span class="pill pill-failed">failed</span>
										<?php if (!empty($row->error)): ?>
											<span class="maild-err"><?= e($row->error) ?></span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- ================================================ WHAT TO CHECK -->
	<div class="maild-card">
		<h2>If mail lands in Spam, or never arrives at all</h2>
		<p class="hint">
			&ldquo;Sent&rdquo; above means a mail server accepted the message. It does not mean a
			person received it, or that it reached an Inbox. Both outcomes have the same short list
			of causes, and they are all on the <strong>sending domain</strong> rather than in this
			application &mdash; nothing on this page can fix them.
		</p>
		<p class="hint">
			<strong>Two tools tell you which one it is.</strong>
			Send a test to the throwaway address that
			<a href="https://www.mail-tester.com" target="_blank" rel="noopener">mail-tester.com</a>
			gives you: it scores the message out of 10 and names every failing check, SPF and DKIM
			included. And on a message that did arrive, open the raw source (Gmail:
			&#8942; &rarr; &ldquo;Show original&rdquo;) and read the
			<code>Authentication-Results:</code> line &mdash; the <code>spf=</code>,
			<code>dkim=</code> and <code>dmarc=</code> verdicts turn guesswork into a fact. For
			ongoing monitoring, add the domain to
			<a href="https://postmaster.google.com" target="_blank" rel="noopener">Gmail Postmaster Tools</a>.
		</p>
		<table class="maild-kv">
			<tr>
				<th>From domain mismatch</th>
				<td>
					<code>MAIL_FROM_ADDRESS</code> is on a different domain from the SMTP account
					used to send. Receivers fail SPF/DMARC and discard silently. Either send as
					the SMTP account's own address, or use a provider where the From domain is
					verified.
				</td>
			</tr>
			<tr>
				<th>No SPF record</th>
				<td>
					The From domain's DNS must authorise whoever relays for it &mdash; e.g.
					<code>v=spf1 include:_spf.google.com ~all</code> for Google Workspace.
				</td>
			</tr>
			<tr>
				<th>No DKIM signing</th>
				<td>
					Set up in the mail provider's console; it adds a DNS record and a signature to
					every message. Gmail and Outlook both treat unsigned bulk mail harshly.
				</td>
			</tr>
			<tr>
				<th>DMARC set to reject</th>
				<td>
					If the From domain publishes <code>p=reject</code> and SPF/DKIM don't line up,
					every message is dropped before any spam folder is involved.
				</td>
			</tr>
			<tr>
				<th>Gmail app password</th>
				<td>
					<code>SMTP_PASS</code> must be a 16-character app password with 2-step
					verification on, not the account password.
				</td>
			</tr>
			<tr>
				<th>From domain &ne; link domain</th>
				<td>
					A message sent from an address at <code>gmail.com</code> whose body links to
					<code>pearland411.com</code> has the exact shape of a phishing attempt, and
					Gmail files it accordingly. If the From-domain mismatch above was worked around
					by sending as the SMTP account's own address, this is the price &mdash; it is a
					stopgap, not the fix. Authenticate the real domain instead.
				</td>
			</tr>
			<tr>
				<th>No sending history</th>
				<td>
					A domain that has never sent mail has no reputation to draw on, so early
					messages are judged harshly. Volume here is low and steady, which is the good
					case; give it days, not hours, after SPF and DKIM are correct, and do not
					test by sending the same message twenty times.
				</td>
			</tr>
		</table>
	</div>

</div>
