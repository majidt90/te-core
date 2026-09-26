(function ($) {
	'use strict';

	var cfg = window.teCoreAdmin || {};
	var counter = 0;

	$('.te-color').wpColorPicker();

	var $list = $('#te-sections');
	if ($list.length && $.fn.sortable) {
		$list.sortable({
			handle: '.te-drag',
			axis: 'y',
			placeholder: 'te-placeholder',
			forcePlaceholderSize: true
		});
	}

	$('#te-add-section').on('click', function () {
		var type = $('#te-section-type').val();
		var tpl = document.getElementById('te-tpl-' + type);
		if (!tpl) return;
		counter += 1;
		var token = 'n' + Date.now() + counter;
		var html = tpl.innerHTML.replace(/__i__/g, token);
		$list.append(html);
		var added = $list.children('.te-section').last();
		added.find('.te-color').wpColorPicker();
		added.get(0).scrollIntoView({ block: 'nearest' });
	});

	$list.on('click', '.te-section-remove', function () {
		if (window.confirm(cfg.removeConfirm || 'Remove this section?')) {
			$(this).closest('.te-section').remove();
		}
	});

	$list.on('click', '.te-section-toggle', function () {
		var box = $(this).closest('.te-section');
		box.toggleClass('is-closed');
		$(this).attr('aria-expanded', box.hasClass('is-closed') ? 'false' : 'true');
	});

	$('#te-sections-form').on('submit', function () {
		$list.children('.te-section').each(function (i) {
			$(this).find('[name]').each(function () {
				this.name = this.name.replace(/te_core_sections\[[^\]]+\]/, 'te_core_sections[' + i + ']');
			});
		});
	});

	$('.te-reset-form').on('submit', function () {
		return window.confirm(cfg.resetConfirm || 'Restore the default sections?');
	});

	var frame;
	$(document).on('click', '.te-media-select', function (event) {
		event.preventDefault();
		var $field = $(this).closest('.te-media');
		frame = wp.media({
			title: cfg.mediaTitle || '',
			button: { text: cfg.mediaButton || '' },
			multiple: false,
			library: { type: 'image' }
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			var src = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
			$field.find('input[type="hidden"]').val(att.id);
			$field.find('img').attr('src', src).prop('hidden', false);
		});
		frame.open();
	});

	$(document).on('click', '.te-media-clear', function (event) {
		event.preventDefault();
		var $field = $(this).closest('.te-media');
		$field.find('input[type="hidden"]').val('0');
		$field.find('img').attr('src', '').prop('hidden', true);
	});
})(jQuery);
