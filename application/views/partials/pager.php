<?php
/**
 * The pager under a public list: page links, "Page X of N", and the
 * "Go to page" jump.
 *
 * Every list page grew its own copy of this block, and they had drifted — the
 * directory's ellipsis was a dead `<span>`, /all-blog printed one link per page
 * with no window at all, and only some of them said which page you were on.
 * One partial, so the next change lands on all of them.
 *
 * Set before including:
 *   $pager_label        string  aria-label for the <nav>.
 *   $pager_links        string  a rendered <ul class="pagination">. Either the
 *                               helper's pager_links() or CI3's create_links().
 *   $pager_current      int
 *   $pager_total        int     total pages.
 *   $pager_jump_action  string  optional — URL the "Go to page" form submits
 *                               to. The page must read `?page=`. Omit to leave
 *                               the jump control out.
 *   $pager_jump_hidden  array   optional — query values to carry through
 *                               (sort, search term, …).
 *
 * All of them are unset at the end: these pages include several partials and a
 * value left behind is the kind of thing that shows up two sections later.
 */

$pager_label       = isset($pager_label)   ? (string) $pager_label : 'Pages';
$pager_links       = isset($pager_links)   ? (string) $pager_links : '';
$pager_current     = isset($pager_current) ? (int) $pager_current  : 1;
$pager_total       = isset($pager_total)   ? (int) $pager_total    : 0;
$pager_jump_action = isset($pager_jump_action) ? (string) $pager_jump_action : '';
$pager_jump_hidden = isset($pager_jump_hidden) && is_array($pager_jump_hidden) ? $pager_jump_hidden : array();

// The jump control earns its place once the window stops showing every page.
// Below that it is noise beside five numbered links.
$pager_show_jump = ($pager_jump_action !== '' && $pager_total > 7);

if ($pager_links !== '' || $pager_show_jump): ?>
	<nav class="p411-pager" aria-label="<?= e($pager_label) ?>">
		<?= $pager_links ?>

		<?php if ($pager_total > 1): ?>
			<p class="p411-pager__hint">Page <?= number_format($pager_current) ?> of <?= number_format($pager_total) ?></p>
		<?php endif; ?>

		<?php if ($pager_show_jump): ?>
			<?= pager_jump_form($pager_jump_action, $pager_total, $pager_current, $pager_jump_hidden) ?>
		<?php endif; ?>
	</nav>
<?php endif;

unset($pager_label, $pager_links, $pager_current, $pager_total,
      $pager_jump_action, $pager_jump_hidden, $pager_show_jump);
