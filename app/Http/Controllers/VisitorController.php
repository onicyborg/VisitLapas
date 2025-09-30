<?php

namespace App\Http\Controllers;

use App\Models\Visitors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    /**
     * List page for visitors
     */
    public function index()
    {
        $visitors = Visitors::orderBy('updated_at', 'desc')->get();
        return view('visitors', compact('visitors'));
    }

    /**
     * Show single visitor (JSON)
     */
    public function show(string $id)
    {
        $visitor = Visitors::findOrFail($id);
        return response()->json(['data' => $visitor]);
    }

    /**
     * Store new visitor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'national_id' => 'nullable|string|max:32|unique:visitors,national_id',
            'name' => 'required|string|max:150',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'relation_note' => 'nullable|string|max:120',
            'is_blacklisted' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $visitor = new Visitors();
        $visitor->id = (string) Str::uuid();
        $visitor->national_id = $data['national_id'] ?? null;
        $visitor->name = $data['name'];
        $visitor->gender = $data['gender'] ?? null;
        $visitor->birth_date = $data['birth_date'] ?? null;
        $visitor->phone = $data['phone'] ?? null;
        $visitor->address = $data['address'] ?? null;
        $visitor->relation_note = $data['relation_note'] ?? null;
        $visitor->is_blacklisted = (bool) ($data['is_blacklisted'] ?? false);
        $visitor->notes = $data['notes'] ?? null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/visitors');
            $visitor->photo_url = Storage::url($path);
        }

        $visitor->save();

        return response()->json([
            'message' => 'Visitor created successfully',
            'data' => $visitor,
        ]);
    }

    /**
     * Update visitor
     */
    public function update(Request $request, string $id)
    {
        $visitor = Visitors::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'national_id' => 'nullable|string|max:32|unique:visitors,national_id,' . $visitor->id . ',id',
            'name' => 'required|string|max:150',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'relation_note' => 'nullable|string|max:120',
            'is_blacklisted' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $visitor->national_id = $data['national_id'] ?? null;
        $visitor->name = $data['name'];
        $visitor->gender = $data['gender'] ?? null;
        $visitor->birth_date = $data['birth_date'] ?? null;
        $visitor->phone = $data['phone'] ?? null;
        $visitor->address = $data['address'] ?? null;
        $visitor->relation_note = $data['relation_note'] ?? null;
        $visitor->is_blacklisted = (bool) ($data['is_blacklisted'] ?? $visitor->is_blacklisted);
        $visitor->notes = $data['notes'] ?? null;

        if ($request->boolean('photo_remove')) {
            if (!empty($visitor->photo_url) && str_starts_with($visitor->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($visitor->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $visitor->photo_url = null;
        }
        if ($request->hasFile('photo')) {
            if (!empty($visitor->photo_url) && str_starts_with($visitor->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($visitor->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $path = $request->file('photo')->store('public/visitors');
            $visitor->photo_url = Storage::url($path);
        }

        $visitor->save();

        return response()->json([
            'message' => 'Visitor updated successfully',
            'data' => $visitor,
        ]);
    }

    /**
     * Delete visitor
     */
    public function destroy(string $id)
    {
        $visitor = Visitors::findOrFail($id);
        if (!empty($visitor->photo_url) && str_starts_with($visitor->photo_url, '/storage/')) {
            $storagePath = 'public/' . ltrim(substr($visitor->photo_url, strlen('/storage/')), '/');
            try { Storage::delete($storagePath); } catch (\Throwable $e) {}
        }
        $visitor->delete();

        return response()->json(['message' => 'Visitor deleted successfully']);
    }
}
