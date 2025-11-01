<?php

namespace App\Http\Controllers;

use App\Models\Inmate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InmatesController extends Controller
{
    /**
     * Tampilkan halaman daftar napi
     */
    public function index()
    {
        $inmates = Inmate::orderBy('updated_at', 'desc')->get();
        return view('inmates', compact('inmates'));
    }

    /**
     * Tampilkan satu napi (JSON)
     */
    public function show(string $id)
    {
        $inmate = Inmate::findOrFail($id);
        // dd($inmate);
        return response()->json(['data' => $inmate]);
    }

    /**
     * Simpan napi baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register_no' => 'required|string|max:50|unique:inmates,register_no',
            'name' => 'required|string|max:150',
            'nik' => 'required|string|max:50|unique:inmates,nik',
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'nullable|date',
            'cell_block' => 'nullable|string|max:50',
            'status' => 'required|in:active,transferred,released',
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

        $inmate = new Inmate();
        $inmate->id = (string) Str::uuid();
        $inmate->register_no = $data['register_no'];
        $inmate->name = $data['name'];
        $inmate->nik = $data['nik'];
        $inmate->gender = $data['gender'];
        $inmate->birth_date = $data['birth_date'] ?? null;
        $inmate->cell_block = $data['cell_block'] ?? null;
        $inmate->status = $data['status'];
        $inmate->notes = $data['notes'] ?? null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/inmates');
            $inmate->photo_url = Storage::url($path);
        }
        $inmate->save();

        return response()->json([
            'message' => 'Inmate created successfully',
            'data' => $inmate,
        ]);
    }

    /**
     * Update napi
     */
    public function update(Request $request, string $id)
    {
        $inmate = Inmate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'register_no' => 'required|string|max:50|unique:inmates,register_no,' . $inmate->id . ',id',
            'name' => 'required|string|max:150',
            'nik' => 'required|string|max:50|unique:inmates,nik,' . $inmate->id . ',id',
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'nullable|date',
            'cell_block' => 'nullable|string|max:50',
            'status' => 'required|in:active,transferred,released',
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

        $inmate->register_no = $data['register_no'];
        $inmate->name = $data['name'];
        $inmate->nik = $data['nik'];
        $inmate->gender = $data['gender'];
        $inmate->birth_date = $data['birth_date'] ?? null;
        $inmate->cell_block = $data['cell_block'] ?? null;
        $inmate->status = $data['status'];
        $inmate->notes = $data['notes'] ?? null;

        if ($request->boolean('photo_remove')) {
            if (!empty($inmate->photo_url) && str_starts_with($inmate->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($inmate->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $inmate->photo_url = null;
        }
        if ($request->hasFile('photo')) {
            if (!empty($inmate->photo_url) && str_starts_with($inmate->photo_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($inmate->photo_url, strlen('/storage/')), '/');
                try { Storage::delete($storagePath); } catch (\Throwable $e) {}
            }
            $path = $request->file('photo')->store('public/inmates');
            $inmate->photo_url = Storage::url($path);
        }

        $inmate->save();

        return response()->json([
            'message' => 'Inmate updated successfully',
            'data' => $inmate,
        ]);
    }

    /**
     * Hapus napi
     */
    public function destroy(string $id)
    {
        $inmate = Inmate::findOrFail($id);
        if (!empty($inmate->photo_url) && str_starts_with($inmate->photo_url, '/storage/')) {
            $storagePath = 'public/' . ltrim(substr($inmate->photo_url, strlen('/storage/')), '/');
            try { Storage::delete($storagePath); } catch (\Throwable $e) {}
        }
        $inmate->delete();

        return response()->json(['message' => 'Inmate deleted successfully']);
    }
}
