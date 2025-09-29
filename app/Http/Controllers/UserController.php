<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display users list page.
     */
    public function index()
    {
        $users = User::with(['profile'])
            ->orderBy('updated_at', 'desc')
            ->get();
        return view('users', compact('users'));
    }

    /**
     * Show a single user.
     */
    public function show(string $id)
    {
        $users = User::with(['profile'])->findOrFail($id);
        return response()->json(['data' => $users]);
    }

    /**
     * Store a new user (user + profile).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $user = new User();
        // users.id bertipe UUID (lihat migration users: $table->uuid('id')->primary())
        $user->id = (string) Str::uuid();
        $user->email = $payload['email'];
        $user->is_active = $payload['is_active'] ?? true;
        $user->password = Hash::make($payload['password'] ?? 'Qwerty123*');
        $user->name = $payload['full_name'];
        $user->phone = $payload['phone'] ?? null;
        $user->save();

        $profile = new UserProfile();
        $profile->user_id = $user->id;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/profiles');
            $profile->avatar_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile']);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user,
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $user->id . ',id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        $user->email = $payload['email'];
        $user->name = $payload['full_name'];
        $user->phone = $payload['phone'] ?? null;
        if (array_key_exists('is_active', $payload)) {
            $user->is_active = $payload['is_active'];
        }
        if (!empty($payload['password'] ?? null)) {
            $user->password = Hash::make($payload['password']);
        }
        $user->save();

        $profile = $user->profile ?: new UserProfile(['user_id' => $user->id]);
        // posisi opsional; hapus jika tidak digunakan di project ini
        if ($request->boolean('photo_remove')) {
            if (!empty($profile->avatar_url) && str_starts_with($profile->avatar_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->avatar_url, strlen('/storage/')), '/');
                try {
                    Storage::delete($storagePath);
                } catch (\Throwable $e) {
                }
            }
            $profile->avatar_url = null;
        }
        if ($request->hasFile('photo')) {
            if (!empty($profile->avatar_url) && str_starts_with($profile->avatar_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->avatar_url, strlen('/storage/')), '/');
                try {
                    Storage::delete($storagePath);
                } catch (\Throwable $e) {
                }
            }
            $path = $request->file('photo')->store('public/profiles');
            $profile->avatar_url = Storage::url($path);
        }
        $profile->save();

        $user->load(['profile']);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        // delete profile first (and optionally the file)
        if ($user->profile) {
            $profile = $user->profile;
            if (!empty($profile->avatar_url) && str_starts_with($profile->avatar_url, '/storage/')) {
                $storagePath = 'public/' . ltrim(substr($profile->avatar_url, strlen('/storage/')), '/');
                try {
                    Storage::delete($storagePath);
                } catch (\Throwable $e) {
                }
            }
            $profile->delete();
        }
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
