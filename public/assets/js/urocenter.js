/* ==========================================================================
   Clínica UroCenter — Helpers compartidos de frontend
   JavaScript nativo (sin jQuery). Ver docs/analisis/analisis-arquitectura-flowstock.md §14
   ========================================================================== */
(function (window, document) {
    'use strict';

    const Urocenter = {};

    /* ------------------------------------------------------------------ *
     * Utilidades básicas
     * ------------------------------------------------------------------ */

    Urocenter.csrfToken = function () {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    Urocenter.escapeHtml = function (value) {
        if (value === null || value === undefined) return '';
        return String(value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    };

    /* ------------------------------------------------------------------ *
     * Peticiones AJAX (fetch + CSRF + manejo de 403/422/500)
     * ------------------------------------------------------------------ */

    Urocenter.request = async function (url, options) {
        options = options || {};
        const fetchOptions = {
            method: options.method || 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        };

        if (options.body instanceof FormData) {
            fetchOptions.body = options.body;
            fetchOptions.headers['X-CSRF-TOKEN'] = Urocenter.csrfToken();
        } else if (options.body) {
            fetchOptions.body = JSON.stringify(options.body);
            fetchOptions.headers['Content-Type'] = 'application/json';
            fetchOptions.headers['X-CSRF-TOKEN'] = Urocenter.csrfToken();
        }

        const response = await fetch(url, fetchOptions);
        let payload = {};
        try {
            payload = await response.json();
        } catch (error) {
            payload = {};
        }

        return { ok: response.ok, status: response.status, payload: payload };
    };

    /* ------------------------------------------------------------------ *
     * Notificaciones (SweetAlert2 del tema)
     * ------------------------------------------------------------------ */

    Urocenter.notify = function (icon, title, text) {
        if (!window.Swal) {
            window.alert(title + (text ? '\n' + text : ''));
            return;
        }
        window.Swal.fire({
            icon: icon,
            title: title,
            text: text || '',
            confirmButtonText: 'Aceptar',
        });
    };

    Urocenter.success = function (message) {
        Urocenter.notify('success', 'Listo', message || 'Operación realizada correctamente.');
    };

    Urocenter.error = function (message) {
        Urocenter.notify('error', 'No se pudo completar', message || 'Ocurrió un problema inesperado.');
    };

    /**
     * Muestra el error de una petición: 403, 422 (por campo) o 500.
     */
    Urocenter.showRequestError = function (result, form) {
        const payload = result.payload || {};

        if (result.status === 403) {
            Urocenter.error(payload.message || 'No tiene permisos para realizar esta acción.');
            return;
        }

        if (result.status === 422 && payload.errors) {
            Object.keys(payload.errors).forEach(function (field) {
                const input = form ? form.querySelector('[name="' + field + '"]') : null;
                const feedback = form ? form.querySelector('.' + field + '-error') : null;
                if (input) input.classList.add('is-invalid');
                if (feedback) {
                    feedback.textContent = payload.errors[field][0];
                    feedback.classList.add('d-block');
                }
            });
            Urocenter.error(payload.message || 'Revise los datos ingresados.');
            return;
        }

        Urocenter.error(payload.message || 'Ocurrió un error en el servidor.');
    };

    Urocenter.clearFormErrors = function (form) {
        if (!form) return;
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('d-block');
        });
    };

    /* ------------------------------------------------------------------ *
     * Bloqueo de botón (anti doble submit)
     * ------------------------------------------------------------------ */

    Urocenter.lockButton = function (button, text) {
        if (!button) return function () {};
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = text || 'Procesando...';
        return function () {
            button.disabled = false;
            button.innerHTML = original;
        };
    };

    /* ------------------------------------------------------------------ *
     * Tabla con datos remotos
     * Replica la estructura DOM que espera el CSS del tema
     * (dataTables_wrapper, dataTables_filter, dataTables_length,
     *  dataTables_info, dataTables_paginate) sin requerir jQuery.
     *
     * Uso:
     *   Urocenter.dataTable({
     *       table: '#datatable-empresa',
     *       url:   '/configuracion/empresa/data',
     *       pageSize: 10,
     *       renderRow: (item, can) => '<tr>...</tr>',
     *       onLoaded: (payload) => { ... }
     *   });
     * ------------------------------------------------------------------ */

    Urocenter.dataTable = function (config) {
        const table = typeof config.table === 'string' ? document.querySelector(config.table) : config.table;
        if (!table) return null;

        const tbody = table.querySelector('tbody');
        const thead = table.querySelector('thead');
        if (!tbody || !thead) return null;

        const headers = Array.from(thead.querySelectorAll('th'));
        let rows = [];
        let filtered = [];
        let sortCol = -1;
        let sortDir = 'asc';
        let page = 1;
        let pageSize = config.pageSize || 10;
        let can = {};
        let wrapped = false;

        let searchInput, lengthSelect, infoEl, paginateUl;

        /* --- Construye (una sola vez) el andamiaje del DataTable --- */
        function buildWrapper() {
            if (wrapped) return;
            wrapped = true;

            const anchor = table.closest('.table-responsive') || table;
            const wrapper = document.createElement('div');
            wrapper.className = 'dataTables_wrapper dt-bootstrap5 urocenter-table-wrapper';

            const topRow = document.createElement('div');
            topRow.className = 'row';
            topRow.innerHTML =
                '<div class="col-sm-12 col-md-6">' +
                '  <div class="dataTables_length">' +
                '    <label>Mostrar' +
                '      <select class="form-select form-select-sm d-inline-block w-auto mx-1">' +
                '        <option value="10">10</option>' +
                '        <option value="25">25</option>' +
                '        <option value="50">50</option>' +
                '        <option value="100">100</option>' +
                '      </select> registros' +
                '    </label>' +
                '  </div>' +
                '</div>' +
                '<div class="col-sm-12 col-md-6">' +
                '  <div class="dataTables_filter">' +
                '    <label><input type="search" class="form-control ms-0 form-control-sm" placeholder="Buscar..."></label>' +
                '  </div>' +
                '</div>';

            const bottomRow = document.createElement('div');
            bottomRow.className = 'row mt-3';
            bottomRow.innerHTML =
                '<div class="col-sm-12 col-md-5"><div class="dataTables_info"></div></div>' +
                '<div class="col-sm-12 col-md-7">' +
                '  <div class="dataTables_paginate paging_simple_numbers">' +
                '    <ul class="pagination pagination-boxed mb-0 justify-content-end"></ul>' +
                '  </div>' +
                '</div>';

            anchor.parentNode.insertBefore(wrapper, anchor);
            wrapper.appendChild(topRow);
            wrapper.appendChild(anchor);
            wrapper.appendChild(bottomRow);

            searchInput = topRow.querySelector('input[type="search"]');
            lengthSelect = topRow.querySelector('select');
            infoEl = bottomRow.querySelector('.dataTables_info');
            paginateUl = bottomRow.querySelector('.pagination');

            lengthSelect.value = String(pageSize);

            headers.forEach(function (th, index) {
                th.classList.add('sorting');
                th.addEventListener('click', function () {
                    if (sortCol === index) {
                        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortCol = index;
                        sortDir = 'asc';
                    }
                    headers.forEach(function (h) {
                        h.classList.remove('sorting_asc', 'sorting_desc');
                    });
                    th.classList.add(sortDir === 'asc' ? 'sorting_asc' : 'sorting_desc');
                    th.classList.remove('sorting');
                    render();
                });
            });

            searchInput.addEventListener('input', function () {
                page = 1;
                render();
            });

            lengthSelect.addEventListener('change', function () {
                pageSize = parseInt(lengthSelect.value, 10) || 10;
                page = 1;
                render();
            });
        }

        function applyFilterAndSort() {
            const term = (searchInput.value || '').trim().toLowerCase();
            filtered = rows.filter(function (row) {
                return !term || row.textContent.toLowerCase().indexOf(term) !== -1;
            });

            if (sortCol >= 0) {
                const direction = sortDir === 'asc' ? 1 : -1;
                filtered.sort(function (a, b) {
                    const av = a.children[sortCol] ? a.children[sortCol].textContent.trim() : '';
                    const bv = b.children[sortCol] ? b.children[sortCol].textContent.trim() : '';
                    const an = parseFloat(av.replace(/[^0-9.-]/g, ''));
                    const bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
                    const numeric = !isNaN(an) && !isNaN(bn) && /^[\d,.\s-]+$/.test(av) && /^[\d,.\s-]+$/.test(bv);
                    return numeric ? (an - bn) * direction : av.localeCompare(bv) * direction;
                });
            }
        }

        function renderPagination(totalPages) {
            paginateUl.innerHTML = '';

            function addButton(label, targetPage, disabled, active) {
                const li = document.createElement('li');
                li.className = 'paginate_button page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                const link = document.createElement('a');
                link.href = 'javascript:void(0);';
                link.className = 'page-link';
                link.innerHTML = label;
                if (!disabled) {
                    link.addEventListener('click', function () {
                        page = targetPage;
                        render();
                    });
                }
                li.appendChild(link);
                paginateUl.appendChild(li);
            }

            if (totalPages <= 1) return;

            addButton('<i class="ti ti-chevron-left"></i>', page - 1, page <= 1, false);
            for (let p = 1; p <= totalPages; p++) {
                addButton(String(p), p, false, p === page);
            }
            addButton('<i class="ti ti-chevron-right"></i>', page + 1, page >= totalPages, false);
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
            filtered.slice(start - 1, end).forEach(function (row) {
                tbody.appendChild(row);
            });

            infoEl.textContent = total === 0
                ? 'Sin registros'
                : 'Mostrando ' + start + ' a ' + end + ' de ' + total + ' registros';

            renderPagination(totalPages);
        }

        const api = {
            /** Vuelve a cargar los datos desde el servidor. */
            reload: async function () {
                const result = await Urocenter.request(config.url);

                if (!result.ok) {
                    Urocenter.showRequestError(result);
                    return;
                }

                can = result.payload.can || {};
                rows = (result.payload.data || []).map(function (item, index) {
                    const html = config.renderRow(item, can, index);
                    const template = document.createElement('tbody');
                    template.innerHTML = html;
                    return template.firstElementChild;
                });

                buildWrapper();
                filtered = rows.slice();
                render();

                if (typeof config.onLoaded === 'function') {
                    config.onLoaded(result.payload);
                }
            },
            getCan: function () {
                return can;
            },
            getRows: function () {
                return rows;
            },
        };

        api.reload();
        return api;
    };

    window.Urocenter = Urocenter;

    /* ------------------------------------------------------------------ *
     * Limpieza de elementos de demostración del tema
     * ------------------------------------------------------------------ */

    document.addEventListener('DOMContentLoaded', function () {
        // El tema inyecta un panel "Theme Customizer" (demostración) con imágenes
        // de vista previa en rutas relativas, que fallan en rutas profundas.
        // No se usa en UroCenter: se retira del DOM.
        document.querySelectorAll('.toggle-theme, #theme-settings-offcanvas').forEach(function (elemento) {
            elemento.remove();
        });
    });
})(window, document);
