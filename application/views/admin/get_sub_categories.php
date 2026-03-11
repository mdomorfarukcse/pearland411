<option value="">Choose a Sub Category</option>
<?php
foreach ($sub_categories as $category) {
?>
	<option value="<?= $category->sub_category_id ?>"><?= $category->sub_category_name ?></option>
<?php
}
?>