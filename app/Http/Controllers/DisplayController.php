<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\VisitQueue;
use App\Models\Counters;
use App\Models\DisplaySettings;

class DisplayController extends Controller
{
    /**
     * Render public display page with initial settings.
     */
    public function index(Request $request)
    {
        $settings = DisplaySettings::query()->first();
        // Defaults if not set
        $theme = $settings->theme ?? 'light';
        $voice = (bool) ($settings->voice_enabled ?? false);
        $ticker = $settings->ticker_text ?? '';

        return view('display', [
            'theme' => $theme,
            'voice_enabled' => $voice,
            'ticker_text' => $ticker,
        ]);
    }

    /**
     * JSON data for the display page, polled by the frontend.
     * GET /api/v1/display?date=YYYY-MM-DD
     */
    public function data(Request $request)
    {
        $date = $request->query('date');
        if (!$date) { $date = Carbon::now()->toDateString(); }

        // Settings snapshot
        $settings = DisplaySettings::query()->first();
        $settingsOut = [
            'theme' => $settings->theme ?? 'light',
            'voice_enabled' => (bool) ($settings->voice_enabled ?? false),
            'ticker_text' => $settings->ticker_text ?? '',
        ];

        // Current: called or serving, today, ordered by called_at asc
        $currentRows = VisitQueue::with(['counter'])
            ->whereDate('visit_date', $date)
            ->whereIn('status', ['called','serving'])
            ->orderBy('called_at', 'asc')
            ->get();

        $current = $currentRows->map(function($q){
            return [
                'counter_code' => (string) ($q->counter->code ?? '-'),
                'ticket_number' => (string) $q->ticket_number,
                'status' => (string) $q->status,
                'since' => optional($q->called_at)->format('H:i') ?? '-',
            ];
        })->values();

        // Next: waiting, today, priority desc then created_at asc, limit 10
        $nextRows = VisitQueue::query()
            ->whereDate('visit_date', $date)
            ->where('status', 'waiting')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get(['ticket_number']);

        $next = $nextRows->map(fn($q) => [ 'ticket_number' => (string) $q->ticket_number ])->values();

        return response()->json([
            'settings' => $settingsOut,
            'current' => $current,
            'next' => $next,
            'date' => $date,
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Update display settings (admin only).
     */
    public function updateSettings(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'theme' => ['required','in:light,dark'],
            'voice_enabled' => ['nullable','boolean'],
            'ticker_text' => ['nullable','string','max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $settings = DisplaySettings::query()->first();
        if (!$settings) { $settings = new DisplaySettings(); }
        $settings->id = (string) Str::uuid();
        $settings->theme = $data['theme'];
        $settings->voice_enabled = (bool) ($data['voice_enabled'] ?? false);
        $settings->ticker_text = $data['ticker_text'] ?? '';
        $settings->updated_by = Auth::id();
        $settings->updated_at = Carbon::now();
        $settings->save();

        return response()->json([
            'message' => 'Display settings updated',
            'data' => [
                'theme' => $settings->theme,
                'voice_enabled' => (bool) $settings->voice_enabled,
                'ticker_text' => (string) $settings->ticker_text,
            ],
        ]);
    }
}
