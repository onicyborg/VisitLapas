<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\VisitQueue;
use App\Models\DisplaySettings;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Today stats
        $visitors_today = VisitQueue::query()
            ->whereDate('visit_date', $today)
            ->distinct('visitor_id')->count('visitor_id');

        $inmates_today = VisitQueue::query()
            ->whereDate('visit_date', $today)
            ->distinct('inmate_id')->count('inmate_id');

        $queues_today = VisitQueue::query()
            ->whereDate('visit_date', $today)
            ->count();

        $completed_today = VisitQueue::query()
            ->whereDate('visit_date', $today)
            ->where('status', 'done')
            ->count();

        // Daily visits (done) last 7 days (including today)
        $start = $today->copy()->subDays(6);
        $doneRows = VisitQueue::query()
            ->select(DB::raw('visit_date, count(*) as c'))
            ->whereBetween('visit_date', [$start->toDateString(), $today->toDateString()])
            ->where('status', 'done')
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get()
            ->keyBy(function ($r) { return Carbon::parse($r->visit_date)->toDateString(); });
        $daily_labels = [];
        $daily_values = [];
        for ($d = $start->copy(); $d->lte($today); $d->addDay()) {
            $key = $d->toDateString();
            $daily_labels[] = $d->isoFormat('ddd, D/M');
            $daily_values[] = (int)($doneRows[$key]->c ?? 0);
        }

        // Status distribution today
        $statuses = ['waiting','called','serving','done','no_show','cancelled'];
        $statusCounts = VisitQueue::query()
            ->select('status', DB::raw('count(*) as c'))
            ->whereDate('visit_date', $today)
            ->groupBy('status')
            ->pluck('c', 'status');
        $status_distribution = [];
        foreach ($statuses as $s) {
            $status_distribution[$s] = (int)($statusCounts[$s] ?? 0);
        }

        // Top 5 current queues (active)
        $activeStatuses = ['waiting','called','serving'];
        $top_queues = VisitQueue::query()
            ->with(['visitor:id,name', 'inmate:id,name'])
            ->whereIn('status', $activeStatuses)
            ->whereDate('visit_date', $today)
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->limit(5)
            ->get(['id','ticket_number','visitor_id','inmate_id','status','priority']);

        // Ticker text from display settings
        $ticker_text = optional(DisplaySettings::query()->first())->ticker_text ?? '';

        return view('welcome', [
            'visitors_today' => $visitors_today,
            'inmates_today' => $inmates_today,
            'queues_today' => $queues_today,
            'completed_today' => $completed_today,
            'daily_labels' => $daily_labels,
            'daily_values' => $daily_values,
            'status_distribution' => $status_distribution,
            'top_queues' => $top_queues,
            'ticker_text' => $ticker_text,
        ]);
    }
}
