<?php

namespace App\Http\Controllers;

use App\Models\VisitQueue;
use App\Models\Visitors;
use App\Models\Inmates;
use App\Models\Counters;
use App\Models\QueueCallLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QueueController extends Controller
{
    /**
     * List queues with filters and server-side pagination.
     */
    public function index(Request $request)
    {
        $date = $request->input('date') ?: Carbon::now()->toDateString();
        $status = $request->input('status');
        $search = trim((string) ($request->input('search.value') ?? $request->input('q') ?? ''));

        // AJAX request from DataTables -> return JSON
        if ($request->ajax()) {
            $base = VisitQueue::with(['visitor','inmate','counter'])
                ->whereDate('visit_date', $date);

            $recordsTotal = (clone $base)->count();

            if ($status && in_array($status, ['waiting','called','serving','done','no_show','cancelled'])) {
                $base->where('status', $status);
            }
            if ($search !== '') {
                $base->where(function($qq) use ($search) {
                    $qq->where('ticket_number', 'like', "%$search%")
                       ->orWhereHas('visitor', function($v) use ($search){ $v->where('name', 'like', "%$search%"); })
                       ->orWhereHas('inmate', function($i) use ($search){ $i->where('name', 'like', "%$search%"); });
                });
            }

            $recordsFiltered = (clone $base)->count();

            $base->orderByRaw("CASE WHEN status IN ('waiting','called','serving') THEN 0 ELSE 1 END ASC")
                 ->orderBy('created_at', 'ASC');

            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            if ($length > 100) { $length = 100; }

            $rows = $base->skip($start)->take($length)->get();

            $data = $rows->map(function($qrow){
                $status = $qrow->status;
                if (in_array($status,['waiting','called','serving'])) {
                    $statusBadge = '<span class="badge bg-primary">'.ucfirst(str_replace('_',' ', $status)).'</span>';
                } elseif ($status==='done') {
                    $statusBadge = '<span class="badge bg-success">Done</span>';
                } elseif ($status==='no_show') {
                    $statusBadge = '<span class="badge bg-warning">No Show</span>';
                } else {
                    $statusBadge = '<span class="badge bg-secondary">Cancelled</span>';
                }

                $actions = '<div class="d-inline-flex gap-1">';
                if (in_array($status, ['waiting','called'])) {
                    $actions .= '<button class="btn btn-sm btn-light-primary btn-icon btn-edit" data-bs-toggle="tooltip" title="Edit"><i class="bi bi-pencil-square"></i></button>';
                    $actions .= '<button class="btn btn-sm btn-light-info btn-icon btn-call" data-bs-toggle="tooltip" title="Call"><i class="bi bi-megaphone"></i></button>';
                }
                if ($status==='called') {
                    $actions .= '<button class="btn btn-sm btn-light-success btn-icon btn-start" data-bs-toggle="tooltip" title="Start"><i class="bi bi-play-circle"></i></button>';
                }
                if ($status==='serving') {
                    $actions .= '<button class="btn btn-sm btn-light-success btn-icon btn-finish" data-bs-toggle="tooltip" title="Finish"><i class="bi bi-check2-circle"></i></button>';
                }
                if (in_array($status, ['waiting','called'])) {
                    $actions .= '<button class="btn btn-sm btn-light-warning btn-icon btn-no-show" data-bs-toggle="tooltip" title="No Show"><i class="bi bi-person-x"></i></button>';
                }
                if (in_array($status, ['waiting','called','serving'])) {
                    $actions .= '<button class="btn btn-sm btn-light-danger btn-icon btn-cancel" data-bs-toggle="tooltip" title="Cancel"><i class="bi bi-x-circle"></i></button>';
                }
                $actions .= '</div>';
                return [
                    'DT_RowAttr' => [
                        'data-id' => $qrow->id,
                        'data-status' => $qrow->status,
                        'data-visitor-id' => $qrow->visitor_id,
                        'data-inmate-id' => $qrow->inmate_id,
                        'data-counter-id' => $qrow->counter_id,
                    ],
                    'ticket' => '<strong>'.e((string)$qrow->ticket_number).'</strong>',
                    'date' => e($qrow->visit_date->format('Y-m-d')),
                    'visitor' => e($qrow->visitor->name ?? '-'),
                    'inmate' => e($qrow->inmate->name ?? '-'),
                    'status' => $statusBadge,
                    'counter' => e($qrow->counter->code ?? '-'),
                    'times' => '<div class="text-muted small">'
                        .' <div>Called: '.e(optional($qrow->called_at)->format('H:i') ?? '-').'</div>'
                        .' <div>Start: '.e(optional($qrow->started_at)->format('H:i') ?? '-').'</div>'
                        .' <div>End: '.e(optional($qrow->ended_at)->format('H:i') ?? '-').'</div>'
                        .'</div>',
                    'actions' => $actions,
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        // Non-AJAX: render page shell with dropdown data
        $visitors = Visitors::orderBy('name')->limit(200)->get(['id','name']);
        $inmates = Inmates::orderBy('name')->limit(200)->get(['id','name']);
        $activeCounters = Counters::where('is_active', true)->orderBy('code')->get(['id','code','name']);

        return view('queue', [
            'date' => $date,
            'status' => $status,
            'q' => $search,
            // Empty paginator removed; DataTables will fetch rows
            'queues' => collect(),
            'visitors' => $visitors,
            'inmates' => $inmates,
            'activeCounters' => $activeCounters,
        ]);
    }

    /**
     * Store a new queue (auto ticket per date) with validation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visit_date' => ['required','date'],
            'visitor_id' => ['required','uuid','exists:visitors,id'],
            'inmate_id' => ['required','uuid','exists:inmates,id'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error','errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $visitor = Visitors::find($data['visitor_id']);
        if ($visitor && $visitor->is_blacklisted) {
            return response()->json(['message' => 'Visitor diblokir (blacklisted).'], 422);
        }

        $queue = null;
        DB::transaction(function() use (&$queue, $data) {
            // Determine prefix by priority: 2=A, 1=B, 0=C
            $priority = (int)($data['priority'] ?? 0);
            $prefix = match ($priority) {
                2 => 'A',
                1 => 'B',
                default => 'C',
            };

            // Get last ticket for the date; parse numeric tail, then increment
            $lastTicket = DB::table('visit_queue')
                ->whereDate('visit_date', $data['visit_date'])
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->value('ticket_number');

            $lastNum = 0;
            if (is_string($lastTicket) && preg_match('/(\d+)/', $lastTicket, $m)) {
                $lastNum = (int) $m[1];
            } elseif (is_numeric($lastTicket)) {
                $lastNum = (int) $lastTicket;
            }
            $nextNum = $lastNum + 1;
            $ticket = sprintf('%s-%03d', $prefix, $nextNum);

            $queue = new VisitQueue();
            $queue->id = (string) Str::uuid();
            $queue->visit_date = $data['visit_date'];
            $queue->ticket_number = $ticket;
            $queue->visitor_id = $data['visitor_id'];
            $queue->inmate_id = $data['inmate_id'];
            $queue->priority = $priority;
            $queue->status = 'waiting';
            $queue->created_by = Auth::id();
            $queue->save();
        });

        return response()->json(['message' => 'Queue created','data' => $queue]);
    }

    /**
     * Update editable fields for waiting/called.
     */
    public function update(Request $request, VisitQueue $queue)
    {
        if (!in_array($queue->status, ['waiting','called'])) {
            return response()->json(['message' => 'Tidak bisa mengubah data pada status ini.'], 422);
        }
        $validator = Validator::make($request->all(), [
            'visitor_id' => ['required','uuid','exists:visitors,id'],
            'inmate_id' => ['required','uuid','exists:inmates,id'],
            'priority' => ['nullable','integer','min:0'],
            'notes' => ['nullable','string'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error','errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $visitor = Visitors::find($data['visitor_id']);
        if ($visitor && $visitor->is_blacklisted) {
            return response()->json(['message' => 'Visitor diblokir (blacklisted).'], 422);
        }

        $queue->visitor_id = $data['visitor_id'];
        $queue->inmate_id = $data['inmate_id'];
        $queue->priority = $data['priority'] ?? 0;
        $queue->notes = $data['notes'] ?? null;
        $queue->save();

        return response()->json(['message' => 'Queue updated','data' => $queue]);
    }

    /**
     * Call a queue: set status called, set counter, called_at, and write call log with call_no sequence.
     */
    public function call(Request $request, VisitQueue $queue)
    {
        $validator = Validator::make($request->all(), [
            'counter_id' => ['required','uuid','exists:counters,id'],
            'message' => ['nullable','string','max:200'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error','errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $counter = Counters::where('id', $data['counter_id'])->where('is_active', true)->first();
        if (!$counter) {
            return response()->json(['message' => 'Counter tidak aktif atau tidak ditemukan.'], 422);
        }

        DB::transaction(function() use ($queue, $data) {
            $queue->status = 'called';
            $queue->counter_id = $data['counter_id'];
            $queue->called_at = Carbon::now();
            $queue->save();

            // Get last call_no for this queue using ORDER BY ... FOR UPDATE
            $lastCall = DB::table('queue_call_logs')
                ->where('queue_id', $queue->id)
                ->orderByDesc('call_no')
                ->lockForUpdate()
                ->value('call_no');
            $log = new QueueCallLogs();
            $log->id = (string) Str::uuid();
            $log->queue_id = $queue->id;
            $log->called_by = Auth::id();
            $log->counter_id = $data['counter_id'];
            $log->call_no = ($lastCall ?? 0) + 1;
            $log->message = $data['message'] ?? null;
            $log->created_at = Carbon::now();
            $log->save();
        });

        return response()->json(['message' => 'Queue called','data' => $queue->fresh(['counter'])]);
    }

    /** Start service */
    public function start(Request $request, VisitQueue $queue)
    {
        if (!in_array($queue->status, ['called'])) {
            return response()->json(['message' => 'Hanya antrian dipanggil yang dapat mulai dilayani.'], 422);
        }
        $queue->status = 'serving';
        $queue->started_at = Carbon::now();
        $queue->save();
        return response()->json(['message' => 'Service dimulai','data' => $queue]);
    }

    /** Finish service */
    public function finish(Request $request, VisitQueue $queue)
    {
        if (!in_array($queue->status, ['serving'])) {
            return response()->json(['message' => 'Hanya antrian yang sedang dilayani yang dapat diselesaikan.'], 422);
        }
        $queue->status = 'done';
        $queue->ended_at = Carbon::now();
        $queue->save();
        return response()->json(['message' => 'Antrian selesai','data' => $queue]);
    }

    /** Mark no-show */
    public function noShow(Request $request, VisitQueue $queue)
    {
        if (!in_array($queue->status, ['waiting','called'])) {
            return response()->json(['message' => 'Status tidak valid untuk no-show.'], 422);
        }
        $queue->status = 'no_show';
        $queue->save();
        return response()->json(['message' => 'Ditandai tidak hadir','data' => $queue]);
    }

    /** Cancel with reason */
    public function cancel(Request $request, VisitQueue $queue)
    {
        if (!in_array($queue->status, ['waiting','called','serving'])) {
            return response()->json(['message' => 'Status tidak valid untuk pembatalan.'], 422);
        }
        $validator = Validator::make($request->all(), [
            'reason' => ['required','string','max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error','errors' => $validator->errors()], 422);
        }
        $queue->status = 'cancelled';
        $queue->cancelled_reason = $request->input('reason');
        $queue->save();
        return response()->json(['message' => 'Antrian dibatalkan','data' => $queue]);
    }
}
