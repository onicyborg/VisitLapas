@extends('layouts.master')

@push('styles')
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .image-input-placeholder {
            background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}');
        }

        [data-bs-theme="dark"] .image-input-placeholder {
            background-image: url('{{ asset('assets/media/svg/avatars/blank-dark.svg') }}');
        }

        .image-input-wrapper {
            background-size: cover;
            background-position: center;
        }

        .image-input.image-input-circle .image-input-wrapper {
            border-radius: 50%;
        }
    </style>
@endpush

@section('content')
    <div class="card m-6">
        <div class="card-header flex-wrap d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 me-4">Daftar Pengguna</h3>
            <div class="d-flex align-items-center gap-2 flex-wrap my-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="usersSearch" class="form-control form-control-sm"
                        placeholder="Cari pengguna...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button id="btnAddUser" class="btn btn-primary btn-sm">
                    <i class="ki-duotone ki-plus fs-2"></i> Tambah Pengguna
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Avatar</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Terakhir Login</th>
                            <th>Status</th>
                            <th style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr data-id="{{ $u->id }}">
                                <td class="avatar">
                                    @php
                                        $avatar = optional($u->profile)->avatar_url;
                                        $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=4e73df&color=fff&size=64';
                                        $fallbackFull = 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=4e73df&color=fff&size=512';
                                    @endphp
                                    <a href="#" class="avatar-view" data-full="{{ $avatar ?: $fallbackFull }}" title="Lihat foto">
                                        <img src="{{ $avatar ?: $fallback }}" alt="{{ $u->name }}" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
                                    </a>
                                </td>
                                <td class="full_name">{{ $u->name }}</td>
                                <td class="email">{{ $u->email }}</td>
                                <td class="phone">{{ $u->phone ?? '-' }}</td>
                                <td class="last_login">
                                    @if ($u->last_login_at)
                                        <span title="{{ \Carbon\Carbon::parse($u->last_login_at)->toDayDateTimeString() }}">
                                            {{ \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="status">
                                    @if ($u->is_active ?? true)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $u->id }}">
                                        <center><i class="bi bi-pencil-square"></i></center>
                                    </button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $u->id }}">
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

    <!-- Modal Preview Avatar -->
    <div class="modal fade" id="avatarPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pratinjau Foto</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center justify-content-center p-0">
                    <img id="avatarPreviewImg" src="" alt="Avatar" style="max-width: 100%; max-height: 80vh; object-fit: contain;" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="userForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle">Tambah Pengguna</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="bi bi-x-lg fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>

                        <input type="hidden" id="user_id" name="id">

                        <div class="row">
                            <div class="col-md-4 mb-5">
                                <label class="form-label d-block">Foto Profil</label>
                                <div class="image-input image-input-circle" data-kt-image-input="true"
                                    style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                    <div id="photo_wrapper" class="image-input-wrapper w-125px h-125px"></div>
                                    <label
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click"
                                        title="Change avatar">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        <input type="file" name="photo" id="photo"
                                            accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="photo_remove" />
                                    </label>
                                    <span
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click"
                                        title="Cancel avatar">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </span>
                                    <span
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove" data-bs-toggle="tooltip" data-bs-dismiss="click"
                                        title="Remove avatar">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </span>
                                </div>
                                <div class="invalid-feedback d-block" data-field="photo"></div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-5">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                                    <div class="invalid-feedback" data-field="full_name"></div>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                    <div class="invalid-feedback" data-field="email"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                                <div class="invalid-feedback" data-field="phone"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Password (opsional)</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Biarkan kosong jika tidak ingin mengubah">
                                <div class="invalid-feedback" data-field="password"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active">
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveUser">Simpan</button>
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
            index: '{{ route('users.index') }}',
            store: '{{ route('users.store') }}',
            show: (id) => '{{ route('users.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace(
                'ID_PLACEHOLDER', id),
            update: (id) => '{{ route('users.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace(
                'ID_PLACEHOLDER', id),
            destroy: (id) => '{{ route('users.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace(
                'ID_PLACEHOLDER', id),
        };

        const userModalEl = document.getElementById('userModal');
        const userModal = new bootstrap.Modal(userModalEl);

        const userForm = document.getElementById('userForm');
        const userIdInput = document.getElementById('user_id');
        const fullName = document.getElementById('full_name');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        const password = document.getElementById('password');
        const isActive = document.getElementById('is_active');
        const photoInput = document.getElementById('photo');
        const photoWrapper = document.getElementById('photo_wrapper');
        const photoRemove = document.querySelector('input[name="photo_remove"]');

        const table = $('#usersTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            ordering: true,
            searching: true
        });

        // Preview avatar fullscreen
        document.addEventListener('click', function(e) {
            const a = e.target.closest('a.avatar-view');
            if (!a) return;
            e.preventDefault();
            const fullUrl = a.getAttribute('data-full');
            const img = document.getElementById('avatarPreviewImg');
            if (img) img.src = fullUrl || '';
            const modalEl = document.getElementById('avatarPreviewModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        // Pencarian keyword
        document.getElementById('usersSearch').addEventListener('input', function() {
            table.search(this.value).draw();
        });

        function clearValidation() {
            userForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            userForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        document.getElementById('btnAddUser').addEventListener('click', () => {
            clearValidation();
            userForm.reset();
            userIdInput.value = '';
            isActive.checked = true;
            photoWrapper.style.backgroundImage = "url('{{ asset('assets/media/avatars/blank.png') }}')";
            if (photoRemove) photoRemove.value = '';
            document.getElementById('userModalTitle').textContent = 'Tambah Pengguna';
            userModal.show();
        });

        $('#usersTable').on('click', '.btnEdit', function() {
            clearValidation();
            const id = $(this).data('id');
            userForm.reset();
            userIdInput.value = id;
            document.getElementById('userModalTitle').textContent = 'Edit Pengguna';
            userModal.show();

            const loader = document.getElementById('modalLoader');
            loader.classList.remove('d-none');
            [...userForm.elements].forEach(el => el.disabled = true);

            $.ajax({
                url: routes.show(id),
                method: 'GET',
                success: function(resp) {
                    const d = resp.data || {};
                    fullName.value = d.name || '';
                    email.value = d.email || '';
                    phone.value = d.phone || '';
                    const imgUrl = d.profile?.avatar_url ||
                        `{{ asset('assets/media/avatars/blank.png') }}`;
                    photoWrapper.style.backgroundImage = `url('${imgUrl}')`;
                    const active = (d.is_active === true) || (d.is_active === 1) || (d.is_active ===
                        '1');
                    isActive.checked = active;
                    if (photoRemove) photoRemove.value = '';
                },
                error: function() {
                    toastr?.error?.('Gagal memuat data pengguna. Coba lagi.');
                    userModal.hide();
                },
                complete: function() {
                    loader.classList.add('d-none');
                    [...userForm.elements].forEach(el => el.disabled = false);
                }
            });
        });

        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidation();

            const id = userIdInput.value;
            const formData = new FormData();
            formData.append('full_name', fullName.value);
            formData.append('email', email.value);
            formData.append('phone', phone.value);
            formData.append('is_active', isActive.checked ? 1 : 0);
            if (password.value) formData.append('password', password.value);
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
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    // Sederhanakan: reload agar data sinkron
                    userModal.hide();
                    toastr?.success?.(resp.message || 'Berhasil disimpan');
                    window.location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errs = xhr.responseJSON.errors;
                        Object.keys(errs).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) input.classList.add('is-invalid');
                            const fb = userForm.querySelector(
                                `.invalid-feedback[data-field="${field}"]`);
                            if (fb) fb.textContent = errs[field][0];
                        });
                        return;
                    }
                    toastr?.error?.('Terjadi kesalahan. Coba lagi.');
                }
            });
        });

        $('#usersTable').on('click', '.btnDelete', function() {
            const id = $(this).data('id');
            const name = $(this).closest('tr').find('.full_name').text().trim();

            function performDelete() {
                $.ajax({
                    url: routes.destroy(id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
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
                    title: 'Hapus pengguna?',
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
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        performDelete();
                    }
                });
            } else {
                if (confirm('Yakin ingin menghapus pengguna ini?')) {
                    performDelete();
                }
            }
        });
    </script>
@endpush
