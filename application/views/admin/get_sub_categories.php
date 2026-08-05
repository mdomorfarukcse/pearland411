<?php
/**
 * Sub-category <option> list, for the category dropdown's onChange handler.
 *
 * $selected carries the ids already attached to the listing. Without it this
 * partial emitted plain options with no `selected` attribute, and the handler
 * replaces the select's innerHTML wholesale — so touching the category
 * dropdown silently cleared whatever sub-categories were saved.
 */
$selected = (isset($selected) && is_array($selected)) ? array_map('intval', $selected) : array();
foreach ($sub_categories as $category):
	$sid = (int) $category->sub_category_id;
	?>
	<option value="<?= $sid ?>"<?= in_array($sid, $selected, TRUE) ? ' selected' : '' ?>><?= html_escape($category->sub_category_name) ?></option>
<?php endforeach; ?>
