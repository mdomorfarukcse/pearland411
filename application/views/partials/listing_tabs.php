<?php
/**
 * The My listings / My claims tabs at the head of both member screens.
 *
 * Expects $active_tab ('listings' | 'claims') and $tab_counts
 * (User::listingTabCounts()). Claims live beside listings because a claim IS
 * a listing on its way to the member — once approved it moves from one tab
 * to the other.
 */
$__tab   = isset($active_tab) ? (string) $active_tab : 'listings';
$__count = isset($tab_counts) && is_array($tab_counts) ? $tab_counts : array();
$__tabs  = array(
	'listings' => array('My listings', base_url('user-listing')),
	'claims'   => array('My claims', base_url('user/my-claims')),
);
?>
<nav class="p411-mtabs" aria-label="Your businesses">
	<?php foreach ($__tabs as $__k => $__t): ?>
		<a href="<?= e_url($__t[1]) ?>" class="p411-mtabs__tab<?= $__k === $__tab ? ' is-active' : '' ?>"
		   <?= $__k === $__tab ? 'aria-current="page"' : '' ?>>
			<?= e($__t[0]) ?>
			<?php if (isset($__count[$__k])): ?><span class="p411-mtabs__n"><?= (int) $__count[$__k] ?></span><?php endif; ?>
		</a>
	<?php endforeach; ?>
</nav>
<style>
	/* Scoped by class; the member dashboard has no page kit of its own. */
	.p411-mtabs { display: flex; gap: 6px; flex-wrap: wrap; margin: 0 0 18px; border-bottom: 2px solid #E4E6EC; }
	.p411-mtabs__tab { display: inline-flex; align-items: center; gap: 7px; padding: 10px 16px; margin-bottom: -2px;
		border-bottom: 3px solid transparent; color: #5A6472; font-weight: 600; font-size: 15px; text-decoration: none; }
	.p411-mtabs__tab:hover { color: #20245B; text-decoration: none; }
	.p411-mtabs__tab.is-active { color: #20245B; border-bottom-color: #79B82A; }
	.p411-mtabs__n { display: inline-block; min-width: 22px; padding: 1px 7px; border-radius: 11px;
		background: #EEF0F7; color: #20245B; font-size: 12px; line-height: 18px; text-align: center; }
</style>
<?php unset($__tab, $__count, $__tabs, $__k, $__t); ?>
