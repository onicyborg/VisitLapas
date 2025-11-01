@extends('layouts.master')

@section('page_title', 'Queues')

@push('styles')
<style>
    .modal .invalid-feedback{display:block}
    .table-actions .btn{ min-width: 34px; }
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
        <div class="card-header flex-wrap d-flex align-items-center gap-3">
            <h3 class="card-title mb-0">Queues</h3>
            <div class="d-flex flex-wrap align-items-end gap-2 ms-auto">
                <div>
                    <label class="form-label mb-1">Tanggal</label>
                    <input type="date" id="f_date" class="form-control form-control-sm" value="{{ $date }}">
                </div>
                <div>
                    <label class="form-label mb-1">Status</label>
                    <select id="f_status" class="form-select form-select-sm" style="min-width: 160px;">
                        <option value="">All</option>
                        @foreach(['waiting','called','serving','done','no_show','cancelled'] as $st)
                            <option value="{{ $st }}" @selected(($status ?? '')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group input-group-sm" style="width: 260px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="f_search" class="form-control form-control-sm" placeholder="Ticket/Visitor/Inmate" value="{{ $q }}">
                </div>
                <div>
                    <button type="button" id="btnCreate" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-0 gy-4" id="queuesTable">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Ticket</th>
                            <th>Date</th>
                            <th>Visitor</th>
                            <th>Inmate</th>
                            <th>Status</th>
                            <th>Counter</th>
                            <th>Times</th>
                            <th class="text-end" style="width: 300px">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <!-- Pagination handled by DataTables -->
        </div>
    </div>
</div>

<!-- Modal: Create -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createForm">
                <div class="modal-header">
                    <h5 class="modal-title">Create Queue</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Visit Date</label>
                        <input type="date" id="c_visit_date" class="form-control" value="{{ $date }}">
                        <div class="invalid-feedback" data-field="visit_date"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Visitor</label>
                        <select id="c_visitor_id" class="form-select">
                            <option value="">- pilih visitor -</option>
                            @foreach($visitors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="visitor_id"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Inmate</label>
                        <select id="c_inmate_id" class="form-select">
                            <option value="">- pilih inmate -</option>
                            @foreach($inmates as $i)
                                <option value="{{ $i->id }}">{{ $i->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="inmate_id"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Queue</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="e_id">
                    <div class="mb-4">
                        <label class="form-label">Visitor</label>
                        <select id="e_visitor_id" class="form-select">
                            <option value="">- pilih visitor -</option>
                            @foreach($visitors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="visitor_id"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Inmate</label>
                        <select id="e_inmate_id" class="form-select">
                            <option value="">- pilih inmate -</option>
                            @foreach($inmates as $i)
                                <option value="{{ $i->id }}">{{ $i->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="inmate_id"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea id="e_notes" class="form-control" rows="2"></textarea>
                        <div class="invalid-feedback" data-field="notes"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Call -->
<div class="modal fade" id="callModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="callForm">
                <div class="modal-header">
                    <h5 class="modal-title">Call Queue</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="call_id">
                    <div class="mb-4">
                        <label class="form-label">Counter</label>
                        <select id="call_counter_id" class="form-select">
                            <option>- pilih counter -</option>
                            @foreach($activeCounters as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="counter_id"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Message (opsional)</label>
                        <input type="text" id="call_message" class="form-control" maxlength="200" placeholder="e.g. Loket 1 memanggil tiket 12">
                        <div class="invalid-feedback" data-field="message"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Call</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cancel -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelForm">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Queue</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cancel_id">
                    <div class="mb-4">
                        <label class="form-label">Reason</label>
                        <input type="text" id="cancel_reason" class="form-control" maxlength="255" placeholder="Alasan pembatalan"/>
                        <div class="invalid-feedback" data-field="reason"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const routes = {
    index: '{{ route('queues.index') }}',
    store: '{{ route('queues.store') }}',
    update: (id) => '{{ route('queues.update', ['queue' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    call: (id) => '{{ route('queues.call', ['queue' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    start: (id) => '{{ route('queues.start', ['queue' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    finish: (id) => '{{ route('queues.finish', ['queue' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    no_show: (id) => '{{ route('queues.no_show', ['queue' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    cancel: (id) => '{{ route('queues.cancel', ['queue' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
};

// DataTables (server-side)
let dt = $('#queuesTable').DataTable({
    processing: true,
    serverSide: true,
    searching: true,
    lengthChange: false,
    pageLength: 10,
    ajax: {
        url: routes.index,
        type: 'GET',
        data: function(d){
            d.date = document.getElementById('f_date').value;
            d.status = document.getElementById('f_status').value;
        }
    },
    columns: [
        { data: 'ticket', name: 'ticket', orderable: false },
        { data: 'date', name: 'date' },
        { data: 'visitor', name: 'visitor', orderable: false },
        { data: 'inmate', name: 'inmate', orderable: false },
        { data: 'status', name: 'status', orderable: false },
        { data: 'counter', name: 'counter', orderable: false },
        { data: 'times', name: 'times', orderable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false },
    ],
    createdRow: function(row, data){
        if (data.DT_RowAttr) {
            for (const k in data.DT_RowAttr) { row.setAttribute(k, data.DT_RowAttr[k]); }
        }
    },
    drawCallback: function(){
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
});

// Filters: auto apply
document.getElementById('f_date').addEventListener('change', ()=> dt.ajax.reload());
document.getElementById('f_status').addEventListener('change', ()=> dt.ajax.reload());
document.getElementById('f_search').addEventListener('input', function(){ dt.search(this.value).draw(); });

// Create
const createModal = new bootstrap.Modal(document.getElementById('createModal'));
const createForm = document.getElementById('createForm');
document.getElementById('btnCreate')?.addEventListener('click', ()=>{ clearInvalid(createForm); createForm.reset(); document.getElementById('c_visit_date').value='{{ $date }}'; createModal.show(); });
createForm.addEventListener('submit', function(e){
    e.preventDefault(); clearInvalid(createForm);
    const fd = new FormData();
    fd.append('visit_date', document.getElementById('c_visit_date').value);
    fd.append('visitor_id', document.getElementById('c_visitor_id').value);
    fd.append('inmate_id', document.getElementById('c_inmate_id').value);
    fetch(routes.store, { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd })
    .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
        if(!ok){return handleErrors(createForm,d);}
        toastr?.success?.(d.message||'Created');
        createModal.hide();
        dt.ajax.reload(null, false);
    }).catch(()=> toastr?.error?.('Gagal membuat antrian'));
});

// Edit
const editModal = new bootstrap.Modal(document.getElementById('editModal'));
const editForm = document.getElementById('editForm');
editForm.addEventListener('submit', function(e){
    e.preventDefault(); clearInvalid(editForm);
    const id = document.getElementById('e_id').value;
    const fd = new FormData();
    fd.append('_method','PUT');
    fd.append('visitor_id', document.getElementById('e_visitor_id').value);
    fd.append('inmate_id', document.getElementById('e_inmate_id').value);
    fd.append('notes', document.getElementById('e_notes').value || '');
    fetch(routes.update(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd })
    .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
        if(!ok){return handleErrors(editForm,d);}
        toastr?.success?.(d.message||'Updated');
        editModal.hide();
        dt.ajax.reload(null, false);
    }).catch(()=> toastr?.error?.('Gagal mengupdate antrian'));
});

// Call
const callModal = new bootstrap.Modal(document.getElementById('callModal'));
const callForm = document.getElementById('callForm');
const callCounterSelect = document.getElementById('call_counter_id');
const callMessageInput = document.getElementById('call_message');
callForm.addEventListener('submit', function(e){
    e.preventDefault(); clearInvalid(callForm);
    const id = document.getElementById('call_id').value;
    const fd = new FormData();
    fd.append('counter_id', document.getElementById('call_counter_id').value);
    fd.append('message', document.getElementById('call_message').value || '');
    fetch(routes.call(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd })
    .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
        if(!ok){return handleErrors(callForm,d);}
        toastr?.success?.(d.message||'Dipanggil');
        callModal.hide();
        dt.ajax.reload(null, false);
    }).catch(()=> toastr?.error?.('Gagal memanggil antrian'));
});

// Cancel
const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
const cancelForm = document.getElementById('cancelForm');
// Row actions: delegate clicks from the table body
document.querySelector('#queuesTable tbody').addEventListener('click', function(e){
    const btn = e.target.closest('button');
    if(!btn) return;
    const tr = e.target.closest('tr');
    if(!tr) return;
    const id = tr.dataset.id;

    if(btn.classList.contains('btn-edit')){
        clearInvalid(editForm);
        document.getElementById('e_id').value = id;
        // Prefill selects using data attributes from the row when available
        const vId = tr.getAttribute('data-visitor-id');
        const iId = tr.getAttribute('data-inmate-id');
        if (vId) document.getElementById('e_visitor_id').value = vId;
        if (iId) document.getElementById('e_inmate_id').value = iId;
        editModal.show();
        return;
    }
    if(btn.classList.contains('btn-call')){
        // Reset form state to avoid stale selections
        callForm.reset();
        clearInvalid(callForm);
        document.getElementById('call_id').value = id;
        const lastCounterId = tr.getAttribute('data-counter-id');
        if (lastCounterId && callCounterSelect.querySelector(`option[value="${lastCounterId}"]`)) {
            callCounterSelect.value = lastCounterId;
        } else {
            // No previous/invalid counter: ensure default first option is selected
            callCounterSelect.selectedIndex = 0;
        }
        // Clear message by default (no auto-fill)
        callMessageInput.value = '';
        callModal.show();
        return;
    }
    if(btn.classList.contains('btn-start')){
        fetch(routes.start(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN} })
        .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
            if(!ok){ toastr?.error?.(d.message||'Gagal mulai'); return; }
            toastr?.success?.(d.message||'Dimulai');
            dt.ajax.reload(null, false);
        }).catch(()=> toastr?.error?.('Gagal mulai'));
        return;
    }
    if(btn.classList.contains('btn-finish')){
        fetch(routes.finish(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN} })
        .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
            if(!ok){ toastr?.error?.(d.message||'Gagal selesai'); return; }
            toastr?.success?.(d.message||'Selesai');
            dt.ajax.reload(null, false);
        }).catch(()=> toastr?.error?.('Gagal selesai'));
        return;
    }
    if(btn.classList.contains('btn-no-show')){
        if (window.Swal && Swal.fire) {
            Swal.fire({
                title: 'Tandai No Show?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tandai',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((res)=>{
                if(res.isConfirmed){
                    fetch(routes.no_show(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN} })
                    .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
                        if(!ok){ toastr?.error?.(d.message||'Gagal menandai'); return; }
                        toastr?.success?.(d.message||'Ditandai');
                        dt.ajax.reload(null, false);
                    }).catch(()=> toastr?.error?.('Gagal menandai'));
                }
            });
        } else {
            if(confirm('Tandai No Show?')){
                fetch(routes.no_show(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN} })
                .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
                    if(!ok){ toastr?.error?.(d.message||'Gagal menandai'); return; }
                    toastr?.success?.(d.message||'Ditandai');
                    dt.ajax.reload(null, false);
                }).catch(()=> toastr?.error?.('Gagal menandai'));
            }
        }
        return;
    }
    if(btn.classList.contains('btn-cancel')){
        clearInvalid(cancelForm);
        document.getElementById('cancel_id').value = id;
        cancelModal.show();
        return;
    }
});

// Submit cancel form
cancelForm.addEventListener('submit', function(e){
    e.preventDefault(); clearInvalid(cancelForm);
    const id = document.getElementById('cancel_id').value;
    const fd = new FormData();
    fd.append('reason', document.getElementById('cancel_reason').value || '');
    fetch(routes.cancel(id), { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd })
    .then(r=>r.json().then(d=>({ok:r.ok, d}))).then(({ok,d})=>{
        if(!ok){return handleErrors(cancelForm,d);}
        toastr?.success?.(d.message||'Dibatalkan');
        cancelModal.hide();
        dt.ajax.reload(null, false);
    }).catch(()=> toastr?.error?.('Gagal membatalkan antrian'));
});

function clearInvalid(form){
    form.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el=>el.textContent='');
}
function handleErrors(form, resp){
    if(resp && resp.errors){
        Object.keys(resp.errors).forEach(f=>{
            const fb = form.querySelector(`.invalid-feedback[data-field="${f}"]`);
            if (fb) fb.textContent = resp.errors[f][0];
            const input = form.querySelector(`#c_${f}, #e_${f}, #call_${f}, #cancel_${f}`);
            if (input) input.classList.add('is-invalid');
        });
    }
    toastr?.error?.(resp?.message || 'Validasi gagal');
}
// keep URL in sync (optional)
['f_date','f_status','f_search'].forEach(id=>{
    document.getElementById(id).addEventListener('change', syncUrl);
    document.getElementById(id).addEventListener('input', syncUrl);
});
function syncUrl(){
    const params = new URLSearchParams();
    params.set('date', document.getElementById('f_date').value);
    const st = document.getElementById('f_status').value; if (st) params.set('status', st);
    const s = document.getElementById('f_search').value; if (s) params.set('q', s);
    history.replaceState(null, '', routes.index + '?' + params.toString());
}
</script>
@endpush
