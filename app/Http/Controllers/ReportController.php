<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Models\VisitQueue;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VisitReportExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $from = $request->query('from', $today);
        $to = $request->query('to', $today);
        return view('report', compact('from', 'to'));
    }

    public function data(Request $request)
    {
        $draw = (int)$request->input('draw', 1);
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);
        $search = trim((string)($request->input('search.value') ?? ''));

        $from = $request->input('from');
        $to = $request->input('to');

        $baseQuery = VisitQueue::query()
            ->leftJoin('visitors as v', 'v.id', '=', 'visit_queue.visitor_id')
            ->leftJoin('inmates as i', 'i.id', '=', 'visit_queue.inmate_id')
            ->leftJoin('counters as c', 'c.id', '=', 'visit_queue.counter_id')
            ->select(
                'visit_queue.id',
                'visit_queue.visit_date',
                'visit_queue.ticket_number',
                DB::raw("coalesce(v.name,'-') as visitor_name"),
                DB::raw("coalesce(i.name,'-') as inmate_name"),
                DB::raw("coalesce(c.code,'-') as counter_code"),
                'visit_queue.status',
                'visit_queue.priority',
                'visit_queue.called_at',
                'visit_queue.started_at',
                'visit_queue.ended_at',
                'visit_queue.created_at'
            );

        // Total records (without filters)
        $recordsTotal = (clone $baseQuery)->without(['orders'])->count('visit_queue.id');

        // Filters
        if ($from) { $baseQuery->whereDate('visit_queue.visit_date', '>=', $from); }
        if ($to) { $baseQuery->whereDate('visit_queue.visit_date', '<=', $to); }
        if ($search !== '') {
            $baseQuery->where(function($q) use ($search){
                $like = '%'.$search.'%';
                $q->where('visit_queue.ticket_number', 'ilike', $like)
                  ->orWhere('v.name', 'ilike', $like)
                  ->orWhere('i.name', 'ilike', $like)
                  ->orWhere('c.code', 'ilike', $like);
            });
        }

        $recordsFiltered = (clone $baseQuery)->without(['orders'])->count('visit_queue.id');

        // Ordering
        $columnsMap = [
            0 => 'visit_queue.visit_date',
            1 => 'visit_queue.ticket_number',
            2 => 'v.name',
            3 => 'i.name',
            4 => 'visit_queue.status',
            5 => 'c.code',
            6 => 'visit_queue.priority',
            7 => 'visit_queue.created_at',
        ];
        $order = $request->input('order', []);
        if (!empty($order)) {
            foreach ($order as $ord) {
                $idx = (int)($ord['column'] ?? 0);
                $dir = strtolower($ord['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $col = $columnsMap[$idx] ?? 'visit_queue.created_at';
                $baseQuery->orderBy($col, $dir);
            }
        } else {
            $baseQuery->orderBy('visit_queue.visit_date', 'desc')->orderBy('visit_queue.ticket_number');
        }

        // Paging
        if ($length > 0) {
            $baseQuery->skip($start)->take($length);
        }

        $rows = $baseQuery->get();

        $data = $rows->map(function($r){
            return [
                'date' => Carbon::parse($r->visit_date)->format('Y-m-d'),
                'ticket' => e($r->ticket_number),
                'visitor' => e($r->visitor_name),
                'inmate' => e($r->inmate_name),
                'status' => e(ucfirst(str_replace('_',' ', $r->status))),
                'counter' => e($r->counter_code),
                'priority' => (int)$r->priority,
                'times' => trim(implode(' | ', array_filter([
                    $r->called_at ? ('Called: '.Carbon::parse($r->called_at)->format('H:i')) : null,
                    $r->started_at ? ('Start: '.Carbon::parse($r->started_at)->format('H:i')) : null,
                    $r->ended_at ? ('End: '.Carbon::parse($r->ended_at)->format('H:i')) : null,
                ])))
            ];
        })->all();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $search = trim((string)$request->query('q', ''));

        $export = new VisitReportExport($from, $to, $search);
        return Excel::download($export, $export->fileName);
    }
}
