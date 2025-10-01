<?php

namespace App\Exports;

use App\Models\VisitQueue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VisitReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public string $fileName = 'visit_report.xlsx';

    protected ?string $from;
    protected ?string $to;
    protected string $search;

    public function __construct(?string $from, ?string $to, string $search = '')
    {
        $this->from = $from ?: null;
        $this->to = $to ?: null;
        $this->search = trim($search);

        $suffix = ($this->from ?: 'all') . '_to_' . ($this->to ?: 'all');
        $this->fileName = "visit_report_{$suffix}.xlsx";
    }

    public function query()
    {
        $q = VisitQueue::query()
            ->leftJoin('visitors as v', 'v.id', '=', 'visit_queue.visitor_id')
            ->leftJoin('inmates as i', 'i.id', '=', 'visit_queue.inmate_id')
            ->leftJoin('counters as c', 'c.id', '=', 'visit_queue.counter_id')
            ->select(
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

        if ($this->from) { $q->whereDate('visit_date', '>=', $this->from); }
        if ($this->to) { $q->whereDate('visit_date', '<=', $this->to); }
        if ($this->search !== '') {
            $q->where(function($w) {
                $like = '%' . $this->search . '%';
                $w->where('visit_queue.ticket_number', 'ilike', $like)
                  ->orWhere('v.name', 'ilike', $like)
                  ->orWhere('i.name', 'ilike', $like)
                  ->orWhere('c.code', 'ilike', $like);
            });
        }

        return $q->orderBy('visit_date')->orderBy('ticket_number');
    }

    public function headings(): array
    {
        return ['Date','Ticket','Visitor','Inmate','Status','Counter','Priority','Called At','Started At','Ended At'];
    }

    public function map($r): array
    {
        $priorityInt = (int)$r->priority;
        $priorityMap = [0 => 'Reguler', 1 => 'Priority', 2 => 'VIP'];
        $priorityLabel = $priorityMap[$priorityInt] ?? 'Reguler';

        return [
            Carbon::parse($r->visit_date)->format('Y-m-d'),
            (string)$r->ticket_number,
            (string)$r->visitor_name,
            (string)$r->inmate_name,
            ucfirst(str_replace('_',' ', (string)$r->status)),
            (string)$r->counter_code,
            $priorityLabel,
            $r->called_at ? Carbon::parse($r->called_at)->format('Y-m-d H:i:s') : '',
            $r->started_at ? Carbon::parse($r->started_at)->format('Y-m-d H:i:s') : '',
            $r->ended_at ? Carbon::parse($r->ended_at)->format('Y-m-d H:i:s') : '',
        ];
    }
}
