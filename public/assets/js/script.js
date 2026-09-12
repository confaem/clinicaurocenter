/*
Author       : Dreamstechnologies
Template Name: Dreams EMR - Bootstrap Admin Template
*/

(function (window, document) {
	"use strict";

	// jQuery's default easing.
	const swing = (p) => 0.5 - Math.cos(p * Math.PI) / 2;

	// The properties jQuery animates for slideUp/slideDown.
	const SLIDE_PROPS = ['height', 'marginTop', 'marginBottom', 'paddingTop', 'paddingBottom'];

	// Tracks the running animation per element so it can be cancelled,
	// mirroring jQuery's .stop(true, true).
	const running = new WeakMap();

	const isVisible = (el) => !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));

	// jQuery measures the viewport with clientWidth (excludes the scrollbar),
	// not window.innerWidth. Keep that so the breakpoint checks match exactly.
	const winWidth = () => document.documentElement.clientWidth;

	function stop(el, jumpToEnd) {
		const anim = running.get(el);
		if (!anim) return;
		cancelAnimationFrame(anim.frame);
		running.delete(el);
		if (jumpToEnd) anim.finish();
	}

	function slide(el, direction, duration, complete) {
		if (!el) return;
		stop(el, true);

		const down = direction === 'down';
		const inline = {};
		SLIDE_PROPS.forEach((p) => { inline[p] = el.style[p]; });
		const inlineOverflow = el.style.overflow;

		// Measure the element's natural box. When sliding down it is hidden, so
		// reveal it first - the same swap jQuery performs internally.
		if (down) el.style.display = '';
		if (down && getComputedStyle(el).display === 'none') el.style.display = 'block';

		const computed = getComputedStyle(el);
		const target = {};
		SLIDE_PROPS.forEach((p) => { target[p] = parseFloat(computed[p]) || 0; });

		el.style.overflow = 'hidden';

		const restore = () => {
			SLIDE_PROPS.forEach((p) => { el.style[p] = inline[p]; });
			el.style.overflow = inlineOverflow;
		};

		const finish = () => {
			restore();
			if (!down) el.style.display = 'none';
			if (typeof complete === 'function') complete.call(el);
		};

		const start = performance.now();
		const total = typeof duration === 'number' ? duration : 400;

		const step = (now) => {
			const p = Math.min(1, (now - start) / total);
			const eased = swing(p);
			const factor = down ? eased : 1 - eased;
			SLIDE_PROPS.forEach((prop) => { el.style[prop] = (target[prop] * factor) + 'px'; });

			if (p < 1) {
				running.set(el, { frame: requestAnimationFrame(step), finish });
			} else {
				running.delete(el);
				finish();
			}
		};

		// Paint the first frame synchronously so there is no flash of full height.
		SLIDE_PROPS.forEach((prop) => { el.style[prop] = (down ? 0 : target[prop]) + 'px'; });
		running.set(el, { frame: requestAnimationFrame(step), finish });
	}

	const slideDown = (el, duration, complete) => slide(el, 'down', duration, complete);
	const slideUp = (el, duration, complete) => slide(el, 'up', duration, complete);
	const slideToggle = (el, duration, complete) =>
		slide(el, isVisible(el) ? 'up' : 'down', duration, complete);

	function fadeOut(el, duration, complete) {
		if (!el) return;
		stop(el, true);

		const inlineOpacity = el.style.opacity;
		const from = parseFloat(getComputedStyle(el).opacity);
		const total = typeof duration === 'number' ? duration : 400;
		const start = performance.now();

		const finish = () => {
			el.style.display = 'none';
			el.style.opacity = inlineOpacity;
			if (typeof complete === 'function') complete.call(el);
		};

		const step = (now) => {
			const p = Math.min(1, (now - start) / total);
			el.style.opacity = String(from * (1 - swing(p)));
			if (p < 1) {
				running.set(el, { frame: requestAnimationFrame(step), finish });
			} else {
				running.delete(el);
				finish();
			}
		};
		running.set(el, { frame: requestAnimationFrame(step), finish });
	}

	// jQuery's .hide()/.show(): remember the inline display so .show() restores it.
	const hidden = new WeakMap();

	function hide(el) {
		if (!el || !isVisible(el)) return;
		hidden.set(el, el.style.display);
		el.style.display = 'none';
	}

	function show(el) {
		if (!el) return;
		if (getComputedStyle(el).display !== 'none') return;
		el.style.display = hidden.has(el) ? hidden.get(el) : '';
		if (getComputedStyle(el).display === 'none') el.style.display = 'block';
	}

	// Event delegation with the same semantics as $(root).on(type, selector, fn).
	function delegate(root, type, selector, handler) {
		root.addEventListener(type, function (e) {
			const match = e.target.closest ? e.target.closest(selector) : null;
			if (match && root.contains(match)) handler.call(match, e);
		});
	}

	const onReady = (fn) => {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	};

	// Vanilla replacement for the theiaStickySidebar jQuery plugin. Uses native
	// CSS position:sticky (applied inline, not via a stylesheet change) with the
	// same additionalMarginTop offset the plugin was configured with, matching
	// its behaviour for sidebars that fit within the viewport height.
	function initStickySidebar(selector, additionalMarginTop) {
		if (winWidth() <= 767) return;
		document.querySelectorAll(selector).forEach((el) => {
			el.style.position = 'sticky';
			el.style.top = (additionalMarginTop || 0) + 'px';
		});
	}

	window.DTUtil = {
		swing, slideDown, slideUp, slideToggle, fadeOut,
		show, hide, isVisible, winWidth, delegate, onReady, stop,
		initStickySidebar
	};
})(window, document);

(function () {
    "use strict";

	const wrapper = document.querySelector('.main-wrapper');
	const overlay = document.createElement('div');
	overlay.className = 'sidebar-overlay';
	if (wrapper && wrapper.parentNode) {
		wrapper.parentNode.insertBefore(overlay, wrapper);
	}

	// Toggle Mobile Menu
	DTUtil.delegate(document, 'click', '#mobile_btn', function (e) {
		e.preventDefault();
		if (wrapper) wrapper.classList.toggle('slide-nav');
		overlay.classList.toggle('opened');
		document.documentElement.classList.toggle('menu-opened');
	});

	// Close sidebar on close button click
	DTUtil.delegate(document, 'click', '.sidebar-close, .sidebar-overlay', function () {
		if (wrapper) wrapper.classList.remove('slide-nav');
		overlay.classList.remove('opened');
		document.documentElement.classList.remove('menu-opened');
		document.body.classList.remove('full-width');
	});

	// Sidebar
	function initSidebarMenu() {
		const menuLinks = document.querySelectorAll('.sidebar-menu a');

		menuLinks.forEach(function (link) {
			link.addEventListener('click', function (e) {
				const submenu = link.nextElementSibling;
				if (submenu && submenu.tagName === 'UL') {
					if (link.parentElement.classList.contains('submenu')) {
						e.preventDefault();

						if (!link.classList.contains('subdrop')) {
							// Collapse all other open submenus
							const closestUl = link.closest('ul');
							if (closestUl) {
								const visibleUls = Array.from(closestUl.querySelectorAll('ul')).filter(ul => DTUtil.isVisible(ul));
								visibleUls.forEach(ul => DTUtil.slideUp(ul, 250));
								const subdrops = closestUl.querySelectorAll('a.subdrop');
								subdrops.forEach(a => a.classList.remove('subdrop'));
							}

							// Expand current
							DTUtil.stop(submenu, true);
							DTUtil.slideDown(submenu, 350);
							link.classList.add('subdrop');
						} else {
							// Collapse current
							link.classList.remove('subdrop');
							DTUtil.stop(submenu, true);
							DTUtil.slideUp(submenu, 350);
						}
					}
				}
			});
		});

		// Ensure any active link's submenu is shown with animation-ready state
		document.querySelectorAll('.sidebar-menu ul li.submenu a.active').forEach(function (activeLink) {
			const submenu = activeLink.closest('ul');
			if (!submenu) return;
			const parentLink = submenu.previousElementSibling;

			if (parentLink && parentLink.tagName === 'A') {
				parentLink.classList.add('active', 'subdrop');
			}
			submenu.style.display = 'block';

			// Now mark it manually as ready for animation
			submenu.style.height = submenu.offsetHeight + 'px'; // set explicit height
			submenu.style.height = 'auto';     // restore auto height
		});
	}
	
	// Initialize Sidebar
	initSidebarMenu();

	// Mouse Over - Mini Sidebar Hover
	(function() {
		var sidebarEl = document.querySelector('.sidebar');
		var headerLeftEl = document.querySelector('.header-left');
		if (!sidebarEl) return;

		function onSidebarEnter() {
			if (document.body.classList.contains('mini-sidebar') && DTUtil.isVisible(document.getElementById('toggle_btn'))) {
				document.body.classList.add('expand-menu');
				// Show only the currently active/open submenus (no re-animation)
				document.querySelectorAll('.sidebar-menu .subdrop + ul').forEach(function(ul) {
					ul.style.display = 'block';
				});
			}
		}

		function onSidebarLeave() {
			if (document.body.classList.contains('mini-sidebar')) {
				document.body.classList.remove('expand-menu');
				// Clear inline display styles so CSS takes back control of the collapsed state.
				// Do NOT set display:none — that permanently hides elements across hover cycles.
				document.querySelectorAll('.sidebar-menu ul').forEach(function(ul) {
					ul.style.display = '';
				});
			}
		}

		sidebarEl.addEventListener('mouseenter', onSidebarEnter);
		sidebarEl.addEventListener('mouseleave', onSidebarLeave);
		if (headerLeftEl) {
			headerLeftEl.addEventListener('mouseenter', onSidebarEnter);
			headerLeftEl.addEventListener('mouseleave', onSidebarLeave);
		}
	})();

	// Toggle Button
	DTUtil.delegate(document, 'click', '#toggle_btn, #toggle_btn2', function (e) {
		e.preventDefault();
		const body = document.body;
		const html = document.documentElement;
		const isMini = body.classList.contains('mini-sidebar');
		const isFullWidth = html.getAttribute('data-layout') === 'full-width';
		const isHidden = html.getAttribute('data-layout') === 'hidden';
	
		if (isMini) {
			body.classList.remove('mini-sidebar');
			this.classList.add('active');
			localStorage.setItem('screenModeNightTokenState', 'night');
			setTimeout(function () {
				document.querySelectorAll(".header-left").forEach(el => el.classList.add("active"));
			}, 100);
		} else {
			body.classList.add('mini-sidebar');
			this.classList.remove('active');
			localStorage.removeItem('screenModeNightTokenState');
			setTimeout(function () {
				document.querySelectorAll(".header-left").forEach(el => el.classList.remove("active"));
			}, 100);
		}
	
		// If <html> has data-layout="full-width", apply full-width class to <body>
		if (isFullWidth) {
			body.classList.add('full-width');
			body.classList.remove('mini-sidebar');
			document.querySelectorAll('.sidebar-overlay').forEach(el => el.classList.add('opened'));
		} else {
			body.classList.remove('full-width');
		}

		// If <html> has data-layout="hidden", apply hidden-layout class to <body>
		if (isHidden) {
			body.classList.toggle('hidden-layout');
			body.classList.remove('mini-sidebar');
		}
	});
	
	// Tooltip
	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
	
	// Input Mask
	document.querySelectorAll('[data-toggle="input-mask"]').forEach(input => {
		const format = input.getAttribute('data-mask-format');
		const reverse = input.getAttribute('data-reverse') === 'true';		
		if (format && typeof Inputmask !== 'undefined') {
			Inputmask({ 
				mask: format.replace(/0/g, '9'), 
				reverse: reverse 
			}).mask(input);
		}
	});

	// Form Validation
	document.querySelectorAll('.needs-validation').forEach(form => {
		form.addEventListener('submit', event => {
			if (!form.checkValidity()) {
				event.preventDefault();
				event.stopPropagation();
			}
			form.classList.add('was-validated');
		}, false);
	});

	// Choices
	function initChoices() {
		document.querySelectorAll('[data-choices]').forEach(item => {
			const config = {
				allowHTML: true
			};
			const attrs = item.attributes;

			if (attrs['data-choices-groups']) {
				config.placeholderValue = 'This is a placeholder set in the config';
			}
			if (attrs['data-choices-search-false']) {
				config.searchEnabled = false;
			}
			if (attrs['data-choices-search-true']) {
				config.searchEnabled = true;
			}
			if (attrs['data-choices-removeItem']) {
				config.removeItemButton = true;
			}
			if (attrs['data-choices-sorting-false']) {
				config.shouldSort = false;
			}
			if (attrs['data-choices-sorting-true']) {
				config.shouldSort = true;
			}
			if (attrs['data-choices-multiple-remove']) {
				config.removeItemButton = true;
			}
			if (attrs['data-choices-limit']) {
				config.maxItemCount = parseInt(attrs['data-choices-limit'].value);
			}
			if (attrs['data-choices-editItem-true']) {
				config.editItems = true;
			}
			if (attrs['data-choices-editItem-false']) {
				config.editItems = false;
			}
			if (attrs['data-choices-text-unique-true']) {
				config.duplicateItemsAllowed = false;
			}
			if (attrs['data-choices-text-disabled-true']) {
				config.addItems = false;
			}

			const instance = new Choices(item, config);

			if (attrs['data-choices-text-disabled-true']) {
				instance.disable();
			}
		});
	}

	// Call it when the DOM is ready
	document.addEventListener('DOMContentLoaded', initChoices);

	// Initialize Flatpickr on elements with data-provider="flatpickr"
	document.querySelectorAll('[data-provider="flatpickr"]').forEach(el => {
		const config = {
			disableMobile: true
		};
		if (el.hasAttribute('data-date-format')) {
			config.dateFormat = el.getAttribute('data-date-format');
		}
		if (el.hasAttribute('data-enable-time')) {
			config.enableTime = true;
			config.dateFormat = config.dateFormat ? `${config.dateFormat} H:i` : 'Y-m-d H:i';
		}
		if (el.hasAttribute('data-altFormat')) {
			config.altInput = true;
			config.altFormat = el.getAttribute('data-altFormat');
		}
		if (el.hasAttribute('data-minDate')) {
			config.minDate = el.getAttribute('data-minDate');
		}
		if (el.hasAttribute('data-maxDate')) {
			config.maxDate = el.getAttribute('data-maxDate');
		}
		if (el.hasAttribute('data-default-date')) {
			const val = el.getAttribute('data-default-date');
			if (val && val !== 'true') config.defaultDate = val;
		}
		if (el.hasAttribute('data-multiple-date')) {
			config.mode = 'multiple';
		}
		if (el.hasAttribute('data-range-date')) {
			config.mode = 'range';
		}
		if (el.hasAttribute('data-inline-date')) {
			config.inline = true;
			const val = el.getAttribute('data-inline-date');
			if (val && val !== 'true') config.defaultDate = val;
		}
		if (el.hasAttribute('data-disable-date')) {
			config.disable = el.getAttribute('data-disable-date').split(',');
		}
		if (el.hasAttribute('data-week-number')) {
			config.weekNumbers = true;
		}
		flatpickr(el, config);
	});

	// Time Picker
	document.querySelectorAll('[data-provider="timepickr"]').forEach(item => {
		const attrs = item.attributes;
		const config = {
			enableTime: true,
			noCalendar: true,
			dateFormat: "H:i"
		};
		if (attrs["data-time-hrs"]) {
			config.time_24hr = true;
		}
		if (attrs["data-min-time"]) {
			config.minTime = attrs["data-min-time"].value;
		}
		if (attrs["data-max-time"]) {
			config.maxTime = attrs["data-max-time"].value;
		}
		if (attrs["data-default-time"]) {
			config.defaultDate = attrs["data-default-time"].value;
		}
		if (attrs["data-time-inline"]) {
			config.inline = true;
			config.defaultDate = attrs["data-time-inline"].value;
		}
		flatpickr(item, config);
	});
  
	// Select2
	if (window.Choices) {
		document.querySelectorAll('[data-toggle="select2"]').forEach((el) => {
			if (el.choicesInstance) return;
			const config = { shouldSort: false, itemSelectText: '' };

			// Placeholder
			if (el.getAttribute('data-placeholder')) {
				config.placeholderValue = el.getAttribute('data-placeholder');
			}

			// Allow clear (closest Choices equivalent: per-item remove button)
			if (el.getAttribute('data-allow-clear') === 'true') {
				config.removeItemButton = true;
			}

			// Tags (user can enter new values)
			if (el.getAttribute('data-tags') === 'true') {
				config.addItems = true;
				config.duplicateItemsAllowed = false;
			}

			// Maximum selection
			if (el.getAttribute('data-max-selections')) {
				config.maxItemCount = parseInt(el.getAttribute('data-max-selections'));
			}

			if (el.multiple) {
				config.removeItemButton = true;
			}

			el.choicesInstance = new Choices(el, config);

			// AJAX (for dynamic search) - populate choices from the endpoint once,
			// mirroring the old Select2 "search-driven remote data" setups.
			const ajaxUrl = el.getAttribute('data-ajax--url');
			if (ajaxUrl) {
				el.choicesInstance.setChoices(
					() => fetch(ajaxUrl).then((res) => res.json()).then((data) => {
						const items = data.items || data || [];
						return items.map((item) => ({ value: item.id, label: item.text || item.name }));
					}),
					'value', 'label', false
				);
			}
		});
	}

	// Select 2    
    if (window.Choices) {
		document.querySelectorAll('.select').forEach((el) => {
			if (el.choicesInstance || el.hasAttribute('data-choices') || el.hasAttribute('data-toggle')) return;
			el.choicesInstance = new Choices(el, {
				shouldSort: false,
				searchEnabled: false,
				itemSelectText: ''
			});
		});
	}

	// Popover
	const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
	const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

	// Toasts
	document.addEventListener('DOMContentLoaded', function () {
		const toastPlacement = document.getElementById('toastPlacement');
		const placementSelect = document.getElementById('selectToastPlacement');
		if (toastPlacement && placementSelect) {
			const originalClass = toastPlacement.className;
			placementSelect.addEventListener('change', function () {
			toastPlacement.className = `${originalClass} ${this.value}`.trim();
			});
		}
	});

	// Select Table Checkbox
	document.getElementById('select-all')?.addEventListener('change', function() {
		document.querySelectorAll('.form-check.form-check-md input[type="checkbox"]').forEach(function(checkbox) {
			checkbox.checked = this.checked;
		}, this);
	});

	// Full Screen
    if (document.querySelector('.btnFullscreen')) {
		const toggleFullscreen = function () {
			if (!document.fullscreenElement) {
				document.documentElement.requestFullscreen(); 
			} else {
				if (document.exitFullscreen) {
					document.exitFullscreen();
				}
			}
		};
		document.querySelectorAll('.btnFullscreen').forEach(function(btn) {
			btn.addEventListener('click', toggleFullscreen);
		});
	}

	// Sticky Sidebar
	DTUtil.initStickySidebar('.theiaStickySidebar', 20);

	// Select
	function initCheckboxGroup(groupClass, selectAllClass, checkboxClass) {
		document.querySelectorAll('.' + groupClass).forEach(function(group) {
			const selectAll = group.querySelector('.' + selectAllClass);
			const checkboxes = group.querySelectorAll('.' + checkboxClass);

			if (selectAll) {
				selectAll.addEventListener('change', function() {
					checkboxes.forEach(function(checkbox) {
						checkbox.checked = selectAll.checked;
					});
				});
			}
		});
	}

	// Initialize all groups
	initCheckboxGroup('select-group', 'selectall', 'form-check-md');
	initCheckboxGroup('select-group2', 'selectall2', 'form-check-md2');
	initCheckboxGroup('select-group3', 'selectall3', 'form-check-md3');

	// Circle Progress
	document.querySelectorAll('.circle-progress').forEach(function(circle) {
		var value = parseFloat(circle.getAttribute('data-value'));
		var left = circle.querySelector('.progress-left .progress-bar');
		var right = circle.querySelector('.progress-right .progress-bar');

		if (value <= 0) {
			right.style.transform = 'rotate(0deg)';
			left.style.transform = 'rotate(0deg)';
		} else if (value <= 50) {
			right.style.transform = 'rotate(' + (value / 100 * 360) + 'deg)';
			left.style.transform = 'rotate(0deg)';
		} else if (value < 100) {
			right.style.transform = 'rotate(180deg)';
			left.style.transform = 'rotate(' + ((value - 50) / 100 * 360) + 'deg)';
		} else {
			// 100%: both halves at 180deg
			right.style.transform = 'rotate(180deg)';
			left.style.transform = 'rotate(180deg)';
		}
	});
	
	// Toggle Password
	document.querySelectorAll('.toggle-password').forEach(function(toggle) {
		toggle.addEventListener('click', function() {
			const icon = this.querySelector('i');
			const input = this.closest('.input-group').querySelector('.pass-input');
			if (input.getAttribute('type') === 'password') {
				input.setAttribute('type', 'text');
				icon.classList.remove('ti-eye-off');
				icon.classList.add('ti-eye');
			} else {
				input.setAttribute('type', 'password');
				icon.classList.remove('ti-eye');
				icon.classList.add('ti-eye-off');
			}
		});
	});

	//Increment Decrement Numberes
	document.addEventListener("DOMContentLoaded", function () {
		document.querySelectorAll(".custom-increment.cart").forEach(container => {
			const input = container.querySelector("input[type='text']");

			const incrementBtn = container.querySelector("button[class*='increment']");
			const decrementBtn = container.querySelector("button[class*='decrement']");

			if (!input || !incrementBtn || !decrementBtn) return;

			incrementBtn.addEventListener("click", function () {
			let current = parseInt(input.value, 10) || 0;
			input.value = current + 1;
			});

			decrementBtn.addEventListener("click", function () {
			let current = parseInt(input.value, 10) || 0;
			input.value = Math.max(0, current - 1); // Prevent going below 0
			});
		});
	});

	// Add Patient
	if (document.querySelector('.vertical-tab')) {
		// Next button
		document.querySelectorAll('.form-wizard-content .next-tab-btn').forEach(btn => {
			btn.addEventListener('click', function () {
			const fieldset = this.closest('.form-wizard-content');
			const nextFieldset = fieldset?.nextElementSibling;
			const progressBar = document.querySelector('.vertical-tab .nav-pills');

			if (fieldset && nextFieldset && progressBar) {
				fieldset.classList.remove('active'); // Hide current step
				nextFieldset.classList.add('active'); // Show next step

				// Optional: fade effect
				nextFieldset.style.opacity = 0;
				nextFieldset.style.transition = 'opacity 0.5s';
				setTimeout(() => {
				nextFieldset.style.opacity = 1;
				}, 10);

				// Update progress bar state
				const active = progressBar.querySelector('.active');
				if (active) {
				active.classList.remove('active');
				active.classList.add('activated');
				const next = active.nextElementSibling;
				if (next) next.classList.add('active');
				}
			}
			});
		});

		// Back button
		document.querySelectorAll('.form-wizard-content .back-btn').forEach(btn => {
			btn.addEventListener('click', function () {
			const fieldset = this.closest('.form-wizard-content');
			const prevFieldset = fieldset?.previousElementSibling;
			const progressBar = document.querySelector('.vertical-tab .nav-pills');

			if (fieldset && prevFieldset && progressBar) {
				fieldset.classList.remove('active'); // Hide current step
				prevFieldset.classList.add('active'); // Show previous step

				// Optional: fade effect
				prevFieldset.style.opacity = 0;
				prevFieldset.style.transition = 'opacity 0.5s';
				setTimeout(() => {
				prevFieldset.style.opacity = 1;
				}, 10);

				// Update progress bar state
				const active = progressBar.querySelector('.active');
				if (active) {
				active.classList.remove('active', 'activated');
				const prev = active.previousElementSibling;
				if (prev) prev.classList.add('active');
				}
			}
			});
		});
	}

	// Mail Check
	document.addEventListener("DOMContentLoaded", function () {
		// Select all checkboxes inside the table
		const checkboxes = document.querySelectorAll(".mail-check-input");

		checkboxes.forEach((checkbox) => {
			checkbox.addEventListener("change", function () {
				const row = this.closest("tr"); // Find the closest <tr> parent
				if (this.checked) {
				row.classList.add("mail-selected"); // Add class when checked
				} else {
				row.classList.remove("mail-selected"); // Remove class when unchecked
				}
			});
		});
  	});

	// Aprrearence Settings 
	document.querySelectorAll('.theme-image').forEach(function(img) {
		img.addEventListener('click', function() {
			document.querySelectorAll('.theme-image').forEach(function(el) {
				el.classList.remove('active');
			});
			this.classList.add('active');
		});
	});

	// Kanban Drag
	if(document.querySelectorAll('.kanban-drag-wrap').length > 0) {
        $(".kanban-drag-wrap").sortable({
            connectWith: ".kanban-drag-wrap",
            handle: ".kanban-card",
            placeholder: "drag-placeholder"
        });
    }

	// Click
	document.querySelectorAll('.star').forEach(function(star) {
		star.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
	
			let icon = this.querySelector('i');
			icon.classList.toggle('ti-star');
			icon.classList.toggle('ti-star-filled');
			icon.classList.toggle('text-warning'); // Optional color change
		});
	});
	 
	// Gallery
    if(document.querySelectorAll('.call-users').length > 0) {
		var swiper = new Swiper(".call-users", {
		slidesPerView: 1,
		spaceBetween: 24,
		keyboard: {
			enabled: true,
		},
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
		},
		navigation: {
			nextEl: ".swiper-button-next",
			prevEl: ".swiper-button-prev",
		},
		loop: true,
		breakpoints: {
			320: {
				slidesPerView: 1,
			},
			576: {
				slidesPerView: 2,
			},
			768: {
				slidesPerView: 3,
			},
			992: {
				slidesPerView: 3,
			},
			1300: {
				slidesPerView: 4,
			},
		}
		});
	}

	// Visit Slider
	if(document.querySelectorAll('.visit-slider').length > 0) {
		var visitSwiper = new Swiper(".visit-slider", {
			slidesPerView: 1,
			spaceBetween: 24,
			speed: 2000,
			navigation: {
				nextEl: ".visit-next",
				prevEl: ".visit-prev",
			},
			loop: true,
			breakpoints: {
				576: { slidesPerView: 1 },
				768: { slidesPerView: 1 },
				992: { slidesPerView: 2 },
				1300: { slidesPerView: 3 },
			}
		});
	}

	// DateRange
	var reportRangeEl = document.getElementById('reportrange');
	if (reportRangeEl && typeof flatpickr !== 'undefined') {
		var today = new Date();
		
		var tomorrow = new Date();
		tomorrow.setDate(today.getDate() + 1);

		var fp = flatpickr(reportRangeEl, {
			mode: 'range',
			dateFormat: 'd M y',
			defaultDate: [today, tomorrow], 
			onReady: function(selectedDates, dateStr) {
				reportRangeEl.value = dateStr || '';
			},
			onChange: function(selectedDates, dateStr) {
				reportRangeEl.value = dateStr || '';
			}
		});
	}

	
})();
