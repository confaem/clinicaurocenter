/*
Vanilla replacement for assets/plugins/daterangepicker/daterangepicker.js (jQuery + moment.js plugin).
Renders the exact same DOM structure and class names as the original plugin so the existing
daterangepicker.css and assets/scss/plugins/_daterange.scss overrides apply unchanged.
Only the features actually used in this project are implemented: range selection (no
singleDatePicker, no timePicker, no showDropdowns, no week numbers, no min/max date).
*/
(function (window, document) {
	"use strict";

	const MONTH_NAMES = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	const MONTH_NAMES_LONG = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	const DAYS_MIN = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];

	function startOfDay(d) { const r = new Date(d); r.setHours(0, 0, 0, 0); return r; }
	function endOfDay(d) { const r = new Date(d); r.setHours(23, 59, 59, 999); return r; }
	function addDays(d, n) { const r = new Date(d); r.setDate(r.getDate() + n); return r; }
	function addMonths(d, n) { const r = new Date(d); r.setMonth(r.getMonth() + n); return r; }
	function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
	function endOfMonth(d) { return endOfDay(new Date(d.getFullYear(), d.getMonth() + 1, 0)); }
	function isSameDay(a, b) { return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); }
	function fmtShort(d) { return `${d.getDate()} ${MONTH_NAMES[d.getMonth()]} ${String(d.getFullYear()).slice(-2)}`; }
	function daysInMonth(year, month) { return new Date(year, month + 1, 0).getDate(); }

	function DateRangePicker(el, options, callback) {
		this.el = el;
		this.callback = callback || function () { };
		this.ranges = options.ranges || {};
		this.startDate = startOfDay(options.startDate || new Date());
		this.endDate = endOfDay(options.endDate || new Date());
		this.oldStartDate = this.startDate;
		this.oldEndDate = this.endDate;
		this.leftMonth = startOfMonth(this.startDate);
		this.rightMonth = addMonths(this.leftMonth, 1);
		this.chosenLabel = null;
		this.picking = null; // 'start' | 'end' | null, while selecting a custom range
		this.isShowing = false;

		this._buildContainer();
		this._boundOutsideClick = this._onOutsideClick.bind(this);
		this.el.addEventListener('click', (e) => { e.stopPropagation(); this.show(); });
	}

	DateRangePicker.prototype._buildContainer = function () {
		const container = document.createElement('div');
		container.className = 'daterangepicker ltr show-ranges';
		container.style.display = 'none';
		container.style.position = 'absolute';
		container.style.zIndex = '3001';

		const ranges = document.createElement('div');
		ranges.className = 'ranges';
		const ul = document.createElement('ul');
		Object.keys(this.ranges).forEach((label) => {
			const li = document.createElement('li');
			li.textContent = label;
			li.setAttribute('data-range-key', label);
			li.addEventListener('click', () => this._clickRange(label));
			ul.appendChild(li);
		});
		const customLi = document.createElement('li');
		customLi.textContent = 'Custom Range';
		customLi.setAttribute('data-range-key', 'Custom Range');
		customLi.addEventListener('click', () => this._clickRange('Custom Range'));
		ul.appendChild(customLi);
		ranges.appendChild(ul);
		container.appendChild(ranges);

		const left = document.createElement('div');
		left.className = 'drp-calendar left';
		left.innerHTML = '<div class="calendar-table"></div><div class="calendar-time"></div>';
		container.appendChild(left);

		const right = document.createElement('div');
		right.className = 'drp-calendar right';
		right.innerHTML = '<div class="calendar-table"></div><div class="calendar-time"></div>';
		container.appendChild(right);

		const buttons = document.createElement('div');
		buttons.className = 'drp-buttons';
		buttons.innerHTML =
			'<span class="drp-selected"></span>' +
			'<button class="cancelBtn btn btn-sm btn-default" type="button">Cancel</button> ' +
			'<button class="applyBtn btn btn-sm btn-primary" type="button">Apply</button>';
		container.appendChild(buttons);

		document.body.appendChild(container);
		this.container = container;
		this.leftTable = left.querySelector('.calendar-table');
		this.rightTable = right.querySelector('.calendar-table');
		this.applyBtn = buttons.querySelector('.applyBtn');
		this.cancelBtn = buttons.querySelector('.cancelBtn');

		this.applyBtn.addEventListener('click', () => this._clickApply());
		this.cancelBtn.addEventListener('click', () => this._clickCancel());
	};

	DateRangePicker.prototype._renderCalendar = function (side) {
		const month = side === 'left' ? this.leftMonth : this.rightMonth;
		const year = month.getFullYear();
		const m = month.getMonth();
		const firstDay = new Date(year, m, 1);
		const dayOfWeek = firstDay.getDay();
		const prevMonthDays = daysInMonth(year, m - 1 < 0 ? 11 : m - 1);
		const prevMonthYear = m - 1 < 0 ? year - 1 : year;
		const prevMonth = m - 1 < 0 ? 11 : m - 1;

		const cells = [];
		let startDay = prevMonthDays - dayOfWeek + 1;
		if (dayOfWeek === 0) startDay = prevMonthDays - 6;
		let cursor = new Date(prevMonthYear, prevMonth, startDay);
		for (let i = 0; i < 42; i++) {
			cells.push(new Date(cursor));
			cursor = addDays(cursor, 1);
		}

		const showPrev = side === 'left';
		const showNext = side === 'right';

		let html = '<table class="table-condensed"><thead><tr>';
		html += showPrev ? '<th class="prev available"><span></span></th>' : '<th></th>';
		html += `<th colspan="5" class="month">${MONTH_NAMES_LONG[m]} ${year}</th>`;
		html += showNext ? '<th class="next available"><span></span></th>' : '<th></th>';
		html += '</tr><tr>';
		DAYS_MIN.forEach((d) => { html += `<th>${d}</th>`; });
		html += '</tr></thead><tbody>';

		const today = new Date();
		for (let row = 0; row < 6; row++) {
			html += '<tr>';
			for (let col = 0; col < 7; col++) {
				const date = cells[row * 7 + col];
				const classes = [];
				if (isSameDay(date, today)) classes.push('today');
				const dow = date.getDay();
				if (dow === 0 || dow === 6) classes.push('weekend');
				if (date.getMonth() !== m) classes.push('off', 'ends');

				const isStart = isSameDay(date, this.startDate);
				const isEnd = this.endDate && isSameDay(date, this.endDate);
				if (isStart) classes.push('active', 'start-date');
				if (isEnd) classes.push('active', 'end-date');
				if (this.endDate && date > this.startDate && date < this.endDate) classes.push('in-range');

				if (!classes.includes('off')) classes.push('available');

				html += `<td class="${classes.join(' ')}" data-date="${date.getFullYear()}-${date.getMonth()}-${date.getDate()}">${date.getDate()}</td>`;
			}
			html += '</tr>';
		}
		html += '</tbody></table>';

		const table = side === 'left' ? this.leftTable : this.rightTable;
		table.innerHTML = html;

		table.querySelectorAll('td.available').forEach((td) => {
			td.addEventListener('click', () => {
				const [y, mo, da] = td.getAttribute('data-date').split('-').map(Number);
				this._clickDate(new Date(y, mo, da));
			});
		});
		const prevEl = table.querySelector('th.prev');
		if (prevEl) prevEl.addEventListener('click', () => this._clickPrev());
		const nextEl = table.querySelector('th.next');
		if (nextEl) nextEl.addEventListener('click', () => this._clickNext());
	};

	DateRangePicker.prototype._updateCalendars = function () {
		this._renderCalendar('left');
		this._renderCalendar('right');
	};

	DateRangePicker.prototype._clickPrev = function () {
		this.leftMonth = addMonths(this.leftMonth, -1);
		this.rightMonth = addMonths(this.rightMonth, -1);
		this._updateCalendars();
	};

	DateRangePicker.prototype._clickNext = function () {
		this.leftMonth = addMonths(this.leftMonth, 1);
		this.rightMonth = addMonths(this.rightMonth, 1);
		this._updateCalendars();
	};

	DateRangePicker.prototype._clickDate = function (date) {
		if (this.picking !== 'end') {
			this.startDate = startOfDay(date);
			this.endDate = null;
			this.picking = 'end';
		} else {
			if (date < this.startDate) {
				this.endDate = endOfDay(this.startDate);
				this.startDate = startOfDay(date);
			} else {
				this.endDate = endOfDay(date);
			}
			this.picking = null;
		}
		this.chosenLabel = null;
		this._updateCalendars();
		this._updateSelectedText();
	};

	DateRangePicker.prototype._clickRange = function (label) {
		this.chosenLabel = label;
		if (label === 'Custom Range') {
			this._showCalendars();
			return;
		}
		const dates = this.ranges[label];
		this.startDate = startOfDay(dates[0]);
		this.endDate = endOfDay(dates[1]);
		this.leftMonth = startOfMonth(this.startDate);
		this.rightMonth = addMonths(this.leftMonth, 1);
		this._hideCalendars();
		this._updateCalendars();
		this._clickApply();
	};

	DateRangePicker.prototype._showCalendars = function () {
		this.container.classList.add('show-calendar');
		this._updateCalendars();
		this._updateSelectedText();
	};

	DateRangePicker.prototype._hideCalendars = function () {
		this.container.classList.remove('show-calendar');
	};

	DateRangePicker.prototype._updateSelectedText = function () {
		const selected = this.container.querySelector('.drp-selected');
		if (!selected) return;
		selected.textContent = this.endDate ? `${fmtShort(this.startDate)} - ${fmtShort(this.endDate)}` : '';
	};

	DateRangePicker.prototype._clickApply = function () {
		this.oldStartDate = this.startDate;
		this.oldEndDate = this.endDate || endOfDay(this.startDate);
		this.endDate = this.oldEndDate;
		this.hide();
		this.callback(this.startDate, this.endDate, this.chosenLabel);
	};

	DateRangePicker.prototype._clickCancel = function () {
		this.startDate = this.oldStartDate;
		this.endDate = this.oldEndDate;
		this.picking = null;
		this._hideCalendars();
		this.hide();
	};

	DateRangePicker.prototype._onOutsideClick = function (e) {
		if (this.container.contains(e.target) || e.target === this.el || this.el.contains(e.target)) return;
		this._clickCancel();
	};

	DateRangePicker.prototype.show = function () {
		if (this.isShowing) return;
		this.isShowing = true;
		this._updateCalendars();
		this._updateSelectedText();
		this.container.style.display = 'block';

		const rect = this.el.getBoundingClientRect();
		const top = rect.bottom + window.scrollY + 7;
		let left = rect.left + window.scrollX;
		const containerWidth = this.container.offsetWidth;
		if (left + containerWidth > window.scrollX + document.documentElement.clientWidth) {
			left = rect.right + window.scrollX - containerWidth;
		}
		this.container.style.top = top + 'px';
		this.container.style.left = Math.max(left, window.scrollX + 4) + 'px';

		document.addEventListener('click', this._boundOutsideClick, true);
	};

	DateRangePicker.prototype.hide = function () {
		if (!this.isShowing) return;
		this.isShowing = false;
		this.container.style.display = 'none';
		document.removeEventListener('click', this._boundOutsideClick, true);
	};

	window.initDateRangePicker = function (el, options, callback) {
		return new DateRangePicker(el, options, callback);
	};
})(window, document);
