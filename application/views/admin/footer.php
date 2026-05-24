<!-- Optional JavaScript -->

<!-- jQuery first, then Popper.js, then Bootstrap JS -->

<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<!-- CSRF token wiring for jQuery AJAX (reads <meta name="csrf-token">) -->
<script src="<?= base_url() ?>assets/js/csrf.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>



<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>



<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>

<script src="<?= base_url() ?>/assets/admin/js/admin-custom.js"></script>

<script src="http://harvesthq.github.io/chosen/chosen.jquery.js"></script>

<script src="<?= base_url('assets/js/customCode2.js'); ?>"></script>
<!-- Select2 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	$(document).ready(function() {
		$('select').select2({
			width: '100%',
			minimumResultsForSearch: 0 // always show search box
		});
	});
</script>

<!-- CKEditor 4 — only initialised on textareas with class="js-ckeditor" -->
<script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>
<script>
	if (typeof CKEDITOR !== 'undefined') {
		document.querySelectorAll('textarea.js-ckeditor').forEach(function (el) {
			CKEDITOR.replace(el, {
				removeButtons: 'Save,NewPage,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Iframe,Flash,Smiley,PageBreak,About',
				height: 320,
				removePlugins: 'elementspath',
				resize_enabled: true
			});
		});
	}
</script>


</body>



</html>