<?php
/**
 * One image upload field, with what is already stored shown beneath it.
 *
 * Used by the admin edit screens so they all behave the same way: the current
 * image is visible and opens full size, whatever you pick shows immediately,
 * and — for galleries — each stored image can be ticked for removal.
 *
 * The listing form previously showed no logo at all: it read
 * `$listing->listing_profile_image`, which is the name of the FILE INPUT, not
 * a column (the column is `listing_image`), and then prepended the images
 * directory to a path that already contained it.
 *
 * Variables consumed (the includer sets them; only $mf_name is required):
 *   $mf_name      (string) file input name. Use "field[]" for a gallery.
 *   $mf_label     (string) field label.
 *   $mf_hint      (string) small print under the input.
 *   $mf_current   (string|array) stored path(s), relative to the web root.
 *   $mf_multiple  (bool)   accept several files.
 *   $mf_remove    (string) checkbox name for per-image removal, e.g.
 *                          "remove_gallery[]". Omitted = no remove control.
 *   $mf_id        (string) input id; defaults to a slug of $mf_name.
 *   $mf_required  (bool)
 *   $mf_empty     (string) message when nothing is stored yet.
 *
 * Every one is unset at the end, so a page can include this repeatedly.
 */

$mf_name     = isset($mf_name) ? (string) $mf_name : '';
if ($mf_name === '') { return; }

$mf_label    = isset($mf_label) ? (string) $mf_label : 'Image';
$mf_hint     = isset($mf_hint) ? (string) $mf_hint : '';
$mf_multiple = !empty($mf_multiple);
$mf_remove   = isset($mf_remove) ? (string) $mf_remove : '';
$mf_required = !empty($mf_required);
$mf_empty    = isset($mf_empty) ? (string) $mf_empty : 'Nothing uploaded yet.';
$mf_id       = isset($mf_id) ? (string) $mf_id : preg_replace('/[^A-Za-z0-9_]/', '_', $mf_name);

$mf_items = array();
if (isset($mf_current)) {
	foreach ((array) $mf_current as $mf_path) {
		$mf_path = trim((string) $mf_path);
		if ($mf_path !== '') { $mf_items[] = $mf_path; }
	}
}

$mf_preview_id = $mf_id . '_preview';
?>
<div class="form-group">
	<label for="<?= e_attr($mf_id) ?>"><?= e($mf_label) ?><?= $mf_required ? ' <span class="req-star">*</span>' : '' ?></label>
	<input type="file"
	       id="<?= e_attr($mf_id) ?>"
	       name="<?= e_attr($mf_name) ?>"
	       class="form-control"
	       accept="image/*"
	       data-preview="#<?= e_attr($mf_preview_id) ?>"
	       <?= $mf_multiple ? 'multiple' : '' ?>
	       <?= $mf_required ? 'required' : '' ?>>
	<?php if ($mf_hint !== ''): ?>
		<small class="form-text text-muted"><?= $mf_hint ?></small>
	<?php endif; ?>

	<?php // Filled by media-preview.js the moment a file is chosen. ?>
	<div class="p411-media" id="<?= e_attr($mf_preview_id) ?>" style="display:none"></div>

	<div class="p411-media">
		<p class="p411-media__title"><?= $mf_multiple ? 'Currently uploaded' : 'Current image' ?></p>
		<?php if (empty($mf_items)): ?>
			<p class="p411-media__hint"><?= e($mf_empty) ?></p>
		<?php else: ?>
			<p class="p411-media__hint">
				Click an image to see it full size.
				<?php if ($mf_remove !== ''): ?>Tick <em>Remove</em> to delete it when you save.<?php endif; ?>
			</p>
			<div class="p411-media__grid" data-media-grid>
				<?php foreach ($mf_items as $mf_i => $mf_path): ?>
					<figure class="p411-media__cell">
						<img src="<?= e_url(base_url($mf_path)) ?>"
						     alt="<?= e_attr($mf_label . ' ' . ($mf_i + 1)) ?>"
						     loading="lazy" tabindex="0">
						<span class="p411-media__name"><?= e(basename($mf_path)) ?></span>
						<?php if ($mf_remove !== ''): ?>
							<label class="p411-media__remove">
								<input type="checkbox" name="<?= e_attr($mf_remove) ?>" value="<?= e_attr($mf_path) ?>">
								Remove
							</label>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
unset($mf_name, $mf_label, $mf_hint, $mf_current, $mf_multiple, $mf_remove,
      $mf_id, $mf_required, $mf_empty, $mf_items, $mf_preview_id, $mf_path, $mf_i);
?>
