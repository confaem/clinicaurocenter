(function () {
	"use strict";

	Dropzone.autoDiscover = false;

	document.querySelectorAll('[data-plugin="dropzone"]').forEach(function (el) {
		var options = { url: el.getAttribute('action') };

		var previewsContainer = el.dataset.previewsContainer;
		if (previewsContainer) options.previewsContainer = previewsContainer;

		var uploadPreviewTemplate = el.dataset.uploadPreviewTemplate;
		if (uploadPreviewTemplate) {
			var templateEl = document.querySelector(uploadPreviewTemplate);
			if (templateEl) options.previewTemplate = templateEl.innerHTML;
		}

		new Dropzone(el, options);
	});
})();
