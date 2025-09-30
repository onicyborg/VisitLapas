@extends('layouts.master')

@section('page_title', 'Counters')

@push('styles')
    <style>
        .modal .invalid-feedback{display:block}
    </style>
@endpush

@section('content')
<div class="m-6">
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-5">
            <i class="bi bi-check-circle-fill fs-2hx me-4"></i>
            <div>
                <div class="fw-bold mb-1">Sukses</div>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
            <i class="bi bi-exclamation-triangle-fill fs-2hx me-4"></i>
            <div>
                <div class="fw-bold mb-1">Gagal</div>
                <div>{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header flex-wrap d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 me-4">Counters</h3>
            <div class="d-flex align-items-center gap-2 flex-wrap my-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="countersSearch" class="form-control form-control-sm" placeholder="Cari counter...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button id="btnAdd" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Add Counter
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-0 gy-4" id="countersTable">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Code</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 260px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($counters as $c)
                            <tr data-id="{{ $c->id }}" data-code="{{ $c->code }}" data-name="{{ $c->name }}" data-active="{{ $c->is_active ? 1 : 0 }}">
                                <td class="code text-nowrap">{{ $c->code }}</td>
                                <td class="name">{{ $c->name }}</td>
                                <td class="status">
                                    @if($c->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex justify-content-end gap-1">
                                        <button class="btn btn-sm btn-light-primary btn-icon btn-edit" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light-warning btn-icon btn-toggle" data-bs-toggle="tooltip" title="{{ $c->is_active ? 'Set Inactive' : 'Set Active' }}">
                                            <i class="bi {{ $c->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light-danger btn-icon btn-delete" data-bs-toggle="tooltip" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- DataTables handles pagination -->
        </div>
    </div>
</div>

<!-- Modal: Create/Edit -->
<div class="modal fade" id="counterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="counterForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Counter</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="counter_id">
                    <div class="mb-5">
                        <label class="form-label">Code</label>
                        <input type="text" id="code" class="form-control" placeholder="e.g. LOKET_1">
                        <div class="invalid-feedback" data-field="code"></div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Name</label>
                        <input type="text" id="name" class="form-control" placeholder="e.g. Loket 1">
                        <div class="invalid-feedback" data-field="name"></div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="is_active">
                        <label class="form-check-label" for="is_active">Active</label>
                        <div class="invalid-feedback d-block" data-field="is_active"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Delete Confirm -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Counter</h5>
                <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg fs-2x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteText">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const routes = {
    store: '{{ route('counters.store') }}',
    update: (id) => '{{ route('counters.update', ['counter' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    toggle: (id) => '{{ route('counters.toggle', ['counter' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    destroy: (id) => '{{ route('counters.destroy', ['counter' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
};

const modalEl = document.getElementById('counterModal');
const modal = new bootstrap.Modal(modalEl);
const form = document.getElementById('counterForm');
const fieldId = document.getElementById('counter_id');
const fieldCode = document.getElementById('code');
const fieldName = document.getElementById('name');
const fieldActive = document.getElementById('is_active');
const modalTitle = document.getElementById('modalTitle');

// Initialize DataTables
const dt = $('#countersTable').DataTable({
    responsive: true,
    pageLength: 10,
    lengthChange: false,
    ordering: true,
    searching: true,
    language: {
        emptyTable: 'Tidak ada data',
        zeroRecords: 'Tidak ditemukan data yang cocok',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        infoFiltered: '(disaring dari _MAX_ total data)'
    },
    columnDefs: [
        { targets: -1, orderable: false } // Actions column
    ]
});
// Header search binds to DataTables
document.getElementById('countersSearch')?.addEventListener('input', function(){
    dt.search(this.value).draw();
});
// Enable Bootstrap tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

function clearValidation(){
    form.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el=>el.textContent='');
}

function openCreate(){
    clearValidation();
    form.reset();
    fieldId.value='';
    fieldActive.checked=true;
    modalTitle.textContent='Add Counter';
    modal.show();
}

function openEdit(tr){
    clearValidation();
    const id = tr.dataset.id;
    fieldId.value = id;
    fieldCode.value = tr.dataset.code || '';
    fieldName.value = tr.dataset.name || '';
    fieldActive.checked = tr.dataset.active === '1';
    modalTitle.textContent='Edit Counter';
    modal.show();
}

function submitForm(e){
    e.preventDefault();
    clearValidation();
    const id = fieldId.value;
    const payload = new FormData();
    payload.append('code', fieldCode.value);
    payload.append('name', fieldName.value);
    payload.append('is_active', fieldActive.checked ? '1' : '0');
    if (id){ payload.append('_method','PUT'); }

    const url = id ? routes.update(id) : routes.store;

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: payload
    }).then(async res => {
        const data = await res.json().catch(()=>({}));
        if (!res.ok){
            if (res.status === 422 && data.errors){
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById(field);
                    if (input) input.classList.add('is-invalid');
                    const fb = form.querySelector(`.invalid-feedback[data-field="${field}"]`);
                    if (fb) fb.textContent = data.errors[field][0];
                });
                return;
            }
            toastr?.error?.(data.message || 'Terjadi kesalahan');
            return;
        }
        toastr?.success?.(data.message || 'Berhasil');
        window.location.reload();
    }).catch(()=> toastr?.error?.('Terjadi kesalahan jaringan'));
}

function toggleRow(tr){
    const id = tr.dataset.id;
    fetch(routes.toggle(id), {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
    }).then(async res=>{
        const data = await res.json().catch(()=>({}));
        if (!res.ok){ toastr?.error?.(data.message || 'Gagal update status'); return; }
        // Optimistic update: update badge and button label
        const active = !!data.data?.is_active;
        tr.dataset.active = active ? '1' : '0';
        const tdStatus = tr.querySelector('.status');
        if (tdStatus){ tdStatus.innerHTML = active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; }
        const btn = tr.querySelector('.btn-toggle');
        if (btn){
            // Icon-only button; update icon and tooltip title
            btn.innerHTML = `<i class="bi ${active ? 'bi-pause-circle' : 'bi-play-circle'}"></i>`;
            const newTitle = active ? 'Set Inactive' : 'Set Active';
            btn.setAttribute('title', newTitle);
            btn.setAttribute('data-bs-original-title', newTitle);
            const tip = bootstrap.Tooltip.getInstance(btn) || new bootstrap.Tooltip(btn);
            // For BS5.3+, refresh content if API available
            if (typeof tip.setContent === 'function') {
                tip.setContent({ '.tooltip-inner': newTitle });
            }
        }
        toastr?.success?.(data.message || 'Status updated');
    }).catch(()=> toastr?.error?.('Gagal update status'));
}

function confirmAndDelete(tr){
    const id = tr.dataset.id;
    const name = tr.dataset.name || tr.dataset.code || '';
    if (window.Swal && Swal.fire) {
        Swal.fire({
            title: 'Hapus counter?',
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
                fetch(routes.destroy(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                }).then(async res=>{
                    const data = await res.json().catch(()=>({}));
                    if (!res.ok){
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menghapus. Coba lagi.' });
                        toastr?.error?.(data.message || 'Gagal menghapus');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message || 'Berhasil dihapus', timer: 1500, showConfirmButton: false });
                    toastr?.success?.(data.message || 'Berhasil dihapus');
                    window.location.reload();
                }).catch(()=>{
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus. Coba lagi.' });
                    toastr?.error?.('Gagal menghapus');
                });
            }
        });
    } else {
        if (confirm('Yakin ingin menghapus counter ini?')) {
            fetch(routes.destroy(id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            }).then(async res=>{
                const data = await res.json().catch(()=>({}));
                if (!res.ok){ toastr?.error?.(data.message || 'Gagal menghapus'); return; }
                toastr?.success?.(data.message || 'Berhasil dihapus');
                window.location.reload();
            }).catch(()=> toastr?.error?.('Gagal menghapus'));
        }
    }
}

// Wire events
 document.getElementById('btnAdd')?.addEventListener('click', openCreate);
 form.addEventListener('submit', submitForm);
 document.getElementById('countersTable')?.addEventListener('click', function(e){
    const tr = e.target.closest('tr');
    if (!tr) return;
    if (e.target.closest('.btn-edit')) return openEdit(tr);
    if (e.target.closest('.btn-toggle')) return toggleRow(tr);
    if (e.target.closest('.btn-delete')) return confirmAndDelete(tr);
 });
</script>
@endpush
