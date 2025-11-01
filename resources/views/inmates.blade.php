@extends('layouts.master')

@section('page_title', 'Narapidana')

@push('styles')
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .image-input-wrapper {
            background-size: cover;
            background-position: center;
        }
        .image-input.image-input-circle .image-input-wrapper { border-radius: 50%; }
    </style>
@endpush

@section('content')
    <div class="card m-6">
        <div class="card-header flex-wrap d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap my-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="inmatesSearch" class="form-control form-control-sm" placeholder="Cari napi...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button id="btnAddInmate" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Tambah Napi
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="inmatesTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Foto</th>
                            <th>No. Register</th>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Jenis Kelamin</th>
                            <th>Blok Sel</th>
                            <th>Status</th>
                            <th style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inmates as $i)
                            <tr data-id="{{ $i->id }}">
                                <td class="photo">
                                    @php
                                        $avatar = $i->photo_url;
                                        $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($i->name) . '&background=6c757d&color=fff&size=64';
                                        $fallbackFull = 'https://ui-avatars.com/api/?name=' . urlencode($i->name) . '&background=6c757d&color=fff&size=512';
                                    @endphp
                                    <a href="#" class="avatar-view" data-full="{{ $avatar ?: $fallbackFull }}" title="Lihat foto">
                                        <img src="{{ $avatar ?: $fallback }}" alt="{{ $i->name }}" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
                                    </a>
                                </td>
                                <td class="register_no">{{ $i->register_no }}</td>
                                <td class="name">{{ $i->name }}</td>
                                <td class="nik">{{ $i->nik }}</td>
                                <td class="gender text-capitalize">{{ $i->gender }}</td>
                                <td class="cell_block">{{ $i->cell_block ?? '-' }}</td>
                                <td class="status text-capitalize">{{ $i->status }}</td>
                                <td>
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $i->id }}">
                                        <center><i class="bi bi-pencil-square"></i></center>
                                    </button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $i->id }}">
                                        <center><i class="bi bi-trash"></i></center>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Preview Photo -->
    <div class="modal fade" id="photoPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pratinjau Foto</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center justify-content-center p-0">
                    <img id="photoPreviewImg" src="" alt="Foto" style="max-width: 100%; max-height: 80vh; object-fit: contain;" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="inmateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="inmateForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inmateModalTitle">Tambah Napi</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>

                        <input type="hidden" id="inmate_id" name="id">

                        <div class="row">
                            <div class="col-md-4 mb-5">
                                <label class="form-label d-block">Foto</label>
                                <div class="image-input image-input-circle" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                    <div id="photo_wrapper" class="image-input-wrapper w-125px h-125px"></div>
                                    <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Ubah foto">
                                        <i class="bi bi-pencil"></i>
                                        <input type="file" name="photo" id="photo" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="photo_remove" />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Batal">
                                        <i class="bi bi-x"></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Hapus foto">
                                        <i class="bi bi-x"></i>
                                    </span>
                                </div>
                                <div class="invalid-feedback d-block" data-field="photo"></div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-5">
                                    <label class="form-label">No. Register</label>
                                    <input type="text" name="register_no" id="register_no" class="form-control" required>
                                    <div class="invalid-feedback" data-field="register_no"></div>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                    <div class="invalid-feedback" data-field="name"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" id="nik" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" pattern="[0-9]*" title="Isikan hanya dengan angka">
                            <div class="invalid-feedback" data-field="nik"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" id="gender" class="form-select" required>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                    <option value="other">Lainnya</option>
                                </select>
                                <div class="invalid-feedback" data-field="gender"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Blok Sel</label>
                                <input type="text" name="cell_block" id="cell_block" class="form-control">
                                <div class="invalid-feedback" data-field="cell_block"></div>
                            </div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="birth_date" id="birth_date" class="form-control">
                            <div class="invalid-feedback" data-field="birth_date"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active">active</option>
                                <option value="released">released</option>
                            </select>
                            <div class="invalid-feedback" data-field="status"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Catatan (opsional)"></textarea>
                            <div class="invalid-feedback" data-field="notes"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveInmate">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const routes = {
            index: '{{ route('inmates.index') }}',
            store: '{{ route('inmates.store') }}',
            show: (id) => '{{ route('inmates.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
            update: (id) => '{{ route('inmates.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
            destroy: (id) => '{{ route('inmates.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        };

        const inmateModalEl = document.getElementById('inmateModal');
        const inmateModal = new bootstrap.Modal(inmateModalEl);

        const inmateForm = document.getElementById('inmateForm');
        const inmateId = document.getElementById('inmate_id');
        const registerNo = document.getElementById('register_no');
        const nameInput = document.getElementById('name');
        const nikInput = document.getElementById('nik');
        const gender = document.getElementById('gender');
        const cellBlock = document.getElementById('cell_block');
        const birthDate = document.getElementById('birth_date');
        const statusSelect = document.getElementById('status');
        const notes = document.getElementById('notes');
        const photoInput = document.getElementById('photo');
        const photoWrapper = document.getElementById('photo_wrapper');
        const photoRemove = document.querySelector('input[name="photo_remove"]');

        const table = $('#inmatesTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            ordering: true,
            searching: true
        });

        // Preview photo fullscreen
        document.addEventListener('click', function(e) {
            const a = e.target.closest('a.avatar-view');
            if (!a) return;
            e.preventDefault();
            const fullUrl = a.getAttribute('data-full');
            const img = document.getElementById('photoPreviewImg');
            if (img) img.src = fullUrl || '';
            const modalEl = document.getElementById('photoPreviewModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        // Search
        document.getElementById('inmatesSearch').addEventListener('input', function() {
            table.search(this.value).draw();
        });

        function clearValidation() {
            inmateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            inmateForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        document.getElementById('btnAddInmate').addEventListener('click', () => {
            clearValidation();
            inmateForm.reset();
            inmateId.value = '';
            statusSelect.value = 'active';
            photoWrapper.style.backgroundImage = "url('{{ asset('assets/media/avatars/blank.png') }}')";
            if (photoRemove) photoRemove.value = '';
            document.getElementById('inmateModalTitle').textContent = 'Tambah Napi';
            inmateModal.show();
        });

        $('#inmatesTable').on('click', '.btnEdit', function() {
            clearValidation();
            const id = $(this).data('id');
            inmateForm.reset();
            inmateId.value = id;
            document.getElementById('inmateModalTitle').textContent = 'Edit Napi';
            inmateModal.show();

            const loader = document.getElementById('modalLoader');
            loader.classList.remove('d-none');
            [...inmateForm.elements].forEach(el => el.disabled = true);

            $.ajax({
                url: routes.show(id),
                method: 'GET',
                success: function(resp) {
                    const d = resp.data || {};
                    registerNo.value = d.register_no || '';
                    nameInput.value = d.name || '';
                    gender.value = d.gender || 'male';
                    cellBlock.value = d.cell_block || '';
                    // HTML input type="date" butuh format YYYY-MM-DD
                    birthDate.value = d.birth_date ? String(d.birth_date).slice(0, 10) : '';
                    nikInput.value = d.nik || '';
                    statusSelect.value = d.status || 'active';
                    notes.value = d.notes || '';
                    const imgUrl = d.photo_url || `{{ asset('assets/media/avatars/blank.png') }}`;
                    photoWrapper.style.backgroundImage = `url('${imgUrl}')`;
                    if (photoRemove) photoRemove.value = '';
                },
                error: function() {
                    toastr?.error?.('Gagal memuat data napi. Coba lagi.');
                    inmateModal.hide();
                },
                complete: function() {
                    loader.classList.add('d-none');
                    [...inmateForm.elements].forEach(el => el.disabled = false);
                }
            });
        });

        inmateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidation();

            const id = inmateId.value;
            const formData = new FormData();
            formData.append('register_no', registerNo.value);
            formData.append('name', nameInput.value);
            formData.append('gender', gender.value);
            formData.append('cell_block', cellBlock.value);
            if (birthDate.value) formData.append('birth_date', birthDate.value);
            formData.append('nik', nikInput.value);
            formData.append('status', statusSelect.value);
            formData.append('notes', notes.value);
            if (photoInput.files && photoInput.files[0]) {
                formData.append('photo', photoInput.files[0]);
                if (photoRemove) photoRemove.value = '';
            }
            if (photoRemove) {
                formData.append('photo_remove', photoRemove.value);
            }

            const ajaxUrl = id ? routes.update(id) : routes.store;
            if (id) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    inmateModal.hide();
                    toastr?.success?.(resp.message || 'Berhasil disimpan');
                    window.location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errs = xhr.responseJSON.errors;
                        Object.keys(errs).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) input.classList.add('is-invalid');
                            const fb = inmateForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
                            if (fb) fb.textContent = errs[field][0];
                        });
                        return;
                    }
                    toastr?.error?.('Terjadi kesalahan. Coba lagi.');
                }
            });
        });

        $('#inmatesTable').on('click', '.btnDelete', function() {
            const id = $(this).data('id');
            const name = $(this).closest('tr').find('.name').text().trim();

            function performDelete() {
                $.ajax({
                    url: routes.destroy(id),
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    success: function(resp) {
                        if (window.Swal && Swal.fire) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: resp.message || 'Berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        toastr?.success?.(resp.message || 'Berhasil dihapus');
                        window.location.reload();
                    },
                    error: function() {
                        if (window.Swal && Swal.fire) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menghapus. Coba lagi.'
                            });
                        }
                        toastr?.error?.('Gagal menghapus. Coba lagi.');
                    }
                });
            }

            if (window.Swal && Swal.fire) {
                Swal.fire({
                    title: 'Hapus napi?',
                    html: `Apakah Anda yakin ingin menghapus <b>${name || 'data ini'}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        performDelete();
                    }
                });
            } else {
                if (confirm('Yakin ingin menghapus napi ini?')) {
                    performDelete();
                }
            }
        });
    </script>
@endpush
