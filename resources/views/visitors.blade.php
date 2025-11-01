@extends('layouts.master')

@section('page_title', 'Pengunjung')

@push('styles')
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .image-input-wrapper { background-size: cover; background-position: center; }
        .image-input.image-input-circle .image-input-wrapper { border-radius: 50%; }
    </style>
@endpush

@section('content')
    <div class="card m-6">
        <div class="card-header flex-wrap d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap my-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="visitorsSearch" class="form-control form-control-sm" placeholder="Cari pengunjung...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button id="btnAddVisitor" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Tambah Pengunjung
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="visitorsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Foto</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Telepon</th>
                            <th>Blacklist</th>
                            <th style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visitors as $v)
                            <tr data-id="{{ $v->id }}">
                                <td class="photo">
                                    @php
                                        $avatar = $v->photo_url;
                                        $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($v->name) . '&background=6c757d&color=fff&size=64';
                                        $fallbackFull = 'https://ui-avatars.com/api/?name=' . urlencode($v->name) . '&background=6c757d&color=fff&size=512';
                                    @endphp
                                    <a href="#" class="avatar-view" data-full="{{ $avatar ?: $fallbackFull }}" title="Lihat foto">
                                        <img src="{{ $avatar ?: $fallback }}" alt="{{ $v->name }}" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
                                    </a>
                                </td>
                                <td class="national_id">{{ $v->national_id ?? '-' }}</td>
                                <td class="name">{{ $v->name }}</td>
                                <td class="gender text-capitalize">{{ $v->gender ?? '-' }}</td>
                                <td class="phone">{{ $v->phone ?? '-' }}</td>
                                <td class="is_blacklisted">
                                    @if($v->is_blacklisted)
                                        <span class="badge bg-danger">Ya</span>
                                    @else
                                        <span class="badge bg-success">Tidak</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $v->id }}">
                                        <center><i class="bi bi-pencil-square"></i></center>
                                    </button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $v->id }}">
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
    <div class="modal fade" id="visitorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="visitorForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="visitorModalTitle">Tambah Pengunjung</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>

                        <input type="hidden" id="visitor_id" name="id">

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
                                <div class="row">
                                    <div class="col-md-6 mb-5">
                                        <label class="form-label">NIK</label>
                                        <input type="text" name="national_id" id="national_id" class="form-control">
                                        <div class="invalid-feedback" data-field="national_id"></div>
                                    </div>
                                    <div class="col-md-6 mb-5">
                                        <label class="form-label">Nama</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                        <div class="invalid-feedback" data-field="name"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-5">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select name="gender" id="gender" class="form-select">
                                            <option value="">Pilih</option>
                                            <option value="male">Laki-laki</option>
                                            <option value="female">Perempuan</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                        <div class="invalid-feedback" data-field="gender"></div>
                                    </div>
                                    <div class="col-md-6 mb-5">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" name="birth_date" id="birth_date" class="form-control">
                                        <div class="invalid-feedback" data-field="birth_date"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                                <div class="invalid-feedback" data-field="phone"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Hubungan / Catatan</label>
                                <input type="text" name="relation_note" id="relation_note" class="form-control">
                                <div class="invalid-feedback" data-field="relation_note"></div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" id="address" class="form-control" rows="2" placeholder="Alamat (opsional)"></textarea>
                            <div class="invalid-feedback" data-field="address"></div>
                        </div>

                        <div class="form-check form-switch mb-5">
                            <input class="form-check-input" type="checkbox" id="is_blacklisted">
                            <label class="form-check-label" for="is_blacklisted">Masukkan ke daftar blacklist</label>
                            <div class="invalid-feedback d-block" data-field="is_blacklisted"></div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Catatan (opsional)"></textarea>
                            <div class="invalid-feedback" data-field="notes"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveVisitor">Simpan</button>
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
            index: '{{ route('visitors.index') }}',
            store: '{{ route('visitors.store') }}',
            show: (id) => '{{ route('visitors.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
            update: (id) => '{{ route('visitors.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
            destroy: (id) => '{{ route('visitors.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        };

        const visitorModalEl = document.getElementById('visitorModal');
        const visitorModal = new bootstrap.Modal(visitorModalEl);

        const visitorForm = document.getElementById('visitorForm');
        const visitorId = document.getElementById('visitor_id');
        const nationalId = document.getElementById('national_id');
        const nameInput = document.getElementById('name');
        const gender = document.getElementById('gender');
        const birthDate = document.getElementById('birth_date');
        const phone = document.getElementById('phone');
        const address = document.getElementById('address');
        const relationNote = document.getElementById('relation_note');
        const notes = document.getElementById('notes');
        const isBlacklisted = document.getElementById('is_blacklisted');
        const photoInput = document.getElementById('photo');
        const photoWrapper = document.getElementById('photo_wrapper');
        const photoRemove = document.querySelector('input[name="photo_remove"]');

        // Digits-only enforcement for national_id while keeping type="text"
        if (nationalId) {
            // Mobile keyboard hint
            nationalId.setAttribute('inputmode', 'numeric');

            // Sanitize on input
            nationalId.addEventListener('input', function () {
                const cleaned = this.value.replace(/\D+/g, '');
                if (this.value !== cleaned) this.value = cleaned;
            });

            // Block non-digit key presses (allow controls)
            nationalId.addEventListener('keydown', function (e) {
                const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Home','End','Tab'];
                if (allowed.includes(e.key)) return;
                if (e.ctrlKey || e.metaKey) return; // allow copy/cut/paste shortcuts
                if (!/^\d$/.test(e.key)) e.preventDefault();
            });

            // Clean pasted content
            nationalId.addEventListener('paste', function (e) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const digits = (text || '').replace(/\D+/g, '');
                const start = this.selectionStart ?? this.value.length;
                const end = this.selectionEnd ?? this.value.length;
                this.value = this.value.slice(0, start) + digits + this.value.slice(end);
                const caret = start + digits.length;
                this.setSelectionRange?.(caret, caret);
            });
        }

        const table = $('#visitorsTable').DataTable({
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
        document.getElementById('visitorsSearch').addEventListener('input', function() {
            table.search(this.value).draw();
        });

        function clearValidation() {
            visitorForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            visitorForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        document.getElementById('btnAddVisitor').addEventListener('click', () => {
            clearValidation();
            visitorForm.reset();
            visitorId.value = '';
            isBlacklisted.checked = false;
            photoWrapper.style.backgroundImage = "url('{{ asset('assets/media/avatars/blank.png') }}')";
            if (photoRemove) photoRemove.value = '';
            document.getElementById('visitorModalTitle').textContent = 'Tambah Pengunjung';
            visitorModal.show();
        });

        $('#visitorsTable').on('click', '.btnEdit', function() {
            clearValidation();
            const id = $(this).data('id');
            visitorForm.reset();
            visitorId.value = id;
            document.getElementById('visitorModalTitle').textContent = 'Edit Pengunjung';
            visitorModal.show();

            const loader = document.getElementById('modalLoader');
            loader.classList.remove('d-none');
            [...visitorForm.elements].forEach(el => el.disabled = true);

            $.ajax({
                url: routes.show(id),
                method: 'GET',
                success: function(resp) {
                    const d = resp.data || {};
                    nationalId.value = d.national_id || '';
                    nameInput.value = d.name || '';
                    gender.value = d.gender || '';
                    birthDate.value = d.birth_date ? String(d.birth_date).slice(0, 10) : '';
                    phone.value = d.phone || '';
                    address.value = d.address || '';
                    relationNote.value = d.relation_note || '';
                    notes.value = d.notes || '';
                    isBlacklisted.checked = !!d.is_blacklisted;
                    const imgUrl = d.photo_url || `{{ asset('assets/media/avatars/blank.png') }}`;
                    photoWrapper.style.backgroundImage = `url('${imgUrl}')`;
                    if (photoRemove) photoRemove.value = '';
                },
                error: function() {
                    toastr?.error?.('Gagal memuat data pengunjung. Coba lagi.');
                    visitorModal.hide();
                },
                complete: function() {
                    loader.classList.add('d-none');
                    [...visitorForm.elements].forEach(el => el.disabled = false);
                }
            });
        });

        visitorForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidation();

            const id = visitorId.value;
            const formData = new FormData();
            if (nationalId.value) formData.append('national_id', nationalId.value);
            formData.append('name', nameInput.value);
            if (gender.value) formData.append('gender', gender.value);
            if (birthDate.value) formData.append('birth_date', birthDate.value);
            if (phone.value) formData.append('phone', phone.value);
            if (address.value) formData.append('address', address.value);
            if (relationNote.value) formData.append('relation_note', relationNote.value);
            formData.append('is_blacklisted', isBlacklisted.checked ? '1' : '0');
            if (notes.value) formData.append('notes', notes.value);
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
                    visitorModal.hide();
                    toastr?.success?.(resp.message || 'Berhasil disimpan');
                    window.location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errs = xhr.responseJSON.errors;
                        Object.keys(errs).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) input.classList.add('is-invalid');
                            const fb = visitorForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
                            if (fb) fb.textContent = errs[field][0];
                        });
                        return;
                    }
                    toastr?.error?.('Terjadi kesalahan. Coba lagi.');
                }
            });
        });

        $('#visitorsTable').on('click', '.btnDelete', function() {
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
                    title: 'Hapus pengunjung?',
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
                if (confirm('Yakin ingin menghapus pengunjung ini?')) {
                    performDelete();
                }
            }
        });
    </script>
@endpush
