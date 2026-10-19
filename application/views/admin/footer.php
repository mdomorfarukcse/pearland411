<!-- Optional JavaScript -->

<!-- jQuery first, then Popper.js, then Bootstrap JS -->

<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<!-- CSRF token wiring for jQuery AJAX (reads <meta name="csrf-token">) -->
<script src="<?= base_url() ?>assets/js/csrf.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>



<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>



<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>

<script src="<?= base_url('assets/admin/js/admin-custom.js') ?>?v=<?= @filemtime(FCPATH . 'assets/admin/js/admin-custom.js') ?: 1 ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

<script src="<?= base_url('assets/js/customCode2.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/customCode2.js') ?: 1 ?>"></script>
<!-- Select2 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	$(document).ready(function() {
		// Skip selects that opt out (admin-table toolbar uses its own dropdowns).
		$('select:not(.admin-table-size):not(.no-select2)').select2({
			width: '100%',
			minimumResultsForSearch: 0
		});
	});
</script>

<!-- Universal admin-table enhancer — search, page-size, prev/next.
     Wires automatically to every <table data-admin-table>, plus any
     <table> following the legacy #pg-sear input. -->
<?php
// Publish the operator's rows-per-page choice so admin-table.js can render the
// dropdown and match its own client-side paging to what the server did. One
// setting drives both pagers; see application/helpers/admin_helper.php.
// The EFFECTIVE size, not the raw choice. With nothing chosen the choice is
// '' and no <option> matched, so the browser fell back to showing the first
// one — the category list read "10 per page" while displaying all 23 rows.
$__ap_choice = admin_per_page_choice();
if ($__ap_choice === '') {
	$__ap_effective = (string) admin_per_page();
	$__ap_choice = array_key_exists($__ap_effective, admin_per_page_options()) ? $__ap_effective : '';
}
$__ap_options = array();
foreach (admin_per_page_options() as $__ap_v => $__ap_l) {
	$__ap_options[] = array('value' => $__ap_v, 'label' => $__ap_l);
}
?>
<script>
	window.P411_ADMIN_PER_PAGE         = <?= (int) admin_per_page() ?>;
	window.P411_ADMIN_PER_PAGE_CHOICE  = <?= json_encode($__ap_choice) ?>;
	window.P411_ADMIN_PER_PAGE_OPTIONS = <?= json_encode($__ap_options) ?>;
</script>
<script src="<?= base_url('assets/admin/js/admin-table.js') ?>?v=<?= @filemtime(FCPATH . 'assets/admin/js/admin-table.js') ?: 1 ?>"></script>
<script src="<?= base_url('assets/admin/js/media-preview.js') ?>?v=<?= @filemtime(FCPATH . 'assets/admin/js/media-preview.js') ?: 1 ?>"></script>

<!-- CKEditor 4 (4.22.1 — last free release; suppresses the "not secure" nag). -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
	if (typeof CKEDITOR !== 'undefined') {
		// Stop the CDN-served editor from rendering the red
		// "This version is not secure" overlay. Config must be set
		// before any CKEDITOR.replace() call.
		CKEDITOR.config.versionCheck = false;

		document.querySelectorAll('textarea.js-ckeditor').forEach(function (el) {
			CKEDITOR.replace(el, {
				removeButtons: 'Save,NewPage,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Iframe,Flash,Smiley,PageBreak,About',
				height: 320,
				removePlugins: 'elementspath',
				resize_enabled: true,
				versionCheck: false
			});
		});
	}
</script>


</body>



</html>