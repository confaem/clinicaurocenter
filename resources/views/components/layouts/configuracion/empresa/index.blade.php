@extends('layouts.app')

@section('title', 'Empresas')
@section('page-title', 'Mantenimiento de Empresas')
@section('page-subtitle', 'Administra los datos de la clínica y sus sedes')

@section('breadcrumb')
    {{ Breadcrumbs::render('configuracion.empresa.index') }}
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                    <h4 class="card-title fw-semibold mb-0">Listado de Empresas</h4>
                    @can_do('configuracion.empresa.index', 'crear')
                        <button type="button" class="btn btn-primary" onclick="abrirModalEmpresa()">
                            <i class="ti ti-plus align-middle me-1"></i>Nueva Empresa
                        </button>
                    @endcan_do
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaEmpresas" class="table table-nowrap table-hover mb-0 w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Cód.</th>
                                    <th>Logo</th>
                                    <th>RUC</th>
                                    <th>Razón Social</th>
                                    <th>Nombre Comercial</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Contenido cargado por AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Modal Crear / Editar ===================== --}}
    <div class="modal fade" id="modalEmpresa" tabindex="-1" aria-labelledby="modalEmpresaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmpresaLabel">Datos de la empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="formEmpresa" enctype="multipart/form-data" novalidate>
                    <div class="modal-body p-4">
                        <input type="hidden" id="empresa_id" name="id">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ruc" class="form-label">RUC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ruc" name="ruc" maxlength="15" required>
                                <div class="invalid-feedback ruc-error"></div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="razon_social" class="form-label">Razón social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="razon_social" name="razon_social" maxlength="250" required>
                                <div class="invalid-feedback razon_social-error"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre_comercial" class="form-label">Nombre comercial</label>
                                <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" maxlength="250">
                                <div class="invalid-feedback nombre_comercial-error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" maxlength="250">
                                <div class="invalid-feedback direccion-error"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="50">
                                <div class="invalid-feedback telefono-error"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="150">
                                <div class="invalid-feedback email-error"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="logo_file" class="form-label">Logo (imagen)</label>
                                <input type="file" class="form-control" id="logo_file" name="logo_file" accept=".png,.jpg,.jpeg,.svg,.webp">
                                <small class="text-muted">Máx. 2 MB</small>
                                <div class="invalid-feedback logo_file-error"></div>
                            </div>
                        </div>

                        <div class="text-center d-none" id="contenedorPreviewLogo">
                            <img src="" alt="Vista previa" id="previewLogo" class="img-fluid rounded" style="max-height: 70px;">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarEmpresa">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== Modal Ver ===================== --}}
    <div class="modal fade" id="modalVerEmpresa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-building-hospital me-1"></i>Detalle de la empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="detalleEmpresa"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const empresaUrl = {
        data:   "{{ route('configuracion.empresa.data') }}",
        store:  "{{ route('configuracion.empresa.store') }}",
        base:   "{{ url('configuracion/empresa') }}",
    };

    const modalEmpresa = new bootstrap.Modal(document.getElementById('modalEmpresa'));
    const modalVerEmpresa = new bootstrap.Modal(document.getElementById('modalVerEmpresa'));
    const formEmpresa = document.getElementById('formEmpresa');

    /* ---------------- Tabla remota ---------------- */
    const tablaEmpresas = Urocenter.dataTable({
        table: '#tablaEmpresas',
        url: empresaUrl.data,
        pageSize: 10,
        renderRow: function (item, can) {
            const logo = item.logo
                ? '<img src="/storage/' + Urocenter.escapeHtml(item.logo) + '" alt="logo" class="rounded" style="height:28px;">'
                : '<span class="text-muted fs-13">—</span>';

            const estado = item.estado
                ? '<span class="badge badge-soft-success">Activo</span>'
                : '<span class="badge badge-soft-danger">Inactivo</span>';

            let acciones = '<div class="table-actions">';

            if (can.ver) {
                acciones += '<button class="btn btn-light" title="Ver" onclick="verEmpresa(' + item.id + ')"><i class="ti ti-eye"></i></button>';
            }
            if (can.editar) {
                acciones += '<button class="btn btn-light" title="Editar" onclick="editarEmpresa(' + item.id + ')"><i class="ti ti-pencil"></i></button>';
            }
            if (item.estado && can.desactivar) {
                acciones += '<button class="btn btn-light text-danger" title="Desactivar" onclick="cambiarEstadoEmpresa(' + item.id + ')"><i class="ti ti-toggle-right"></i></button>';
            } else if (!item.estado && can.activar) {
                acciones += '<button class="btn btn-light text-success" title="Activar" onclick="cambiarEstadoEmpresa(' + item.id + ')"><i class="ti ti-toggle-left"></i></button>';
            }

            acciones += '</div>';

            return '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td>' + logo + '</td>' +
                '<td>' + Urocenter.escapeHtml(item.ruc) + '</td>' +
                '<td>' + Urocenter.escapeHtml(item.razon_social) + '</td>' +
                '<td>' + Urocenter.escapeHtml(item.nombre_comercial || '—') + '</td>' +
                '<td>' + Urocenter.escapeHtml(item.telefono || '—') + '</td>' +
                '<td>' + estado + '</td>' +
                '<td>' + acciones + '</td>' +
                '</tr>';
        },
    });

    /* ---------------- Crear / Editar ---------------- */
    function abrirModalEmpresa() {
        formEmpresa.reset();
        Urocenter.clearFormErrors(formEmpresa);
        document.getElementById('empresa_id').value = '';
        document.getElementById('modalEmpresaLabel').textContent = 'Nueva empresa';
        document.getElementById('contenedorPreviewLogo').classList.add('d-none');
        modalEmpresa.show();
    }

    async function editarEmpresa(id) {
        const result = await Urocenter.request(empresaUrl.base + '/' + id + '/edit');

        if (!result.ok) {
            Urocenter.showRequestError(result, formEmpresa);
            return;
        }

        const empresa = result.payload.data;

        formEmpresa.reset();
        Urocenter.clearFormErrors(formEmpresa);

        document.getElementById('empresa_id').value = empresa.id;
        ['ruc', 'razon_social', 'nombre_comercial', 'direccion', 'telefono', 'email'].forEach(function (campo) {
            const input = document.getElementById(campo);
            if (input) input.value = empresa[campo] || '';
        });

        document.getElementById('modalEmpresaLabel').textContent = 'Editar empresa';

        const contenedor = document.getElementById('contenedorPreviewLogo');
        const preview = document.getElementById('previewLogo');
        if (empresa.logo) {
            preview.src = '/storage/' + empresa.logo;
            contenedor.classList.remove('d-none');
        } else {
            contenedor.classList.add('d-none');
        }

        modalEmpresa.show();
    }

    formEmpresa.addEventListener('submit', async function (event) {
        event.preventDefault();

        const id = document.getElementById('empresa_id').value;
        const url = id ? empresaUrl.base + '/' + id : empresaUrl.store;
        const liberar = Urocenter.lockButton(document.getElementById('btnGuardarEmpresa'), 'Guardando...');

        Urocenter.clearFormErrors(formEmpresa);

        const result = await Urocenter.request(url, {
            method: 'POST',
            body: new FormData(formEmpresa),
        });

        liberar();

        if (!result.ok) {
            Urocenter.showRequestError(result, formEmpresa);
            return;
        }

        modalEmpresa.hide();
        tablaEmpresas.reload();
        Urocenter.success(result.payload.message);
    });

    /* ---------------- Ver detalle ---------------- */
    async function verEmpresa(id) {
        const result = await Urocenter.request(empresaUrl.base + '/' + id + '/edit');

        if (!result.ok) {
            Urocenter.showRequestError(result);
            return;
        }

        const empresa = result.payload.data;
        const filas = [
            ['RUC', empresa.ruc],
            ['Razón social', empresa.razon_social],
            ['Nombre comercial', empresa.nombre_comercial],
            ['Dirección', empresa.direccion],
            ['Teléfono', empresa.telefono],
            ['Correo', empresa.email],
            ['Estado', empresa.estado ? 'Activo' : 'Inactivo'],
        ];

        document.getElementById('detalleEmpresa').innerHTML = filas.map(function (fila) {
            return '<div class="d-flex justify-content-between border-bottom py-2">' +
                '<span class="text-muted">' + fila[0] + '</span>' +
                '<span class="fw-medium">' + Urocenter.escapeHtml(fila[1] || '—') + '</span>' +
                '</div>';
        }).join('');

        modalVerEmpresa.show();
    }

    /* ---------------- Activar / Desactivar ---------------- */
    async function cambiarEstadoEmpresa(id) {
        const confirmacion = await Swal.fire({
            icon: 'question',
            title: '¿Confirmar cambio de estado?',
            text: 'La empresa cambiará entre activa e inactiva.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar',
        });

        if (!confirmacion.isConfirmed) return;

        const result = await Urocenter.request(empresaUrl.base + '/' + id + '/toggle', { method: 'POST' });

        if (!result.ok) {
            Urocenter.showRequestError(result);
            return;
        }

        tablaEmpresas.reload();
        Urocenter.success(result.payload.message);
    }

    /* ---------------- Vista previa del logo ---------------- */
    document.getElementById('logo_file').addEventListener('change', function (event) {
        const archivo = event.target.files[0];
        const contenedor = document.getElementById('contenedorPreviewLogo');
        const preview = document.getElementById('previewLogo');

        if (!archivo) {
            contenedor.classList.add('d-none');
            return;
        }

        preview.src = URL.createObjectURL(archivo);
        contenedor.classList.remove('d-none');
    });
</script>
@endpush
