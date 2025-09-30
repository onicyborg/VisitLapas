<?php

namespace App\Http\Controllers;

use App\Models\Counters;
use App\Models\VisitQueue;
use App\Models\QueueCallLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CountersController extends Controller
{
    /**
     * Index with search/filter/pagination.
     */
    public function index(Request $request)
    {
        $counters = Counters::orderBy('code')->get();
        return view('counters', compact('counters'));
    }

    /** Create new counter (AJAX) */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required','string','max:30','regex:/^[A-Za-z0-9_-]+$/','unique:counters,code'],
            'name' => ['required','string','max:80'],
            'is_active' => ['sometimes','boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error','errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $counter = new Counters();
        $counter->id = (string) Str::uuid();
        $counter->code = $data['code'];
        $counter->name = $data['name'];
        $counter->is_active = (bool) ($data['is_active'] ?? true);
        $counter->save();

        return response()->json(['message' => 'Counter created successfully','data' => $counter]);
    }

    /** Update counter (AJAX) */
    public function update(Request $request, Counters $counter)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required','string','max:30','regex:/^[A-Za-z0-9_-]+$/','unique:counters,code,' . $counter->id . ',id'],
            'name' => ['required','string','max:80'],
            'is_active' => ['sometimes','boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error','errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $counter->code = $data['code'];
        $counter->name = $data['name'];
        if (array_key_exists('is_active', $data)) {
            $counter->is_active = (bool) $data['is_active'];
        }
        $counter->save();

        return response()->json(['message' => 'Counter updated successfully','data' => $counter]);
    }

    /** Toggle active flag (AJAX) */
    public function toggle(Counters $counter)
    {
        $counter->is_active = !$counter->is_active;
        $counter->save();
        return response()->json(['message' => 'Status updated','data' => $counter]);
    }

    /** Delete counter safely (AJAX) */
    public function destroy(Counters $counter)
    {
        $usedInQueues = VisitQueue::where('counter_id', $counter->id)->exists();
        $usedInLogs = QueueCallLogs::where('counter_id', $counter->id)->exists();
        if ($usedInQueues || $usedInLogs) {
            return response()->json(['message' => 'Counter pernah dipakai, silakan nonaktifkan saja.'], 422);
        }
        $counter->delete();
        return response()->json(['message' => 'Counter deleted successfully']);
    }
}
