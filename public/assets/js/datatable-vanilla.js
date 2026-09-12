/*
Vanilla replacement for the jQuery DataTables plugin, scoped to the single
`.datatable` table used on data-tables.html. Reproduces the same DOM/class
structure the plugin generated (dataTables_wrapper, dataTables_filter,
dataTables_length, dataTables_info, dataTables_paginate, sorting/sorting_asc/
sorting_desc on <th>) so the existing style.css rules apply unchanged.
Implements: search filter, column sort, row-per-page, numbered pagination -
the same features the original init (`bFilter`, `ordering`, custom pagination
icons, custom length/info text) used.
*/
document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	document.querySelectorAll('table.datatable').forEach((table) => {
		const thead = table.querySelector('thead');
		const tbody = table.querySelector('tbody');
		const headers = Array.from(thead.querySelectorAll('th'));
		const allRows = Array.from(tbody.querySelectorAll('tr'));

		let filtered = allRows.slice();
		let sortCol = -1;
		let sortDir = 'asc';
		let pageSize = 10;
		let page = 1;

		// Wrapper + top controls (length + filter), matching dataTables_wrapper markup.
		// If the table sits inside a `.table-responsive` scroller, wrap that
		// element instead of the table itself so the length/filter/pagination
		// controls stay outside the horizontally-scrolling area.
		const responsiveAncestor = table.closest('.table-responsive');
		const anchor = responsiveAncestor || table;
		const wrapper = document.createElement('div');
		wrapper.className = 'dataTables_wrapper dt-bootstrap5';
		anchor.parentNode.insertBefore(wrapper, anchor);

		const topRow = document.createElement('div');
		topRow.className = 'row';
		topRow.innerHTML = `
			<div class="col-sm-12 col-md-6">
				<div class="dataTables_filter">
					<label>
						<input type="search" class="form-control ms-0 form-control-sm" placeholder="Search">
					</label>
				</div>
			</div>`;
		wrapper.appendChild(topRow);
		wrapper.appendChild(anchor);

		const bottomRow = document.createElement('div');
		bottomRow.className = 'row';
		bottomRow.innerHTML = `
			<div class="col-sm-12 col-md-5">
				<div class="dataTables_info"></div>
				<div class="dataTables_length">
					<label>Row Per Page
						<select class="form-select form-select-sm d-inline-block w-auto mx-1">
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
						Entries
					</label>
				</div>
			</div>
			<div class="col-sm-12 col-md-7">
				<div class="dataTables_paginate paging_simple_numbers">
					<ul class="pagination pagination-boxed mb-0"></ul>
				</div>
			</div>`;
		wrapper.appendChild(bottomRow);

		const lengthSelect = bottomRow.querySelector('select');
		const searchInput = topRow.querySelector('input[type="search"]');
		const infoEl = bottomRow.querySelector('.dataTables_info');
		const paginateUl = bottomRow.querySelector('.pagination');

		headers.forEach((th, i) => {
			th.classList.add('sorting');
			th.addEventListener('click', () => {
				if (sortCol === i) {
					sortDir = sortDir === 'asc' ? 'desc' : 'asc';
				} else {
					sortCol = i;
					sortDir = 'asc';
				}
				headers.forEach((h) => h.classList.remove('sorting_asc', 'sorting_desc'));
				th.classList.add(sortDir === 'asc' ? 'sorting_asc' : 'sorting_desc');
				th.classList.remove('sorting');
				render();
			});
		});

		function applyFilterAndSort() {
			const term = searchInput.value.trim().toLowerCase();
			filtered = allRows.filter((row) => !term || row.textContent.toLowerCase().includes(term));

			if (sortCol >= 0) {
				const dir = sortDir === 'asc' ? 1 : -1;
				filtered.sort((a, b) => {
					const av = a.children[sortCol].textContent.trim();
					const bv = b.children[sortCol].textContent.trim();
					const an = parseFloat(av.replace(/[^0-9.-]/g, ''));
					const bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
					if (!isNaN(an) && !isNaN(bn) && /^[$]?[\d,.\s-]+$/.test(av) && /^[$]?[\d,.\s-]+$/.test(bv)) {
						return (an - bn) * dir;
					}
					return av.localeCompare(bv) * dir;
				});
			}
		}

		function renderPagination(totalPages) {
			paginateUl.innerHTML = '';

			const addBtn = (label, targetPage, disabled, active) => {
				const li = document.createElement('li');
				li.className = 'paginate_button page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
				const a = document.createElement('a');
				a.href = 'javascript:void(0);';
				a.className = 'page-link';
				a.innerHTML = label;
				if (!disabled) {
					a.addEventListener('click', () => { page = targetPage; render(); });
				}
				li.appendChild(a);
				paginateUl.appendChild(li);
			};

			addBtn('&laquo;', 1, page <= 1, false);
			addBtn('<i class="ti ti-arrow-left"></i>', page - 1, page <= 1, false);
			for (let p = 1; p <= totalPages; p++) {
				addBtn(String(p), p, false, p === page);
			}
			addBtn('<i class="ti ti-arrow-right"></i>', page + 1, page >= totalPages, false);
			addBtn('&raquo;', totalPages, page >= totalPages, false);
		}

		function render() {
			applyFilterAndSort();

			const total = filtered.length;
			const totalPages = Math.max(1, Math.ceil(total / pageSize));
			if (page > totalPages) page = totalPages;
			if (page < 1) page = 1;

			const start = total === 0 ? 0 : (page - 1) * pageSize + 1;
			const end = Math.min(page * pageSize, total);

			tbody.innerHTML = '';
			filtered.slice(start - 1, end).forEach((row) => tbody.appendChild(row));

			infoEl.textContent = total === 0 ? 'No entries' : `${start} - ${end} of ${total} items`;
			renderPagination(totalPages);
		}

		lengthSelect.addEventListener('change', () => {
			pageSize = parseInt(lengthSelect.value, 10);
			page = 1;
			render();
		});
		searchInput.addEventListener('input', () => {
			page = 1;
			render();
		});

		render();
	});
});
