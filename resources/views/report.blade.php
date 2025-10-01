@extends('layouts.master')

@section('page_title', 'Reports')

@push('styles')
    <style>
        .table-actions .btn {
            min-width: 34px;
        }
    </style>
@endpush

@section('content')
    <div class="m-6">
        <div class="card">
            <div class="card-header flex-wrap d-flex align-items-center gap-3">
                <h3 class="card-title mb-0">Visit Reports</h3>
                <div class="d-flex flex-wrap align-items-end gap-2 ms-auto">
                    <div>
                        <label class="form-label mb-1">From</label>
                        <input type="date" id="f_from" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div>
                        <label class="form-label mb-1">To</label>
                        <input type="date" id="f_to" class="form-control form-control-sm"
                            value="{{ $to }}">
                    </div>
                    <div class="input-group input-group-sm" style="width: 260px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="f_search" class="form-control form-control-sm"
                            placeholder="Ticket/Visitor/Inmate">
                    </div>
                    <div>
                        <button type="button" id="btnExport" class="btn btn-success btn-sm"><i
                                class="bi bi-file-earmark-excel"></i> Export Excel</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-4" id="reportsTable">
                        <thead>
                            <tr class="fw-semibold text-muted text-center">
                                <th class="text-center">Date</th>
                                <th class="text-center">Ticket</th>
                                <th class="text-center">Visitor</th>
                                <th class="text-center">Inmate</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Counter</th>
                                <th class="text-center">Priority</th>
                                <th class="text-center">Times</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const routes = {
            data: '{{ route('reports.data') }}',
            export: '{{ route('reports.export') }}'
        };

        let dt = $('#reportsTable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: routes.data,
                type: 'GET',
                data: function(d) {
                    d.from = document.getElementById('f_from').value;
                    d.to = document.getElementById('f_to').value;
                }
            },
            columns: [{
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'ticket',
                    name: 'ticket'
                },
                {
                    data: 'visitor',
                    name: 'visitor'
                },
                {
                    data: 'inmate',
                    name: 'inmate'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'counter',
                    name: 'counter'
                },
                {
                    data: 'priority',
                    name: 'priority',
                    render: function(data, type, row) {
                        if (data === null || data === undefined) return '';
                        const n = parseInt(data, 10);
                        let label = 'Regular';
                        let color = 'secondary';
                        if (n === 1) { label = 'Priority'; color = 'warning'; }
                        else if (n === 2) { label = 'VIP'; color = 'danger'; }
                        return '<center><span class="badge bg-' + color + '">' + label + '</span></center>';
                    }
                },
                {
                    data: 'times',
                    name: 'times'
                },
            ],
            createdRow: function(row, data) {},
            drawCallback: function() {},
        });

        // Auto apply filters
        ['f_from', 'f_to'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => dt.ajax.reload());
        });
        document.getElementById('f_search').addEventListener('input', function() {
            dt.search(this.value).draw();
        });

        // Export button
        function buildExportUrl() {
            const params = new URLSearchParams();
            const from = document.getElementById('f_from').value;
            if (from) params.set('from', from);
            const to = document.getElementById('f_to').value;
            if (to) params.set('to', to);
            const q = document.getElementById('f_search').value;
            if (q) params.set('q', q);
            return routes.export+'?' + params.toString();
        }

        document.getElementById('btnExport').addEventListener('click', function() {
            window.location.href = buildExportUrl();
        });
    </script>
@endpush
